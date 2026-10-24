<?php

/**
 * std_library.php
 *
 * This library provides:
 *
 * - callAPI($method, $url, $data)
 * - clean_request($key, $length, $default, $stripslash, $stripmeta,
 * 		$striptags,	$strip_for_DB)
 * - fileList($dir, $matchStr)
 * - fileListByExt($dir, $ext)
 * - fileListHTML($dir, $ext)
 * - js($modname)
 * - kioskmode()
 * - module($modname)
 * - pagemode()
 * - printArray($array)
 * - redirect($redirectpage, $query)
 * - runmode()
 * - strip_meta(&$rawdata)
 * - strip_non_alphanum(&$rawdata)
 */

/**
 * Make a call to the API which returns a JSON object
 * @param string $method - 'GET', 'POST' or 'PUT'
 * @param string $url - full URL of API
 * @param mixed $data Assoc array containing data to send. Use 'false' if none
 * @return array The JSON data as an assoc array
 *
 * See: https://weichie.com/blog/curl-api-calls-with-php/
 */
function callAPI($method, $url, $data)
{
	$curl = curl_init();
	switch ($method) {
		case "POST":
			curl_setopt($curl, CURLOPT_POST, 1);
			if ($data)
				curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
			break;
		case "PUT":
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
			if ($data)
				curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
			break;
		default: // GET
			if ($data)
				$url = sprintf("%s?%s", $url, http_build_query($data));
	}
	// OPTIONS:
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json',
	));
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	// EXECUTE:
	$result = array('apiError' => 1, 'apiResponse' => 0); // default
	$response = curl_exec($curl);
	$respCode = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);

	if ($response) {
		$result = json_decode($response, true);
		$result['apiError'] = 0;
		$result['apiResponse'] = $respCode;	// integer
	} else {
		$result['apiResponse'] = $respCode;
	}
	curl_close($curl);
	return $result;
}

/**
 * Gets the value from $_REQUEST for a given key and sanitises it
 * If $length is specified, also trims it to that length.
 * @param string $key $_REQUEST key for desired data
 * @param int $length (optional) def: 0, max number of chars allowed
 * @param mixed $default (optional) def: FALSE, value to return if nothing found
 * @param bool $stripslash (optional) def: FALSE, run through stripslashes()
 * @param bool $stripmeta (optional) def: FALSE, run through strip_meta()
 * @param bool $striptags (optional) def: TRUE, remove HTML code
 * @return mixed Value of $_REQUEST item
 */
function clean_request(
	$key,
	$length			= 0,
	$default		= FALSE,
	$stripslash		= FALSE,
	$stripmeta		= FALSE,
	$striptags		= FALSE,
	$strip_for_DB 	= FALSE
) {
	// First checks to see if $key item is in $_REQUEST.
	$request = $default;
	if (array_key_exists($key, $_REQUEST)) {
		$request = trim($_REQUEST[$key]);
		if ($length > 0) {
			$request = substr($request, 0, $length);
		}
		if ($stripslash) {
			$request = stripslashes($request);
		}
		if ($stripmeta) {
			strip_meta($request);
		}
		if ($striptags) {
			strip_tags($request);
		}
		if ($strip_for_DB) {
			// Remove some of the characters that might be used for SQL
			// injection. This should be used only for simple stuff - eg,
			// firstname, lastname etc
			$dbchars = array(';', '=', '(', ')');
			$request = str_replace($dbchars, '', $request);
		}
		trim($request);
	}
	return $request;
}

/**
 * Search directory for filenames that contain a given string.
 * @param string $dir Directory
 * @param string $matchStr String to match against filenames
 * @return array With names of matching files
 *
 */
function fileList($dir, $matchStr)
{
	$files = scandir($dir);
	$filearr = array();
	foreach ($files as $file) {
		if (is_file($dir . '/' . $file) && substr($file, 0, strlen($matchStr)) == $matchStr) {
			array_push($filearr, $file);
		}
	}
	return $filearr;
}

/**
 * Search a directory for filenames that have a specific extension.
 * @param string $dir Directory
 * @param string $ext Extension
 * @return array With names of matching files
 */
function fileListByExt($dir, $ext)
{
	$files = scandir($dir);
	$filearr = array();
	foreach ($files as $file) {
		if (is_file($dir . '/' . $file)) {
			$file_parts = pathinfo($dir . '/' . $file);
			if ($file_parts['extension'] == $ext) {
				array_push($filearr, $file);
			}
		}
	}
	return $filearr;
}

/**
 * Wrapper to fileListByExt. Creates the span items as well as returning array.
 * Search a directory for filenames that have a specific extension.
 * @param string $dir Directory
 * @param string $ext Extension
 * @return array With names of matching files
 */
function fileListHTML($dir, $ext, $datType = 'img')
{
	$images = fileListByExt($dir, $ext);
	$imgNum = count($images);
	echo TAB . '<span id="numImgs" class="data" data="'
		. $imgNum . '"></span>' . NL;
	foreach ($images as $image) {
		echo TAB . '<span class="' . $datType . 'Data data" data="' . $image . '">'
			. '</span>' . NL;
	}
	return $images;
}

/**
 * Provides the path of a named JavaScript file - looks for it in specific
 * directories. If found, echos the appropriate <script> tags.
 * Looks first in a the site's main '/js/' directory.
 * If not found there (or even if it is) then looks in the calling script's
 * own directory for a file in a local 'js/' subdir. That means local files
 * will override site-wide files.
 * @param string $modname Name of file (without .js extension).
 */
