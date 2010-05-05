<?php

class Content {
	private static $current;
	private static $currentId;
	private static $parentPath;

	private static $navArray;
	private static $idLookup;
	private static $idLookupClean;
	private static $childLookup;
	private static $urlLookup;
	private static $breadcrumbs;

	private static $content = array();
	private static $navCache;
	private static $navCheck;

	public static function bootstrap () {
		self::parseNavigation();
		self::parseUrl();
	}
	
	public static function parseNavigation () {
		if(empty(self::$navArray)) {
			$refs = array();
			$data = array();
			$options = false;
			$list = array();
			
			$pages = DB::select('content', '*', array('type = ?', 'page'), array('weight', 'desc'));

			while($data = $pages->fetch(PDO::FETCH_ASSOC)) {
				$data = filter('content_parsenavigation_data', $data);
				$options = unserialize(stripcslashes($data['options']));
				$thisref = &$refs[ $data['id'] ];

				$continue = true;
				plugin('content_parsenavigation_before', array($thisref, $data, $options, &$continue));

				if($continue) {
					$thisref['id'] = (int) $data['id'];
					$thisref['menutitle'] = $data['menutitle'];
					$thisref['slug'] = $data['slug'];
					if(!empty($options['external'])) {
						$external[$thisref['id']] = $options['external'];
					}
					if ($data['parent_id'] == 0) {
						$list[$thisref['id']] = &$thisref;
					} else {
						$refs[ $data['parent_id'] ]['children'][ $thisref['id'] ] = &$thisref;
					}
				}

				plugin('content_parsenavigation_after', array($thisref, $data, $options));
				
				$list2[$thisref['id']] = $thisref['menutitle'];
				$list3[$thisref['id']] = $thisref['slug'];
				$list4[$thisref['id']] = (int) $data['parent_id'];
			}

			plugin('content_parsenavigation_afterloop', array($list, $list2, $list3, $list4));
			$list = filter('content_parsenavigation_nav', $list);
			$list2 = filter('content_parsenavigation_idlookup', $list2);
			$list3 = filter('content_parsenavigation_idlookupclean', $list3);
			$list4 = filter('content_parsenavigation_childlookup', $list4);


			self::$navArray = $list;
			self::$idLookup  = $list2;
			self::$idLookupClean = $list3;
			self::$childLookup = $list4;
			
			self::$urlLookup = self::generateIdLookups();
			self::$breadcrumbs = self::generateBreadcrumbs();

			plugin('content_parsenavigation_aftergeneration');

			return self::$navArray;
		}
		else {
			return self::$navArray;
		}		
	}

	private static function generateIdLookups ($path = null, $children = array()) {
		if($path === null) {
			$children = self::$navArray;
		}

		foreach($children as $key => $value) {
			$hasChildren = array_key_exists('children', $value);

			if($hasChildren) {
				$r[$value['id']] = $path.'/'.$value['slug'];
				$r += self::generateIdLookups($path.'/'.$value['slug'], $value['children']);
			}
			else {
				$r[$value['id']] = $path.'/'.$value['slug'];
			}
		}
		return $r;
	}

	private static function generateBreadcrumbString ($path = null, $children = array()) {
		if($path === null) {
			$children = self::$navArray;
		}

		foreach($children as $key => $value) {
			$hasChildren = array_key_exists('children', $value);

			if($hasChildren) {
				$r[$value['id']] = $path."\0".$value['menutitle'];
				$r += self::generateBreadcrumbString($path."\0".$value['menutitle'], $value['children']);
			}
			else {
				$r[$value['id']] = $path."\0".$value['menutitle'];
			}
		}
		return $r;
	}

	private static function generateBreadcrumbs ($path = null, $children = array()) {
		$breadcrumbs = self::generateBreadcrumbString();
		foreach($breadcrumbs as $id => $path) {
			$r[$id] = array_remove_empty(explode("\0", $path));
		}
		return $r;
	}

	public static function currentId () {
		return self::$currentId;
	}

