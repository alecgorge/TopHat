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
		return array_search($library, self::$loadedLibraries) !== false;
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

		self::$shelf[$name] = array('type' => $type, 'file' => $file, 'importance' => $importance);
	}
	
	public static function bootstrap () {
		$inis = glob(CC_ROOT.CC_CONTENT.'libraries/*/*.ini');
		foreach($inis as $ini) {
			$info = parse_ini_file($ini);
			$dir = explode('/', dirname($ini));
			$dir = end($dir).'/';

			$info = filter('library_library_info', $info);

			$info['js_file'] = (array)$info['js_file'];
			array_walk($info['js_file'], 'Library::prependPATH', $dir);

			$info['css_file'] = (array)$info['css_file'];
			array_walk($info['css_file'], 'Library::prependPATH', $dir);

			$info['php_file'] = (array)$info['php_file'];
			array_walk($info['php_file'], 'Library::prependPATH', $dir);


			$info['file'] = array(
				'js' => $info['js_file'],
				'css' => $info['css_file'],
				'php' => $info['php_file']
			);

			self::register($info['type'], $info['name'], $info['file'], $info['importance']);
		}
	}

	public static function prependPATH (&$x, $key, $dir) {
		$x = CC_PUB_ROOT.CC_CONTENT.'libraries/'.$dir.$x;
	}
}
class JS extends Library {
	public static function queue ($file, $importance = 0){
		self::queueFile('js', $file, $importance);
	}
	public static function queueString ($string, $importance = 0) {
		self::queueFile('js-string', $string, $importance);
	}
	public static function load () {
		$sortedFiles = (array)Library::$queue['js'];
		if(empty($sortedFiles)) {
			return;
		}
		ksort($sortedFiles);

		$sortedStrings = (array)Library::$queue['js-string'];
		if(empty($sortedFiles)) {
			return;
		}
		ksort($sortedStrings);

		foreach($sortedFiles as $imp => $files) {
			foreach($files as $file) {
				$r .= sprintf("\n".'<script type="text/javascript" src="%s"></script>', $file);
			}
		}
		
		foreach($sortedStrings as $imp => $files) {
			foreach($files as $file) {
				$r .= sprintf("\n".'<script type="text/javascript">%s</script>', $file);				
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
function queue_js_string ($string, $importance = 0) {
	JS::queueString($string, $impotance);
}
function load_css() {
	CSS::load();
}
function load_js() {
	JS::load();
}
Hooks::bind('system_ready','Library::bootstrap', -100);

?>