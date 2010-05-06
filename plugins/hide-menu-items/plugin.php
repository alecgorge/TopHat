<?php

$hideMenuP = new Plugin('Hide Menu Items', 'CanyonCMS Team', 'Allows you to hide certain items from displaying on the menu.');

$hideMenuP->bind('admin_menu', function () {
	Admin::registerSubpage('dashboard', 'kool', 'Subpage', function () {
		echo 'test';
	});
});

$hideMenuP->filter('content_setcontent', function ($x) {
	return sprintf("<i>%s</i>", $x);
});
?>