	/**
	 * This function returns HTML from the site's navigation.
	 *
	 * @param array $options The options for the nav generation. It is an associative array. Any items not named will use the default: <code>array('root' => "\n<ul id='ul-navigation'>\n%s\n</ul>\n",
	 *	'child' => "\n<ul class='ul-sub-navigation'>\n%s\n</ul>\n",
	 *	'item' => "\n\t<li><a href='%2\$s' title='%1\$s'>%1\$s</a></li>",
	 *	'itemSelected' => "\n\t<li class='selected'><a href='%2\$s' title='%1\$s'>%1\$s</a></li>",
	 *	'itemHasChild' => "\n\t<li><a href='%2\$s' title='%1\$s'>%1\$s</a>%3\$s</li>",
	 *	'itemHasChildSelected' => "\n\t<li class='selected'><a href='%2\$s' title='%1\$s'>%1\$s</a>%3\$s</li>");</code>
	 * @param bool $isChild Ignore. For internal use only.
	 * @param bool $inputArray Ignore. For internal use only.
	 * @return string The nav.
	 */
	public static function generateNavHTML($options, $isChild = false, $inputArray = array()) {
		if(!empty(self::$navCache) && self::$navCheck == serialize($options)) {
			return self::$navCache;
		}

		$defaults = array(
						'root' => "\n<ul id='ul-navigation'>\n%s\n</ul>\n",
						'child' => "\n<ul class='ul-sub-navigation'>\n%s\n</ul>\n",
						'item' => "\n\t<li><a href='%2\$s' title='%1\$s'>%1\$s</a></li>",
						'itemSelected' => "\n\t<li class='selected'><a href='%2\$s' title='%1\$s'>%1\$s</a></li>",
						'itemHasChild' => "\n\t<li><a href='%2\$s' title='%1\$s'>%1\$s</a>%3\$s</li>",
						'itemHasChildSelected' => "\n\t<li class='selected'><a href='%2\$s' title='%1\$s'>%1\$s</a>%3\$s</li>"
					);

		if(!$isChild) {
			$options = array_merge($defaults, $options);

			self::$navCheck = $options;
		}

		if(empty($inputArray)) {
			$inputArray = self::parseNavigation();
		}
		
		foreach($inputArray as $id => $item) {
			plugin('nav_each', $item);

			// has sub pages
			if(is_array($item['children'])) {
				$mt = UTF8::htmlentities($item['menutitle']);
				plugin('nav_haschild_before', array($item));

				$page = new Page($item['id']);
				$url = $page->url();

				// get the children's html
				$childrenHtml = self::generateNavHTML($options, true, $item['children']);

				// current page
				if(Content::currentId() == $item['id']) {
					// add the html on
					$thisOutput = sprintf($options['itemHasChildSelected'], $mt, $url, $childrenHtml);
				}
				else {
					// add the html on
					$thisOutput = sprintf($options['itemHasChild'], $mt, $url, $childrenHtml);
				}

				plugin('nav_haschild_after', array($item, $thisOutput, $output));
				$thisOutput = filter('nav_haschild_after', $thisOutput);

				$output .= $thisOutput;
			}
			// no subpages
			else {
				$mt = UTF8::htmlentities($item['menutitle']);
				plugin('nav_nochild_before', array($item));

				$page = new Page($item['id']);
				$url = $page->url();

				// current page
				if(Content::currentId() == $item['id']) {
					// add the html on
					$thisOutput = sprintf($options['itemSelected'], $mt, $url);
				}
				else {
					// add the html on
					$thisOutput = sprintf($options['item'], $mt, $url);
				}

				plugin('nav_nochild_after', array($item, $thisOutput, $output));
				$thisOutput = filter('nav_nochild_after', $thisOutput);

				$output .= $thisOutput;
			}
		}

		// if it is a child block, we need some special wrapping
		if($isChild) {
			plugin('nav_child', array($output));
			return filter('nav_child', sprintf($options['child'], $output));
		}
		else {
			plugin('nav_root', array($output));
			self::$navCache .= filter('nav_root', sprintf($options['root'], $output));
		}

		plugin('nav_finished', array(self::$navCache));
		self::$navCache = filter('nav_finished', self::$navCache);
		return self::$navCache;
	}

