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
	 * @var The part of the array where current menu item resides.
	 */
	private $currentItem;

	/**
	 * @var Menu items added by plugins.
	 */
	private $plugin_menu = array();

	public function __construct () {
		$this->parseUrl();
	}

	private function sortMenu ($arr1, $arr2) {
		if($arr1['weight'] == $arr2['weight']) {
			return 0;
		}

		return ($arr1['weight'] < $arr2['weight']) ? -1 : 1;
	}

	/**
	 * Static wrapper for buildMenu();
	 *
	 * @return string The menu.
	 */
	public static function menu () {
		return self::$handle->buildMenu();
	}

	public static function content () {
		return self::$handle->getContent();
	}

	public static function title () {
		$current = self::$handle->getCurrentItem();
		return $current['title'];
	}

	/**
	 * Get a part of an array that has the slug, title and callback for a particular admin page
	 *
	 * @return array The part of the array for the current admin page.
	 */
	public function getCurrentItem () {
		if(!empty($this->currentItem)) {
			return $this->currentItem;
		}

		if(empty($_GET['page'])) {
			$_GET['page'] = 'dashboard';
		}
		$parts = (array) explode('/', $_GET['page'], 2);

		// sub menu
		if(count($parts) > 1) {
			$this->currentItem = $this->plugin_menu[$parts[0]]['children'][$parts[1]];
		}
		// main level
		else {
			$this->currentItem = $this->plugin_menu[$parts[0]];
		}
		return $this->currentItem;
	}

	public function getContent () {
		$current = $this->getCurrentItem();
		call_user_func($current['callback']);
   	}
	
	/**
	 * Generates a multilevel array of the nav items.
	 */
	private function buildMenu () {
		// make sure menu is in order.
		$menu = $this->plugin_menu;
		uasort($menu, array($this, 'sortMenu'));

		$html = "";

		// first level menu items
		foreach($menu as $slug => $contents) {
			$title = $contents['title'];
			$callback = $contents['callback'];
			$children = $contents['children'];
			uasort($children, array($this, 'sortMenu'));

			// deal with children
			if(!empty($children)) {
				$sub_html = "\n\t\t<ul>\n";
				foreach($children as $sub_slug => $sub_content) {
					$sub_title = $sub_content['title'];
					$sub_callback = $sub_content['callback'];

					$sub_html .= sprintf("\t\t\t<li class='admin-submenu'><a href='%s' title='Admin Page: %s'>%2\$s</a></li>\n", $this->makeUrl($slug.'/'.$sub_slug), $sub_title);
				}
				$sub_html .= "\t\t</ul>";
			}

			$html .= sprintf("\n\t<li><a href='%s' title='Admin Page: %s'>%s</a>%s\n\t</li>", $this->makeUrl($slug), $title, $title, $sub_html);
			unset($sub_html);

       	}

		$html = "<ul>".$html."\n</ul>\n";

		return $html;
	}

	public function makeUrl ($slug) {
		return filter('admin-makeurl', CC_PUB_ADMIN.'?page='.$slug);
   	}

	public function includeDesign () {
		require CC_ADMIN.'design/index.tpl.php';
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

		//self::$handle->buildMenu();
		self::$handle->includeDesign();
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