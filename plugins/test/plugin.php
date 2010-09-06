<?php

class TestPlugin {
	public static $plugin;
	public static function bootstrap () {
		self::$plugin = new Plugin('TestPlugin', 'author' , '3.3');
   	}
}
Hooks::bind('system_complete', 'TestPlugin::bootstrap');