	public static function createPage ($title, $menutitle, $content, $settings = array(), $weight = 0, $parent = 0) {
		/**
		 * @todo Fix bug if menutitles are different but slugs are the same. (Blarg!!!! and Blarg!!!).
		 */
		$run = DB::select('content', 'COUNT(menutitle)', array('`menutitle` = ? AND `parent_id` = ?', $menutitle, $parent));
		$count = $run->fetchColumn();
		if($count > 0) {
			return false;
		}

		$smt = DB::insert('content', array(
			'title' => $title,
			'menutitle' => $menutitle,
			'content' => $content,
			'created'=> time(),
			'last_modified' => time(),
			'settings' => serialize($settings),
			'weight' => $weight,
			'parent_id'=>$parent,
			'type' => 'page'
		));

		if($smt == 1) {
			return true;
		}
	}

	public static function parseUrl () {
		$_GET['q'] = filter('content_parseurl', $_GET['q']);
		$parts = array_remove_empty(explode('/', $_GET['q']));

		if(count($parts) == 1) {
			if(self::isPage('/'.$parts[0])) {
				self::setCurrent(self::nameToId('/'.$parts[0]));
			}
			else {
				self::trigger404();
			}
		}
		elseif(count($parts) > 1) {
			$page = '/'.implode('/', $parts);
			if(self::isPage($page)) {
				self::setCurrent(self::nameToId($page));
			}
			else {
				self::trigger404();
			}
		}
		else {
			self::setCurrent(Settings::get('core', 'homepage id', true));
		}
	}

	public static function nameToId($name) {
		$id = array_search($name, self::$urlLookup);
		return $id;
	}

	public static function url ($id) {
		return self::$urlLookup[$id];
	}

	public static function trigger404 () {
		header('HTTP/1.0 404 Not Found');

		plugin('content_404');

		$e404t = filter('content_404title', '404 Error: Page Not Found');
		$e404mt = filter('content_404menutitle', 'Page Not Found');
		$e404c = filter('content_404content', 'Page not found. Return to the <a href="'.CC_PUB_ROOT.'">homepage</a>.');

		self::setTitle($e404t);
		self::setContent($e404c);
		self::setTheme(Settings::get('core', 'theme', true));
		self::$currentId = -1;
		self::$breadcrumbs[-1] = array($e404mt);
	}

	public static function isPage ($page) {
		$page = filter('content_ispage', $page);
		plugin('content_ispage', array($page));
		
		return (array_search($page, self::$urlLookup) === false ? false : true);
	}

	public static function setCurrent ($id) {
		self::$currentId = $id;
		self::$current = new Page($id);

		self::setContent(self::$current->getContent());
		self::setTitle(self::$current->getTitle());
		self::setMenutitle(self::$current->getMenutitle());
		self::setTheme(self::$current->getTheme());
		self::setSettings(self::$current->getSettings());
		self::setSlug(self::$current->getSlug());
	}

	public static function getCurrent () {
		return self::$current;
	}

	public static function get () {
		plugin('content_get');
		return filter('content_get', UTF8::htmlentities(self::$content['content']));
	}

	public static function getTitle () {
		plugin('content_gettitle');
		return filter('content_gettitle', UTF8::htmlentities(self::$content['title']));
	}

	public static function getMenutitle () {
		plugin('content_getmenutitle');
		return filter('content_getmenutitle', UTF8::htmlentities(self::$content['menutitle']));
	}

	public static function getSlug () {
		plugin('content_getslug');
		return filter('content_getslug', self::$content['slug']);
	}

	public static function getTheme () {
		plugin('content_gettheme');
		return filter('content_gettheme', self::$content['theme']);
	}

	public static function getBreadcrumbs () {
		plugin('content_getbreadcrumbs');
		return self::$breadcrumbs[self::$currentId];
	}

