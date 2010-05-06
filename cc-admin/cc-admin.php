<?php
class Admin {
	/**
	 * @var Contains the handle to class for static access.
	 */
	static private $handle;

	/**
	 * @var Contains registered menu items.
	 */
	private $menu = array();

	/**
	 * @var The current menu item.
	 */
	private $current;

	/**
	 * @var Menu items added by plugins.
	 */
	private $plugin_menu = array();

	public function __construct () {
		$this->parseUrl();
	}

	/**
	 * Generates a multilevel array of the nav items.
	 */
	private function buildMenu () {
		print_r($this->plugin_menu);
	}

	public function includeDesign () {

	}

	private function includeBasePages () {
		$pages = glob(CC_ADMIN.'pages/*.php');
		foreach($pages as $page) {
			include $page;
		}
	}

	/**
	 * Registers a top level page for the administration.
	 *
	 * @param string $unique_slug
	 * @param string $menutitle
	 * @param callback $callback
	 * @param int $weight
	 */
	public function _registerPage ($unique_slug, $menutitle, $callback, $weight = 0) {
		if(array_key_exists($unique_slug, $this->plugin_menu)) {
			trigger_error("Admin menu item '$unique_slug' already exists, overwriting previous handle.", E_WARNING);
		}
		$this->plugin_menu[$unique_slug] = array(
			'title' => $menutitle,
			'callback' => $callback,
			'weight' => $weight,
			'children' => array()
		);
		return true;
	}

	/**
	 * Registers a submenu page for the administration.
	 *
	 * @param string $parent_slug
	 * @param string $unique_slug
	 * @param string $menutitle
	 * @param callback $callback
	 * @param int $weight
	 */
	public function _registerSubpage ($parent_slug, $unique_slug, $menutitle, $callback, $weight = 0) {
		if(!array_key_exists($parent_slug, (array)$this->plugin_menu)) {
			trigger_error("Admin parent menu item '$parent_slug' for '$unique_slug' doesn't exist!", E_USER_WARNING);
		}
		$this->plugin_menu[$parent_slug]['children'][$unique_slug] = array(
			'title' => $menutitle,
			'callback' => $callback,
			'weight' => $weight
		);
		return true;
	}

	/**
	 * Looks at the url and determins what needs to be included.
	 */
	private function parseUrl () {
		$page = $_GET['page'];

		if(empty($page)) {
			$page = Settings::get('admin', 'homepage');
		}

		$this->current = $page;
	}

	public static function bootstrap () {
		self::$handle = new Admin();
		self::$handle->includeBasePages();

		plugin('admin_menu');

		self::$handle->buildMenu();
	}

	/**
	 * A wrapper for Admin->registerPage
	 */
	public static function registerPage () {
		return call_user_func_array(array(self::$handle, '_registerPage'), func_get_args());
	}

	/**
	 * A wrapper for Admin->registerSubpage
	 */
	public static function registerSubpage () {
		return call_user_func_array(array(self::$handle, '_registerSubpage'), func_get_args());
	}
}

Hooks::bind('system_ready', 'Admin::bootstrap', 10);

?>
