<?php

class Italics {
	public static $plugin;

	public static function bootstrap () {
		self::$plugin->filter('content_setcontent', function ($x) {
			return sprintf("<i>%s</i>", $x);
		});
	}
}

Italics::$plugin = new Plugin('Hide Menu Items', 'CanyonCMS Team', 'Allows you to hide certain items from displaying on the menu.', '1.0');
Italics::$plugin->bootstrap('Italics::bootstrap');

/*$hideMenuP->bind('admin_menu', function () {
	Admin::registerSubpage('dashboard', 'kool', 'Subpage', function () {
		echo 'test';
	});
});*/
