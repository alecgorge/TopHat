<?php
/**
 * This class manages the plugins. Plugin is the class that is used to make new plugins.
 *
 * There are 3 levels of plugins:
 * * available: the plugin file exists but it is not listed as activated in the database
 * * registered: the plugin file exists and it is listed as activated in the database
 * * active: the plugin has initalized in Plugin __construct
 */
class Plugins {
	/**
	 * @var array An associcative array with the key being a plugins safe name and the value being a reference to the plugin.
	 */
	private static $registered = array();

	/**
	 * @var array An associcative array with the key being a plugins name and the value being a reference to the plugin.
	 */
	private static $active = array();
	private static $activePInfo = array();
	private static $pInfo = array();

	public static function add(&$plugin) {
		self::$active[trim($plugin->dir,'/')] = $plugin;
		$vals = array(
			'name' => $plugin->name,
			'desc' => $plugin->description,
			'author' => $plugin->author,
			'version' => $plugin->version,
			'dir' => trim($plugin->dir,'/')
		);
		self::$activePInfo[] = $vals;
		self::$pInfo[] = $vals;
	}

	public static function getActive() {
		return self::$active;
	}

	public static function getActiveInfo() {
		return self::$activePInfo;
	}

	/**
	 * Registers a plugin as valid.
	 * 
	 * Will overwrite previous registrations. 
	 *
	 * @param object &$plugin An instance of the RegisteredPlugin object.
	 */
	public static function register(&$plugin) {
		self::$registered[$plugin->makeSlug()] = $plugin;
	}

	/**
	 * The bootstrap for the plugins.
	 *
	 * It finds the list of active plugins from the database and compares them to the available plugins.
	 */
	public static function bootstrap () {
		$files = glob(CC_ROOT.CC_PLUGINS.'*/plugin.php');
		foreach($files as $file) {
			require_once $file;
			// $plugin is a full path, we don't want that.
			$plugin = explode(CC_ROOT.CC_PLUGINS, $file, 2);
			$plugin = explode('/plugin.php', $plugin[1]);
			$possiblePlugins[] = $plugin[0];
		}

		$db = DB::select('plugins', '*', array('active = ?', 1));
		$activePlugins = $db->fetchAll(PDO::FETCH_ASSOC);

		foreach($activePlugins as $row) {
			if(array_search($row['name'], $possiblePlugins) !== false) {
				$bootstrap = self::$active[$row['name']]->bootstrap;
				if(is_callable($bootstrap)) {
					call_user_func($bootstrap);
				}
			}
		}

		self::runAll();
	}

	/**
	 * Includes all the plugins!
	 */
	private static function runAll () {
		if(empty(self::$registered)) return;

		foreach(self::$registered as $plugin) {
			$plugin->run();
		}
	}

	/**
	 * Returns the list of active plugins.
	 *
	 * @return array The array of active plugins.
	 */
	public static function getAll () {
		return self::$registered;
	}

	/**
	 * Returns an array of all the possible plugins.
	 *
	 * @return array The array of plugins, one name per line.
	 */
	public static function getPluginList () {
		return self::$pInfo;
		$glob = glob(CC_ROOT.CC_PLUGINS.'*/plugin.php');
		foreach($glob as $plugin) {
			// $plugin is a full path, we don't want that.
			$plugin = explode(CC_ROOT.CC_PLUGINS, $plugin, 2);
			$plugin = explode('/plugin.php', $plugin[1]);
			$r[] = $plugin[0];
		}
		return $r;
	}

	/**
	 * Makes sure a given plugin slug (the folder name) is valid.
	 *
	 * @param string $name The folder name of the plugin.
	 * @return bool Is the plugin valid?
	 */
	public static function validate($name) {
		return file_exists(CC_PLUGINS.$name.'/plugin.php');
	}
}
// Plugins::bootstrap();
Hooks::bind('system_after_content_load', 'Plugins::bootstrap');

/**
 * A class for each registered plugin
 */
class RegisteredPlugin {
	/**
	 * @var bool Is plugin active?
	 */
	private $active;

	/**
	 * @var string The name of the folder.
	 */
	private $name;

	/**
	 * @var string The path to the include file.
	 */
	private $pluginFile;

	/**
	 * @var string Options for the plugin. Not used yet.
	 */
	private $options;

