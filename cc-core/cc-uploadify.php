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
	public function  __construct($name, $callback = false, $uploadButtonText = false, $uploadifyOptions = array()) {
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
			'uploader' => TH_PUB_ROOT.TH_CONTENT.'libraries/js/uploadify/uploadify.swf',
			'cancelImg' => TH_PUB_ROOT.TH_CONTENT.'libraries/js/uploadify/cancel.png',
			'script' => TH_PUB_ROOT,
			'displayData' => 'both'
		)));

		$js = <<<EOT
$(function () {
	$('#{$this->id}_button').uploadify({$js_options});
	$('#{$this->id}_upload_box button').click(function () {
		$('#{$this->id}_button').uploadifyUpload();
	});
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
<div class="cc_upload_box" id="{$this->id}_upload_box">
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

	public function getID () {
		return $this->id;
	}

	public function getName () {
		return $this->name;
	}

	public function runCallback () {
		if(is_callable($this->callback)) {
			call_user_func($this->callback, $_FILES[$this->name]);
		}
	}
}

class UploadHandler {
	private $post_field;
	private $callbacks;

	public function __construct($src, $default_callbacks = array()) {
		if($src instanceof Uploader) {
			$this->post_field = $src->getName();
		}
		elseif(is_string($src)) {
			$this->post_field = $name;
		}

		$this->callbacks = (array)$default_callbacks;

		if(array_key_exists($this->post_field, $_FILES)) {
			Hooks::bind('system_ready', array($this, 'runCallbacks'));
		}
	}

	public function addCallback($callback) {
		$this->callbacks[] = $callbacks;
	}

	private function runCallbacks() {
		$data = $_FILES[$this->post_field];
		foreach((array)$callbacks as $c) {
			if(is_callable($c)) {
				call_user_func_array($c, $data);
			}
		}
	}
}

class DefaultUploadHandler extends UploadHandler {
	const FILTER_CALLBACK = 2;
	const ERROR_CALLBACK = 3;
	const UPLOAD_DIRECTORY = 4;
	const SUCCESS_CALLBACK = 5;
	const FINISHED_CALLBACK = 6;

	private $behaviours = array(
		self::FILTER_CALLBACK => false,
		self::ERROR_CALLBACK => false,
		self::SUCCESS_CALLBACK => false
	);


	public function __construct($src, $behaviours) {
		parent::__construct($src, array(
			array(
				$this,
				'handleUpload'
			)
		));

		$this->behaviours[self::UPLOAD_DIRECTORY] = TH_ROOT.TH_UPLOADS;

		$this->behaviours = $this->behaviours + $behaviours;

		$this->addCallback(array($this, 'handleUpload'));
	}

	public function handleUpload ($files) {
		foreach((array)$files as $file) {
			$name = basename($file['name']);
			$size = $file['size'];
			$tmpname = $file['tmp_name'];
			$err = $file['error'];

			if($err === 0) {
				$file_good = true;
				if(is_callable($this->behaviours[self::FILTER_CALLBACK])) {
					$file_good = call_user_func_array($this->behaviours[self::FILTER_CALLBACK], array($name, $size, $this->behaviours));
				}
				
				if($file_good === false) {
					continue;
				}

				$destPath = $this->behaviours[self::UPLOAD_DIRECTORY].$name;

				if(is_string($file_good)) {
					$destPath = $this->behaviours[self::UPLOAD_DIRECTORY].$file_good;
				}

				// don't use move_uploaded_file becuase this way we can get around the size limit set by hosts
				// not safemode compatible, but who cares?
				if(rename($tmpname, $destPath)) {
					if(is_callable($this->behaviours[self::SUCCESS_CALLBACK])) {
						call_user_func_array($this->behaviours[self::SUCCESS_CALLBACK], array($destPath));
					}
				}
			}
			else {
				if(is_callable($this->behaviours[self::ERROR_CALLBACK])) {
					call_user_func_array($this->behaviours[self::ERROR_CALLBACK], array($file, $this->behaviours));
				}
			}
		}
		if(is_callable($this->behaviours[self::FINISHED_CALLBACK])) {
			call_user_func_array($this->behaviours[self::FINISHED_CALLBACK], array($file, $this->behaviours));
		}
	}
}

/**
 * @todo Uploads class to manage uploads folder.
 * @todo Image manipulation class.
 */
class Uploads {
	public static $absPath;

	public static function bootstrap () {
		self::$absPath = TH_ROOT.TH_UPLOADS;
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
	 * @param string $folder A sub folder of TH_UPLOADS. For example "test/". Default is false.
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

	public static function getFilesArray ($folder = false, $pattern = false) {
		return self::fillFileNodes(new DirectoryIterator(self::$absPath.($folder ? $folder : "")), $pattern);
	}

	private static function fillFileNodes(DirectoryIterator $dir, $pattern = false) {
		$data = array();
		foreach($dir as $node) {
			if ($node->isDir() && !$node->isDot()) {
				$data[$node->getPathname().DIRECTORY_SEPARATOR] = self::fillFileNodes(new DirectoryIterator($node->getPathname()));
			} else if ($node-> isFile()) {
				if(!$pattern || preg_match($pattern, $node->getFilename())) {
					$data[] = $node->getPathname();
				}
			}
		}
		return $data;
	}

	/**
	 * Returns an array of the absolute path to all the folders in the given folder matching the given pattern.
	 *
	 * @param string $folder A sub folder of TH_UPLOADS. For example "test/". Default is false.
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
	 * @param string $dest The path relative to TH_ROOT.TH_UPLOADS to place the file.
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
	 * @param string $folder Name of the folder to create $name in. Relative to TH_CORE.TH_CONTENT
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
	 * Makes files relative to TH_ROOT instead of being the absolute path. Useful to find the public link: TH_PUB_ROOT."/".Uploads::unbase("/var/www/content/uploads/test.jpg").
	 *
	 * @param string $file
	 * @return string The relative path (from TH_ROOT) to the file.
	 */
	public static function unbase ($file) {
		return mb_substr($file, strlen(utf8_decode(TH_ROOT)));
	}
}

Uploads::bootstrap();