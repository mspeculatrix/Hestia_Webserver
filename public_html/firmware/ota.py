"""
ota.py is kept as a separate file (and not merged into main.py) so that it can
itself be updated. There are other advantages, such as minimising memory usage
and fault isolation.
"""

import gc
import hashlib
import json
import os
import time
from binascii import hexlify

import urequests

from config import LED, Config

REMOTE_DIR: str = f'{Config.OTA_SERVER_ADDR}/{Config.OTA_DIR}/'


def _flash_led(times: int, delay: int = 250, end_state: int = 0) -> None:
	for _ in range(times):
		LED.on()
		time.sleep_ms(delay)
		LED.off()
		time.sleep_ms(delay)
	LED(end_state)


def _sha256_file(path: str) -> str | None:
	"""
	Stream-hashes a local file in 1KB chunks to avoid large RAM spikes.
	"""
	try:
		h = hashlib.sha256()
		with open(path, 'rb') as f:
			while True:
				chunk = f.read(1024)
				if not chunk:
					break
				h.update(chunk)
		return hexlify(h.digest()).decode()
	except OSError:
		return None


def _get(name: str) -> bytes:
	r = urequests.get(REMOTE_DIR + name, timeout=5)
	try:
		if r.status_code != 200:
			raise OSError(f'HTTP {r.status_code} for {name}')
		return r.content
	finally:
		r.close()


def _safe_replace(src_new: str, dst: str) -> None:
	"""
	Safely replaces dst with src_new on LittleFS where os.rename()
	fails if dst already exists.
	"""
	bak: str = dst + '.bak'
	has_bak = False
	try:  # remove any existing backup file. Will fail if there is no file.
		os.remove(bak)
	except OSError:
		pass

	try:
		# Rename the existing file to a backup file. Will fail if no such file.
		os.rename(dst, bak)
		has_bak = True  # If we got here, it worked
	except OSError:
		pass  # dst didn't exist yet (new file)

	try:
		# Rename the new file to take the place of the old one.
		os.rename(src_new, dst)
		if has_bak:
			# If the renaming succeeded, remove the backup file, it's not needed
			os.remove(bak)
	except Exception:
		# Roll back if moving the new file into place failed
		if has_bak:
			try:
				os.rename(bak, dst)
			except OSError:
				pass
		# This is a failure to update, to raise an exception for main.py to
		# deal with.
		raise


def check_and_update() -> bool:
	"""
	Get the manifest file, check if any files need updating and, if so,
	update them.
	"""
	manifest: dict[str, str] = json.loads(_get('manifest.json'))
	staged: list[str] = []

	try:
		for name, want_hash in manifest.items():
			if _sha256_file(name) == want_hash:
				continue

			print(f'OTA:Downloading:{name}')
			data: bytes = _get(name)
			actual_hash: str = hexlify(hashlib.sha256(data).digest()).decode()
			if actual_hash != want_hash:
				raise OSError(f'SHA-256 mismatch for {name}')

			tmp_name: str = name + '.new'
			with open(tmp_name, 'wb') as f:
				f.write(data)

			staged.append(name)
			del data
			gc.collect()

	except Exception:
		# Roll back any staged .new files on partial failure
		for name in staged:
			try:
				os.remove(name + '.new')
			except OSError:
				pass
		# Even if we succeed in rolling back, this is an update failure so
		# raise an exception for main.py to deal with
		raise

	# Commit all verified files
	for name in staged:
		_safe_replace(name + '.new', name)
		print(f'OTA:Updated:{name}')
		_flash_led(1)

	return bool(staged)
