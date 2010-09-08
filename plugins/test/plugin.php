<?php

class TestPlugin {
	public static $plugin;

	public static function bootstrap () {

	}
}
TestPlugin::$plugin = new Plugin('TestPlugin', 'author' , 'desc', '3.3');
TestPlugin::$plugin->bootstrap('TestPlugin::bootstrap');


