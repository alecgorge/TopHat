<?php

class Uploader {
	private $callback;
	private $name;
	private $id;
	private $text;
	private $options;

	/**
	 * This allows for easy usage of Uploadify. You will have to style things yourself. This class will just generate the HTML. Uploader instances must be instantiated before the page is displayed. createHTML() can be called at any time.
	 *
	 * @param string $name When files are uploaded they will appear in the $_FILES superglobal with this key. If unsure, use "Filedata".
	 * @param callback $callback The function/method to call to handle an upload.
	 * @param string $uploadButtonText The text to display on the upload button. Default is false.
	 * @param string $uploadifyOptions Options to pass to Uploadify in the form of a PHP associative array. See the options section of this page for details: http://www.uploadify.com/documentation/ .
	 */
	public function  __construct($name, $callback, $uploadButtonText = false, $uploadifyOptions = array()) {
		$this->name = $name;
		$this->callback = $callback;
		$this->id = abs(crc32(microtime(true)));
		$this->text = ($uploadButtonText ? $uploadButtonText : __('admin', 'upload'));
		$this->options = $uploadifyOptions;

		if(array_key_exists($this->name, $_FILES)) {
			Hooks::bind('system_ready', array($this, 'runCallback'));
		}

		load_library('uploadify');
		$js_options = json_encode(array_merge($this->options, array(
			'queueID' => $this->id."_queue",
			'uploader' => CC_PUB_ROOT.CC_CONTENT.'libraries/js/uploadify/uploadify.swf',
			'cancelImg' => CC_PUB_ROOT.CC_CONTENT.'libraries/js/uploadify/cancel.png',
			'script' => CC_PUB_ROOT,
			'displayData' => 'both'
		)));

		$js = <<<EOT
$(function () {
	$('#{$this->id}_button').uploadify({$js_options});
});
EOT;
		queue_js_string($js);
	}

	/**
	 * Returns the HTML that actually displays the upload box.
	 *
	 * @return string The HTML.
	 */
	public function createHTML () {
		$upload_box = <<<EOT
<div class="cc_upload_box">
	<div class="cc_upload_box_queue" id="{$this->id}_queue"></div>
	<div class="cc_upload_box_buttons">
		<button class="cc_upload_button">{$this->text}</button>
		<div id="{$this->id}_button" class="cc_upload_flash">Select Files</div>
		<br class="clear"/>
	</div>
</div>
EOT;

		return $upload_box;
	}

	public function runCallback () {
		call_user_func($this->callback, $_FILES[$this->name]);
	}
}

/**
 * @todo Uploads class to manage uploads folder.
 * @todo Image manipulation class.
 */
class Uploads {
	public static $absPath;

	public static function bootstrap () {
		self::$absPath = CC_ROOT.CC_UPLOADS;
		self::testWritable(self::$absPath);
	}

	private static function testWritable ($dir) {
		if(!is_writable($dir)) {
			echo $dir." ".__('admin', 'is-not-writable');
			die();
		}
	}

	/**
	 * Returns an array of the absolute path to all the files in the given folder matching the given pattern.
	 *
	 * @param string $folder A sub folder of CC_UPLOADS. For example "test/". Default is false.
	 * @param string $pattern A PCRE that files must match.
	 * @return array An array of the files matching the given parameters.
	 */
	public static function getAllFiles ($folder = false, $pattern = false) {
		$dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(self::$absPath.($folder ? $folder : ""), FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_SELF | FilesystemIterator::UNIX_PATHS));

		if($pattern !== false) {
			$dir = new RegexIterator($dir, $pattern, RecursiveRegexIterator::GET_MATCH);
		}

		$r = array();
		foreach($dir as $k => $v) {
			$r[] = $k;
		}

		return filter('uploads_all_files', $r);
	}

	/**
	 * Returns an array of the absolute path to all the folders in the given folder matching the given pattern.
	 *
	 * @param string $folder A sub folder of CC_UPLOADS. For example "test/". Default is false.
	 * @param string $pattern A PCRE that folders must match.
	 * @return array An array of folders matching the given parameters.
	 */
	public static function getAllFolders ($folder = false, $pattern = false) {
		$dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(self::$absPath.($folder ? $folder : ""), FilesystemIterator::UNIX_PATHS), RecursiveIteratorIterator::SELF_FIRST);

		if($pattern !== false) {
			$dir = new RegexIterator($dir, $pattern, RecursiveRegexIterator::GET_MATCH);
		}

		$r = array();
		foreach($dir as $k => $v) {
			if(is_dir($k)) {
				$r[] = $k."/";
			}
		}

		return filter('uploads_all_folders', $r);
	}

	/**
	 * Returns Windows paths into Unix paths.
	 *
	 * @param string $file
	 * @return string Replaces all \'s with /'s.
	 */
	public static function normalize ($file) {
		return mb_str_replace('\\', '/', $file);
	}

	/**
	 * Adds a file to the uploads directory. Useful
	 *
	 * @param string $src The path to the file that will be added the the uploads directory.
	 * @param string $dest The path relative to CC_ROOT.CC_UPLOADS to place the file.
	 * @param boolean $move Defaults to true. If false, the file will be copied instead of being moved.
	 * @return Uploads Returns the static reference to Uploads.
	 */
	public static function addFile ($src, $dest, $move = true) {
		plugin('uploads_add', array($src, $dest, $move));
		if($move) {
			rename($src, self::$absPath.$dest);
		}
		else {
			copy($src, self::$absPath.$dest);
		}
		return self;
	}

	/**
	 * Creates a folder with proper permissions.
	 *
	 * @param string $name Name of the folder without the trailing slash.
	 * @param string $folder Name of the folder to create $name in. Relative to CC_CORE.CC_CONTENT
	 * @return mixed The absolute path to the folder if creation was successful, otherwise false.
	 */
	public static function createFolder ($name, $folder = false) {
		$new = self::$absPath.($folder ? $folder : "").rtrim($name, "/")."/";
		self::testWritable(self::$absPath.($folder ? $folder : ""));

		$res = mkdir($new);

		if(function_exists('chmod')) {
			// 0777 because execute commands are needed for some reason to properly iterate over the folder
			// and to make new folders. don't ask me why...
			$res2 = chmod($new, octdec(777));
		}
		return ($res && $res2 ? $new : false);
	}

	/**
	 * Makes files relative to CC_ROOT instead of being the absolute path. Useful to find the public link: CC_PUB_ROOT."/".Uploads::unbase("/var/www/content/uploads/test.jpg").
	 *
	 * @param string $file
	 * @return string The relative path (from CC_ROOT) to the file.
	 */
	public static function unbase ($file) {
		return mb_substr($file, mb_strlen(CC_ROOT));
	}
}

Uploads::bootstrap();