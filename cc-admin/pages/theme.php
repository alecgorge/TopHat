<?php

class ThemeInterface {
	public static function sysReady () {
		if(Themes::getCurrentTheme()->hasInterface()) {
			Admin::registerPage('theme-settings', __('admin', 'theme-settings'), 'ThemeInterface::displayInterface', 2);
		}
	}

	public static function displayInterface () {
		ob_start();

		include Themes::getCurrentTheme()->getInterface();

		$contents = ob_get_contents();
		ob_end_clean();
		return $contents;
	}

}
Hooks::bind('system_ready', ThemeInterface::sysReady());