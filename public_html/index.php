<?php
define('APP_NAME', 'ws_client');
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/std_config.php');
?>
<!DOCTYPE html>
<html lang="en-GB">
<?php
include_once(module('head_begin'));
?>
<script src="js/websocketlib.js"></script>
<script src="js/class_console.js"></script>
<script src="client.js"></script>
</head>

<body>
	<?php
	include_once(module('content_begin'));
	include_once(module('masthead'));
	?>

	<div class="container">

		<div class="row">
			<div class="col-3 ctrl">
				<button type="button" id="wsConnect" class="ctrlBtn idle">connect</button>
			</div>
			<div class="col-3 ctrl">
				<p id="status" class="cfg">-!-</p>
			</div>
			<div class="col-3 ctrl">
				<button type="button" id="statusBtn" class="ctrlBtn">status</button>
			</div>
			<div class="col-3 ctrl">
			</div>
		</div>

		<div class="row">
			<div class="col-6">
				<input type="text" class="form-control" id="wsSendMsg">
			</div>
			<div class="col-3 ctrl">
				<button type="button" id="wsSend" class="ctrlBtn">send</button>
			</div>
			<div class="col-3 ctrl">
			</div>
		</div>

		<div id="console-container" class="row console-row">
			<div class="col-12">
				<div id="console" class="console-box" autocomplete="off">
				</div>
			</div>
		</div>

	</div><!-- /.container -->


	<?php
	include_once(module('content_end'));
	?>
</body>

</html>