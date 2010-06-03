<?php

class CKEditorPlugin implements NewEditor {
	public static $handles = array();
	public static $instance;
	public static $plugin;

	public static function bootstrap () {
		self::$plugin = new Plugin('CKEditor Enabler', 'author' , '3.3');
		$editor = new Editor("CKEditor", 3.2, "xya");
		$editor->bind_create("CKEditorPlugin::create");
		Editors::register($editor);
   	}

	public static function create ($name, $initContents) {
		$ckeditor = self::$plugin;
		require_once $ckeditor->pluginDir().'editor/ckeditor_php5.php';
		self::$handles[$name] = new CKEditor($ckeditor->pluginPublicDir().'editor/');
		self::$handles[$name]->returnOutput = true;
		return self::$handles[$name]->editor($name, $initContents);
   	}
}
Hooks::bind('system_complete', 'CKEditorPlugin::bootstrap');


?>