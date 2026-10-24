<!-- mod::head_begin (localhost) -->

<!-- mod::head_begin (std) -->

<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="pinterest" content="nopin" description="Pinterest sucks!" />
	<meta name="UK-TAG" content="UK-SM">

	<?php
	if (defined('APP_NAME')) {
		echo TAB . '<title>' . SITE_NAME . ' : ' . APP_NAME . '</title>' . NL;
	} else {
		echo TAB . '<title>' . SITE_NAME . '</title>' . NL;
	}
	?>

	<!-- BOOTSTRAP -- see: https://www.bootstrapcdn.com/ 	-->
	<link href="/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
	<script defer src="/assets/bootstrap/js/bootstrap.bundle.min.js"></script>

	<!-- FONT AWESOME ----------------------------------------->
	<link href="/assets/fontawesome/css/all.min.css" rel="stylesheet">

	<!-- ----- JAVASCRIPT ---------------------------------------- -->
	<?php
	if (defined('APP_NAME') && file_exists(APP_NAME . '.js')) {
		// There's a local javascript file for this app
		echo TAB . '<script src="' . APP_NAME . '.js"></script>' . NL;
	}
	if (file_exists('local.js')) {
		// There's a local javascript file for this app
		echo TAB . '<script src="local.js"></script>' . NL;
	}
	?>
	<!-- ----- CSS ----------------------------------------------- -->
	<link href="/css/main.css" rel="stylesheet">
	<?php
	if (file_exists('local.css')) {
		echo TAB . '<link rel="stylesheet" id="local-css" '
			. 'type="text/css" media="all" '
			. 'href="local.css" />' . NL;
	}
	?>