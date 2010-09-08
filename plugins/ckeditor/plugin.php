<?php

class CKEditorPlugin implements NewEditor {
	public static $handles = array();
	public static $instance;
	public static $plugin;

	public static function bootstrap () {
		$editor = new Editor("CKEditor", 3.2, "xya");
		$editor->bind_create("CKEditorPlugin::create");
		Editors::register($editor);
   	}

	public static function create ($name, $initContents) {
		$ckeditor = self::$plugin;
		//var_dump($initContents);
		//return "<textarea id='$name' name='$name' rows='10' >$initContents</textarea>";
		require_once $ckeditor->pluginDir().'ckeditor/ckeditor_php5.php';
		self::$handles[$name] = new CKEditor($ckeditor->pluginPublicDir().'ckeditor/');
		self::$handles[$name]->returnOutput = true;
		self::$handles[$name]->config['width'] = 645;
		self::$handles[$name]->config['height'] = 400;
		self::$handles[$name]->config['toolbar'] = array(
			array('Cut','Copy','Paste','PasteText','PasteFromWord','-','Print', 'SpellChecker', 'Scayt'),
			array('Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'),
			array('HorizontalRule','Smiley','SpecialChar','PageBreak'),
			'/',
			array('Bold','Italic','Underline','Strike','-','Subscript','Superscript'),
			array('NumberedList','BulletedList','-','Outdent','Indent','Blockquote'),
			array('JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'),
			array('Link','Unlink','Anchor','Image','Flash','Table'),
			'/',
			array('Styles','Format','Font','FontSize'),
			array('TextColor','BGColor'),
		);
		return self::$handles[$name]->editor($name, $initContents);
   	}
}
CKEditorPlugin::$plugin = new Plugin('CKEditor Enabler', 'author' , 'desc', '3.3');
CKEditorPlugin::$plugin->bootstrap('CKEditorPlugin::bootstrap');


