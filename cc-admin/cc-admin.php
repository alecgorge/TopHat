<?php
class Admin {
	/**
	 * @var Admin Contains the handle to class for static access.
	 */
	public static $handle;

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

	public $select_menu = array();

	public static $content;

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
	 * Returns a boolean whether user is logged in.
	 */
	public function isLoggedIn () {
		return Users::isValid();
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
		return self::$content;
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
			$_GET['page'] = 'content';
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

		if(is_null($this->currentItem)) {
			$this->currentItem = array('title' => __('admin', '404'));
		}

		return $this->currentItem;
	}

	public function getContent () {
		$current = $this->getCurrentItem();

		if(is_callable($current['callback'])) {
			self::$content = call_user_func($current['callback']);
		}
		else {
			self::$content = "<h2>".__('admin', "404").'</h2>';
		}
   	}

	public static function getAdminPageOptions () {
		ksort(self::$handle->select_menu);
		return filter('admin_admin_page_options', self::$handle->select_menu);
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
				$sub_html = "\n\t\t<ul class='admin-submenu'>\n";
				foreach($children as $sub_slug => $sub_content) {
					$sub_title = $sub_content['title'];
					$sub_callback = $sub_content['callback'];

					$sub_html .= sprintf("\t\t\t<li class='%s'><a href='%s' title='Admin Page: %s'>%3\$s</a></li>\n", (self::isPage($slug.'/'.$sub_slug) ? ' current' : ''), $this->makeUrl($slug.'/'.$sub_slug), $sub_title);
				}
				$sub_html .= "\t\t</ul>";
			}
			$html .= sprintf("\n\t<li%s><a href='%s' title='Admin Page: %s'>%s</a>%s\n\t</li>", (self::isPage($slug) ? ' class="current"' : ''), $this->makeUrl($slug), $title, $title, $sub_html);
			unset($sub_html);

       	}

		$html = "<ul>".$html."\n</ul>\n";

		return $html;
	}

	public function makeUrl ($slug) {
		return filter('admin-makeurl', CC_PUB_ADMIN.'?page='.$slug);
   	}

	public static function link ($slug, $args = array(), $attr = array()) {
		if(!empty($args)) {
		    foreach($args as $key => $value) {
			$append .= "&$key=$value";
		    }
		}
		return self::$handle->makeUrl($slug).$append;
	}

	public function includeDesign () {
		require CC_ADMIN.'design/index.tpl.php';
   	}

	public function includeLogin () {
		require CC_ADMIN.'design/login.tpl.php';
   	}

	private function includeBasePages () {
		$pages = glob(CC_ADMIN.'pages/*.php');
		foreach($pages as $page) {
			include $page;
		}

		$subPages = glob(CC_ADMIN.'pages/*/*.php');
		foreach($subPages as $page) {
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
			trigger_error("Admin menu item '$unique_slug' already exists, overwriting previous handle.", E_USER_WARNING);
		}
		$this->select_menu[$unique_slug] = $menutitle;
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
		$this->select_menu[$parent_slug.'/'.$unique_slug] = "&#8212; $menutitle";
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
		if(!$this->isLoggedIn()) {
			$this->includeLogin();
			exit();
		}

		$page = $_GET['page'];
		if($page == 'logout') {
			plugin('admin_logout');
		}

		if(empty($page)) {
			$page = Settings::get('admin', 'homepage', true);
			$_GET['page'] = $page;
		}

		$this->current = $page;
	}

	public static function bootstrap () {
		self::$handle = new Admin();
		self::$handle->includeBasePages();

		plugin('admin_menu');
		plugin('admin_sidebar');

		self::$handle->getContent();
		AdminSidebar::cache();

		//self::$handle->buildMenu();
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

	/**
	 * Logs the user out.
	 */
	public static function logout () {
		Users::logout();
		cc_redirect(CC_PUB_ROOT);
		exit();
   	}

	public static function isPage ($page) {
		return $_GET['page'] == $page;
	}

	public static function doIncludeDesign () {
		self::$handle->includeDesign();
	}

}
Hooks::bind('admin_logout', 'Admin::logout', 100);
Hooks::bind('system_complete', 'Admin::bootstrap');
Hooks::bind('system_complete', 'Admin::doIncludeDesign', 100);

?>