	/**
	 *
	 * @param string $folder_name The foldername of the plugin.
	 * @param array $options The array of options from the DB.
	 * @param bool $active Whether to make the plugin active or not.
	 */
	public function __construct ($folder_name, $options, $active = false) {
		$this->name = $folder_name;
		$this->active = $active;
		$this->options = $options;
		$this->pluginFile = CC_ROOT.CC_PLUGINS.$this->name.'/plugin.php';
	}

	/**
	 * Is the plugin valid?
	 *
	 * @return bool Is the plugin valid?
	 */
	public function validate () {
		return file_exists($this->pluginFile);
	}

	/**
	 * This function includes the plugins plugin.php file.
	 */
	public function run () {
		require_once $this->pluginFile;
	}
	
	/**
	 * Creates a URL safe slug of the plugin's name
	 *
	 * @return string A URL-safe slug.
	 */
	public function makeSlug () {
		return UTF8::slugify($this->name);
	}

}

/**
 * A class used to make plugins!
 */
class Plugin {
	/**
	 * @var string The name of the plugin.
	 */
	public $name;
	/**
	 * @var string The author of the plugin.
	 */
	public $author;
	/**
	 * @var string A description of the plugin. Can be any length, but should be long enough to describe the plugin.
	 */
	public $description;
	/**
	 * @var string The path to the homepage of the plugin.
	 */
	public $link = '';
	/**
	 * @var array An array of the binds made.
	 */
	public $binds;
	/**
	 * @var array An array of the filters made.
	 */
	public $filters;
	/**
	 * @var string A url safe version of the plugin name.
	 */
	public $slug;
	/**
	 * @var string The folder the plugin is in under CC_PLUGINS
	 */
	public $dir;

	public $version;

	public $bootstrap;

	public function bootstrap ($callback) {
		$this->bootstrap = $callback;
	}

	/**
	 * Create a new Plugin!
	 *
	 * @param string $name The name of the plugin.
	 * @param string $author The creator of the plugin.
	 * @param string $description A description of the plugin. Can be any length, but should be long enough to describe the plugin.
	 * @param string $version
	 * @param string $link Optional. The path to the homepage of the plugin.
	 */
	public function  __construct($name, $author, $description, $version, $link = '') {
		$backtrace = debug_backtrace();
		$caller = dirname($backtrace[0]['file']);
		$caller = str_replace('\\', '/', $caller);
		$caller = explode('/', rtrim($caller,'/\\'));
		$folderName = end($caller).'/';

		$this->dir = $folderName;
		$this->version = $version;
		$this->name = $name;
		$this->author = $author;
		$this->description = $description;

		if(Validate::link($link)) {
			$this->link = $link;
		}

		Plugins::add(&$this);
	}

	/**
	 * Creates a URL safe slug of the plugin's name
	 *
	 * @return string A URL-safe slug.
	 */
	public function makeSlug () {
		if(empty($this->slug)) {
			$this->slug = UTF8::slugify($this->name);
		}
		return $this->slug;
	}

	/**
	 * Returns the plugin's name.
	 *
	 * @return string The plugin's name.
	 */
	public function getName () {
		return $this->name;
	}

	/**
	 * A wrapper for Hooks::bind();
	 */
	public function bind($hook, $callback, $priority = 0) {
		$this->binds[] = array($hook, $callback);
		Hooks::bind($hook, $callback, $priority);
	}

	/**
	 * A wrapper for Filters::bind();
	 */
	public function filter($hook, $callback, $priority = 0) {
		$this->filters[] = array($hook, $callback);
		Filters::bind($hook, $callback, $priority);
	}

	/**
	 * A wrapper for Settings::set();
	 */
	public function set($key, $value) {
		Settings::set($this->getName(), $key, $value);
	}

	/**
	 * A wrapper for Settings::get() {
	 *
	 * @returns mixed The value for $key.
	 */
	public function get($key) {
		return Settings::get($this->getName(), $key);
	}

	/**
	 * @return string The publicly accessible absolute path to the plugin's directory.
	 */
	public function pluginPublicDir () {
		return CC_PUB_ROOT.CC_PLUGINS.$this->dir;
	}

	/**
	 * @return string The absolute path to the plugin's directory in the filesystem.
	 */
	public function pluginDir () {
		return CC_ROOT.CC_PLUGINS.$this->dir;
	}
}
?>