function js($modname)
{
	$paths = array('/', './', '../');
	$js_pathFile = FALSE;
	foreach ($paths as $path) {
		if (file_exists($path . 'js/' . $modname . '.js')) {
			$js_pathFile = $path . 'js/' . $modname . '.js';
		}
	}
	if ($js_pathFile) {
		echo '<script src="' . $js_pathFile . '"></script>' . NL;
	}
}

/**
 * Looks for the 'km' parameter in the page URL's query string.
 * If the km parameter is not found, returns 't' as the default.
 * @return string
 */
function kioskmode()
{
	// $kioskmode = clean_request('km', 5, 't', TRUE, TRUE, TRUE);
	// return strtolower($kioskmode);
	$kioskmode = 'v';
	if (isset($_COOKIE['sm_km'])) {
		$kioskmode = $_COOKIE['sm_km'];
	}
	return strtolower($kioskmode);
}

/**
 * Get the path of a named PHP module.
 * @param string $modname str Filename of module (without '.php' extension)
 * @return string Full path of module filename to use with include()
 *
 * This first looks to see if there is a file with this name in a
 * 'modules/' subdir in the calling script's location (ie, a local) module.
 * Then it looks in the modules/ subdir within the root dir of the
 * site (ie, system-wide modules).
 * If the named module is not found in either location, a default
 * module filepath is returned.
 */
function module($modname)
{
	// Look first to see if there's a module in a local dir.
	// We'll prioritise that to 'overload' any site-wide module
	$paths = array('./', DOC_ROOT_PATH);
	$mod_pathFile = FALSE;
	foreach ($paths as $path) {
		if (file_exists($path . 'modules/' . $modname . '.php')) {
			$mod_pathFile = $path . 'modules/' . $modname . '.php';
		}
	}
	// If nothing has been found, return the default module
	if (!$mod_pathFile) {
		echo '<p class="error">Missing module: ' . $modname . '</p>' . NL;
		$mod_pathFile = DOC_ROOT_PATH . 'modules/mod_not_found.php';
	}
	return $mod_pathFile;
}

/**
 * Looks for the 'pm' parameter in the page URL's query string, return value
 * or 'index' as the default.
 * @return string
 */
function pagemode()
{
	$pagemode = clean_request('pm', 20, FALSE, TRUE, TRUE, TRUE);
	if ($pagemode) {
		$pagemode = strtolower($pagemode);
	} else {
		$pagemode = 'index';	// default if nothing found
	}
	return $pagemode;
}

/**
 * Debugging only: print the contents of an array
 * @param array $array Array to print
 */
function printArray($array)
{
	echo '<table>' . NL;
	foreach ($array as $key => $value) {
		echo '<tr><td>' . $key . '</td><td>' . $value . '</td></tr>' . NL;
	}
	echo '</table>' . NL;
}

/**
 * Redirect to another page
 * @param string $redirectpage URL of page to redirect to
 * @param string $query query string to add, if any
 */
function redirect($redirectpage = FALSE, $query = FALSE)
{
	$defaultpage = '/index.php'; // default to site home page

	/*	Priority is given to the values in the params.
		If we've not given a specific target & there are
		session settings, use the session vars */
	if (!$redirectpage && !empty($_SESSION['ref_page'])) {
		$redirectpage = $_SESSION['ref_page'];
		if (!$query && !empty($_SESSION['ref_pagequery'])) {
			$redirectpage .= '?' . $_SESSION['ref_pagequery'];
		}
	} elseif ($redirectpage && $query) {
		$redirectpage .= '?' . $query;
	}
	/*	At this point, if we've passed a specific page, then that
		will be used. If not, we've used the session var. If that
		doesn't exist, then $redirectpage is still FALSE, so
		set it to the default */
	if (!$redirectpage) $redirectpage = $defaultpage;
	header('Location: http://' . $_SERVER['HTTP_HOST'] . $redirectpage);
	exit;
}

/**
 * Looks for the 'rm' parameter in the page URL's query string.
 * @return string Returns 'WEB' as the default if rm parameter is not found.
 */
// function runmode()
// {
// 	$runmode = clean_request('rm', 5, 'web', TRUE, TRUE, TRUE);
// 	return strtolower($runmode);
// }

/**
 * Strip out certain characters from a string
 * @param string $rawdata String to sanitise (passed by ref)
 * @return string Sanitised string
 */
function strip_meta(&$rawdata)
{
	/*	NB: argument passed by reference.
		Strip out dubious characters */
	$metachars = array('*', '$', '|');
	$rawdata = str_replace($metachars, '', $rawdata);
	return;
}

/**
 * Strip out pretty much anything that's not an alphanumeric char from a string.
 * @param string $rawdata The string to sanitise (passed by ref)
 * @return bool Whether the string was changed
 */
function strip_non_alphanum(&$rawdata)
{
	$changed = FALSE;
	$orig = $rawdata;
	$badchars = array(
		' ',
		'+',
		'/',
		'\\',
		'&',
		'.',
		',',
		'@',
		'#',
		'*',
		'^',
		'?',
		'!',
		'%20',
		'%2F',
		'%',
		'$',
		'£',
		'~',
		'[',
		']',
		'(',
		')',
		"'",
		'"',
		'<',
		'>'
	);
	$rawdata = str_replace($badchars, '', $rawdata);
	if ($rawdata != $orig) {
		$changed = TRUE;
	}
	return $changed;
}
