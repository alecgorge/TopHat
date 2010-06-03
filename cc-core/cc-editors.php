<?php
class Editors {
	public static $registered = array();
	public static $editor;
	
	public static function register (&$instance) {
		self::$registered[$instance->name] = $instance;
	}

	public static function bootstrap () {
		self::$editor = Settings::get('gui', 'editor', true);
	}
	
	public static function getEditorObj () {
		if(array_key_exists(self::$editor, self::$registered)) {
			return self::$registered[self::$editor];
		}
		else {
			trigger_error(self::$editor." is not a valid editor.", E_USER_ERROR);
		}
	}

	public static function create ($name, $contents) {
		return self::getEditorObj()->run('create', array($name, $contents));
   	}
}
Hooks::bind('system_ready', 'Editors::bootstrap');

class Editor {
	public $name, $version, $author, $binds = array();
	
	public function __construct ($name, $version, $author) {
		$this->name = $name;
		$this->version = $version;
		$this->author = $author;
	}
	
	public function bind_create($callback) {
		if(!is_callable($callback)) {
			trigger_error("$callback is not callable!");
		}
		
		$this->binds['create'] = $callback;
	}

	public function run ($x, $args) {
		return call_user_func_array($this->binds[$x], $args);
	}
}

interface NewEditor {
	public static function create($name, $initContents);
}
?>
