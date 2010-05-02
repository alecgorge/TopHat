<?php
/**
 * This class manages the plugins. Plugin is the class that is used to make new plugins.
 */
class Plugins {
	/**
	 * @var array An associcative array with the key being a plugins safe name and the value being a reference to the plugin.
	 */
	private static $registered = array();
	
	/**
	 * Registers a plugin as valid.
	 * 
	 * Will overwrite previous registrations. 
	 *
	 * @param object &$plugin An instance of the Plugin object.
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
		$db = DB::select('plugins', '*', array('active = ?', 1));
		$activePlugins = $db->fetchAll(PDO::FETCH_ASSOC);

		var_dump($activePlugins);
	}

}
Hooks::bind('system_ready', 'Plugins::bootstrap');

/**
 * A class used to make plugins!
 */
class Plugin {
	/**
	 * @var string The name of the plugin.
	 */
	private $name;
	/**
	 * @var string The author of the plugin.
	 */
	private $author;
	/**
	 * @var string A description of the plugin. Can be any length, but should be long enough to describe the plugin.
	 */
	private $description;
	/**
	 * @var string The path to the homepage of the plugin.
	 */
	private $link = '';
	/**
	 * @var bool Is the plugin active in the users settings?
	 */
	private $active = false;

	/**
	 * Create a new Plugin!
	 *
	 * @param string $name The name of the plugin.
	 * @param string $author The creator of the plugin.
	 * @param string $description A description of the plugin. Can be any length, but should be long enough to describe the plugin.
	 * @param string $link Optional. The path to the homepage of the plugin.
	 */
	public function  __construct($name, $author, $description, $link = '') {
		$this->name = $name;
		$this->author = $author;
		$this->description = $description;

		if(Validate::link($link)) {
			$this->link = $link;
		}


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
?>