	public static function setContent ($x) {
		plugin('content_setcontent', array($x));
		$x = filter('content_setcontent', $x);
		self::$content['content'] = $x;
	}

	public static function setTitle ($x) {
		plugin('content_settitle', array($x));
		$x = filter('content_settitle', $x);
		self::$content['title'] = $x;
	}

	public static function setMenutitle ($x) {
		plugin('content_setmenutitle', array($x));
		$x = filter('content_setmenutitle', $x);
		self::$content['menutitle'] = $x;
	}

	public static function setTheme ($x) {
		plugin('content_setthemet', array($x));
		$x = filter('content_settheme', $x);
		Themes::setCurrentTheme($x);
		self::$content['theme'] = $x;
	}

	public static function setSettings ($x) {
		plugin('content_setsettings', array($x));
		$x = filter('content_setsettings', $x);
		self::$content['settings'] = $x;
	}

	public static function setSlug ($x) {
		plugin('content_setslug', array($x));
		$x = filter('content_setslug', $x);
		self::$content['slug'] = $x;
	}
}

class Page {
	private $settings = array();
	private $id;
	private $info = array();
	private $db_row;

	public function __construct ($id) {
		$this->id = $id;
	}

	public function getId () {
		return $this->id;
	}
	
	public function getContent () {
		$this->checkRow();
		return $this->info['content'];
	}

	public function getMenutitle () {
		$this->checkRow();
		return $this->info['menutitle'];
	}

	public function getTitle () {
		$this->checkRow();
		return $this->info['title'];
	}

	public function getSettings () {
		$this->checkRow();
		return $this->info['settings'];
	}

	public function getCreated () {
		$this->checkRow();
		return $this->info['created'];
	}

	public function getLastModified () {
		$this->checkRow();
		return $this->info['last_modified'];
	}

	public function getWeight () {
		$this->checkRow();
		return $this->info['weight'];
	}

	public function getParentId () {
		$this->checkRow();
		return $this->info['parent_id'];
	}

	public function getSlug () {
		$this->checkRow();
		return $this->info['slug'];
	}

	public function getType () {
		$this->checkRow();
		return $this->info['type'];
	}

	public function url () {
		if(Settings::get('core', 'clean urls', true) !== true) {
			$r .= "?q=";
		}
		$r .= Content::url($this->id);
		
		return CC_PUB_ROOT.$r;
	}

	public function link () {
		return sprintf('<a href="%s" title="%2$s">%2$s</a>', $this->url(), $this->info['menutitle']);
	}

	public function getTheme () {
		if(!array_key_exists('theme', $this->info)) {
			$settings = (array) $this->getSettings();

			plugin('page_gettheme', array($settings));
			$settings = filter('page_gettheme', $settings);

			if(array_key_exists('theme', $settings)) {
				$theme = new Theme($settings['theme']);
				if($theme->validate()) {
					$this->info['theme'] = $theme->getName();
				}
			}
			else {
				$this->info['theme'] = Settings::get('core', 'theme', true);
			}
		}
		return $this->info['theme'];
	}
	
	private function checkRow () {
		if(empty($this->db_row)) {
			$smt = DB::select('content', '*', array('id = ?', $this->getId()));
			$res = $smt->fetchAll(PDO::FETCH_ASSOC);

			foreach($res as $row) {
				$row = filter('page_checkrow', $row);
				$this->db_row = $row;
				
				$this->info['content'] = $row['content'];
				$this->info['name'] = $row['name'];
				$this->info['settings'] = unserialize($row['settings']);
				$this->info['title'] = $row['title'];
				$this->info['created'] = $row['created'];
				$this->info['last_modified'] = $row['last_modified'];
				$this->info['weight'] = $row['weight'];
				$this->info['menutitle'] = $row['menutitle'];
				$this->info['parent_id'] = $row['parent_id'];
				$this->info['type'] = $row['type'];
				$this->info['slug'] = $row['slug'];
			}
		}
	}
}

// admin has its own way of doing things
if(!IS_ADMIN)
	Content::bootstrap();

