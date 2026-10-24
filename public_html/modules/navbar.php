<!-- mod::navbar (std) -->
<?php
$navItems = array(
	'home' 		=> array(
		'url' => '/index.php',
		'icon'	=> 'fa-solid fa-house',
		'label' => '<'
	),
	'dev'	=> array(
		'url' => '/dev/index.php',
		'icon'	=> 'fa-solid fa-code',
		'label' => 'dev'
	)
);

echo '<div class="container">' . NL;

echo '<ul class="nav nav-tabs" role="tablist">' . NL;

foreach ($navItems as $app => $details) {
	echo '<li role="presentation" class="nav-item">';
	echo '<a href="'
		. $details['url'] . '" title="' . $details['label'] . '" class="nav-link';
	if (APP_NAME == $app) echo ' active';
	echo '">';
	if ($details['icon']) {
		echo '<i class="' . $details['icon'] . '"></i> ';
	}
	echo $details['label'] . '</a></li>' . NL;
}

echo '</ul>' . NL;

echo '</div><!-- .container -->' . NL;
?>