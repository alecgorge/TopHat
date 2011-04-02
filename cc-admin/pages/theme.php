<?php

class ThemeInterface {
	private static $interface;

	public static function register ($c) {
		self::$interface = $c;
	}

	public static function sysReady () {
		if(Themes::getCurrentTheme()->hasInterface()) {
			if(Themes::getCurrentTheme()->hasInterfaceName()) {
				$name = Themes::getCurrentTheme()->getInterfaceName();
			}
			Admin::registerPage('theme-settings', $name ? $name : __('admin', 'theme-settings'), 'ThemeInterface::displayInterface', 2);
		}
	}

	public static function displayInterface () {
		if(is_callable(self::$interface)) {
			return call_user_func(self::$interface);
		}
		else {
			return self::$interface;
		}
	}

}
ThemeInterface::sysReady();

if(Themes::getCurrentTheme()->hasInterface()) {
	require Themes::getCurrentTheme()->getInterface();
}