<?php

/**
 * This class is used to queue and load JS and CSS.
 */
class Library {
	/**
	 * @var array Contains an array with the keys being the categories and the values being an array of importance levels having an array of files as the values.
	 */
	public static $queue;
	
	public static $loadedLibraries = array();
	
	public static $shelf;
	
	public static function load ($library) {
		if(is_array($library)) {
			foreach($library as $v) {
				self::load($v);
			}
		}
		else {
			plugin('library_load_call', array($library));
			if(!self::isLoaded($library)) {
				plugin('library_load', array($library));
				self::$loadedLibraries[] = $library;
				if( is_array(self::$shelf[$library]['file']) ) {
					foreach(self::$shelf[$library]['file'] as $type => $files) {
						if(!empty($files)) {
							foreach($files as $file) {
								call_user_func_array('Library::queueFile', array($type, $file, self::$shelf[$library]['importance']));
							}
						}
					}
				}
				else call_user_func_array('Library::queueFile', self::$shelf[$library]);
			}
		}
	}
	
	public static function isLoaded ($library) {
		return array_key_exists($library, self::$loadedLibraries);
	}
	
	public static function queueFile ($type, $file, $importance = 0) {
		if(!is_array(self::$queue[$type])) {
			self::$queue[$type] = array();
		}
		
		if(!is_array(self::$queue[$type][$importance])) {
			self::$queue[$type][$importance] = array();
		}
		
		self::$queue[$type][$importance][]  = $file;
	}
	
	public static function register ($type, $name, $file, $importance = 0) {
		if(is_array(self::$shelf[$name])) {
			trigger_error("Library $name already exists!");
			return;
		}
		$arr = filter('library_register', array($type, $name, $file, $importance));
		list($type, $name, $file, $importance) = $arr;

		self::$shelf[$name] = array($type, $file, $importance);
	}
	
	public static function bootstrap () {
		$inis = glob(CC_ROOT.CC_CONTENT.'libraries/*/*.ini');
		foreach($inis as $ini) {
			$info = parse_ini_file($ini);
			$dir = explode('/', dirname($ini));
			$dir = end($dir).'/';

			$info = filter('library_library_info', $info);

			if(array_key_exists('js_file', $info) || array_key_exists('css_file', $info) || array_key_exists('php_file', $info)) {
				$info['file'] = array('js' => (array) $info['js_file'], 'css' => (array)$info['css_file'], 'php' => (array)$finfo['php_file']);
			}
			self::register($info['type'], $info['name'], CC_PUB_ROOT.CC_CONTENT.'libraries/'.$dir.$info['file'], $info['importance']);
		}
	}
}
class JS extends Library {
	public static function queue ($file, $importance = 0){
		self::queueFile('js', $file, $importance);
	}
	public static function load () {
		$sortedFiles = (array)Library::$queue['js'];
		if(empty($sortedFiles)) {
			return;
		}
		ksort($sortedFiles);
		
		foreach($sortedFiles as $imp => $files) {
			foreach($files as $file) {
				$r .= sprintf("\n".'<script type="text/javascript" src="%s"></script>', $file);
			}
		}
		
		echo $r,"\n";
	}
}
class CSS extends Library {
	public static function queue ($file, $importance = 0){
		self::queueFile('css', $file, $importance);
	}
	public static function load () {
		$sortedFiles = (array)Library::$queue['css'];
		if(empty($sortedFiles)) {
			return;
		}
		ksort($sortedFiles);
		
		foreach($sortedFiles as $imp => $files) {
			foreach($files as $file) {
				$r .= sprintf("\n".'<link rel="stylesheet" type="text/css" href="%s" />', $file);
			}
		}
		
		echo $r,"\n";
	}
}
function load_library ($library) {
	Library::load($library);
}
function queue_js ($file, $importance = 0) {
	JS::queue($file, $importance);
}
function queue_css ($file, $importance = 0) {
	CSS::queue($file, $importance);
}
function load_css() {
	CSS::load();
}
function load_js() {
	JS::load();
}
Hooks::bind('system_ready','Library::bootstrap', -100);

?>