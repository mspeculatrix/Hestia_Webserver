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
	// include_once(module('masthead'));
	?>

	<div id="content" class="container">

		<div class="row">
			<div class="col-6">
				<p class="label main">HESTIA C2</p>
			</div>
			<div class="col-3">
				<input type="text" class="form-control config" id="cfg_ip" value="0.0.0.0">
			</div>
			<div class="col-3 rc">
				<button type="button" id="wsConnect" class="ctrlBtn idle">connect</button>
			</div>
		</div>

		<div id="remote_control">

			<div class="row">
				<div class="col-4 rc">
					<button type="button" id="FWD_TURN_L" class="rcBtn dirBtn inverted">&#10138;</button>
				</div>
				<div class="col-4 rc">
					<button type="button" id="FWD_STR" class="rcBtn dirBtn">&#10506;</button>
				</div>
				<div class="col-4 rc">
					<button type="button" id="FWD_TURN_R" class="rcBtn dirBtn">&#10138;</button>
				</div>
			</div>

			<div class="row">
				<div class="col-4 rc">
					<button type="button" id="ROTATE_L" class="rcBtn dirBtn">&cularr;</button>
				</div>
				<div class="col-4 rc">
					<button type="button" id="DIR_STOP" class="rcBtn dirBtn">&#x23F9;</button>
				</div>
				<div class="col-4 rc">
					<button type="button" id="ROTATE_R" class="rcBtn dirBtn">&curarr;</button>
				</div>
			</div>

			<div class="row">
				<div class="col-4 rc">
					<button type="button" id="REV_TURN_L" class="rcBtn dirBtn inverted">&#10136;</button>
				</div>
				<div class="col-4 rc">
					<button type="button" id="REV_STR" class="rcBtn dirBtn">&#10507;</button>
				</div>
				<div class="col-4 rc">
					<button type="button" id="REV_TURN_R" class="rcBtn dirBtn">&#10136;</button>
				</div>
			</div>
			<hr class="divider">

			<!-- SPEED BUTTONS --------------------------------------------- -->
			<div class="row">
				<div class="col-2 rc">
					<button type="button" id="SP_CRAWL" class="rcBtn speedBtn">&lt;&lt;</button>
				</div>
				<div class="col-2 rc">
					<button type="button" id="SP_SLOW" class="rcBtn speedBtn">&lt;</button>
				</div>
				<div class="col-4 rc">
					<button type="button" id="SP_NORMAL" class="rcBtn speedBtn">std</button>
				</div>
				<div class="col-2 rc">
					<button type="button" id="SP_FAST" class="rcBtn speedBtn">&GT;</button>
				</div>
				<div class="col-2 rc">
					<button type="button" id="SP_MAX" class="rcBtn speedBtn">&gt;&gt;</button>
				</div>
			</div><!-- row -->
		</div><!-- remote control container -->

		<hr class="divider">

		<!-- COMMAND INPUT ------------------------------------------------- -->
		<div class="row">
			<div class="col-10">
				<input type="text" class="form-control command" id="wsSendMsg">
			</div>
			<div class="col-2 rc">
				<button type="button" id="statusBtn" class="rcBtn sml">
					<strong>&#9432;</strong>
				</button>
			</div>
		</div>

		<!-- CONSOLE ------------------------------------------------------- -->
		<div id="console-container" class="row console-row">
			<div class="col-12">
				<div id="console" class="console-box" autocomplete="off"></div>
			</div>
		</div>

		<!-- CONFIGURAION -------------------------------------------------- -->

		<div class="row">
			<div class="col-3">
				<label>LEFT Speed Cal<br>(&lt;=1.0)</label>
				<input type="text" class="form-control config" id="cfg_sp_cal_l" value="1.0">
			</div>
			<div class="col-3">
				<label">RIGHT Speed Cal<br>(&lt;=1.0)</label>
					<input type="text" class="form-control config" id="cfg_sp_cal_r" value="1.0">
			</div>
			<div class="col-3">
				<label">BASE SPEED<br>(0-255)</label>
					<input type="text" class="form-control config" id="cfg_base_sp" value="80">
			</div>
			<div class="col-3">
				<button type="button" id="cfgBtn" class="ctrlBtn">&lt;CFG</button>
			</div>
		</div>

		<div class="row">
			<div class="col-3">
				<label>ADC0 Trigger</label>
				<input type="text" class="form-control config" id="adc0_trigger" value="0">
			</div>
			<div class="col-3">
				<label>ADC1 Trigger</label>
				<input type="text" class="form-control config" id="adc1_trigger" value="0">
			</div>
			<div class="col-3">
				<label>ADC2 Trigger</label>
				<input type="text" class="form-control config" id="adc2_trigger" value="0">
			</div>
			<div class="col-3">
			</div>
		</div>

	</div><!-- /.container -->


	<?php
	include_once(module('content_end'));
	?>
</body>

</html>