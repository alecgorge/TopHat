<?php

/**
 * You can access info about the current page with the static methods in this class.
 *
 * @todo Document private methods.
 * @todo change page references to node
 */
class Content {
	public static $current;
	public static $currentId;
	private static $parentPath;

	public static $navArray;
	public static $navArrayComplete;
	public static $idLookup;
	public static $idLookupClean;
	public static $childLookup;
	public static $count;
	public static $urlLookup;
	public static $idTypeLookup;
	public static $breadcrumbs;

	public static $content = array();
	public static $navCache;
	public static $navCheck;

	public static function bootstrap () {
		self::parseNavigation();
		self::parseUrl();

		// cache content
		self::get();
	}
	
	public static function parseNavigation () {
		if(empty(self::$navArray)) {
			$refs = array();
			$refs5 = array();
			$data = array();
			$options = false;
			$list = array();
			$list5 = array();
			
			$pages = DB::select('content', '*', null, array('weight', 'asc', 'menutitle', 'asc'));

			$count = 0;

			while($data = $pages->fetch(PDO::FETCH_ASSOC)) {
				$data = filter('content_parsenavigation_data', $data);
				$options = unserialize($data['settings']);
				$thisref = &$refs[ $data['content_id'] ];
				$thisref5 = &$refs5[ $data['content_id'] ];

				$continue = true;
				plugin('content_parsenavigation_before', array($thisref, $data, $options, &$continue));

				$thisref['id'] = (int) $data['content_id'];
				$thisref['menutitle'] = $data['menutitle'];
				$thisref['slug'] = $data['slug'];

				$thisref5['id'] = (int) $data['content_id'];
				$thisref5['menutitle'] = $data['menutitle'];
				$thisref5['slug'] = $data['slug'];

				if($continue) {
					$count++;
					if(!empty($options['external'])) {
						$external[$thisref['id']] = $options['external'];
					}
					if ($data['parent_id'] == 0) {
						$list[$thisref['id']] = &$thisref;
					} else {
						$refs[ $data['parent_id'] ]['children'][ $thisref['id'] ] = &$thisref;
					}
				}

				if ($data['parent_id'] == 0) {
					$list5[$thisref['id']] = &$thisref5;
				} else {
					$refs5[ $data['parent_id'] ]['children'][ $thisref5['id'] ] = &$thisref5;
				}


				plugin('content_parsenavigation_after', array($thisref, $data, $options));

				//$list5[$thisref['id']] = &$thisref;
				$list2[$thisref['id']] = $thisref['menutitle'];
				$idTypeList[$data['content_id']] = $data['type'];
				$list3[$thisref['id']] = $thisref['slug'];
				$list4[$thisref['id']] = (int) $data['parent_id'];
			}

			plugin('content_parsenavigation_afterloop', array($list, $list5, $list2, $list3, $list4));
			$list = filter('content_parsenavigation_nav', $list);
			$list2 = filter('content_parsenavigation_idlookup', $list2);
			$list3 = filter('content_parsenavigation_idlookupclean', $list3);
			$list4 = filter('content_parsenavigation_childlookup', $list4);
			$list5 = filter('content_parsenavigation_childlookup', $list5);
			$count = filter('content_parsenavigation_count', $count);


			self::$navArray = $list;
			self::$navArrayComplete = $list5;
			self::$idLookup  = $list2;
			self::$count = $count;
			self::$idLookupClean = $list3;
			self::$childLookup = $list4;
			self::$idTypeLookup = $idTypeList;
			
			self::$urlLookup = self::generateIdLookups();
			self::$breadcrumbs = self::generateBreadcrumbs();

			plugin('content_parsenavigation_aftergeneration');


			return self::$navArray;
		}
		else {
			return self::$navArray;
		}		
	}

	/**
	 * Adds a em-dash before each indented item
	 */
	public static function optionListArrayFromArray($arr, $selected = "", $indent=0, $mother_run=true, $nameInValue = 0){
		foreach($arr as $k=>$v){
			// skip the baseval thingy. Not a real node.
			if($k == "__base_val") continue;

			if($k == "id" || $k == 'menutitle' || $k == 'slug') continue;
			// determine the real value of this node.
			$show_val = ( is_array($v) ? $v["__base_val"] : $v );

			if($k == "children") {
				$return = (array)$return + (array)self::optionListArrayFromArray($v, $selected, ($indent), false, $nameInValue);
			}
			else {
				$return[$v['id']] = str_repeat("&#8212; ", $indent).$v['menutitle'];
				if(is_array($v)){
					// this is what makes it recursive, rerun for childs
					$return = (array)$return + (array)self::optionListArrayFromArray($v, $selected, ($indent+1), false, $nameInValue);
				}
			}
		}
		return $return;
	}

	public static function getType ($id) {
		if(empty(self::$idTypeLookup)) self::parseNavigation();
		return self::$idTypeLookup[$id];
	}

	public static function countNavItems () {
	    self::parseNavigation();
	    return self::$count;
	}

	private static function generateIdLookups ($path = null, $children = array(), $isRoot = true) {
		if($path === null) {
			$children = self::$navArrayComplete;
		}

		foreach($children as $key => $value) {
			if(array_key_exists('children', $value)) {
				$r[$value['id']] = $path.'/'.$value['slug'];
				$r += self::generateIdLookups((string)$path.'/'.$value['slug'], $value['children'], false);
			}
			else {
				$r[$value['id']] = $path.'/'.$value['slug'];
			}
		}

		return $r;
	}

	private static function generateBreadcrumbString ($path = null, $children = array()) {
		if($path === null) {
			$children = self::$navArrayComplete;
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

	/**
	 * Get the id of the current node.
	 *
	 * @return int The current node id.
	 */
	public static function currentId () {
		return self::$currentId;
	}

	/**
	 * This function returns HTML from the site's navigation.
	 *
	 * @param array $options The options for the nav generation. It is an associative array. Any items not named will use the default: <code>array('root' =&gt; &quot;\n&lt;ul id='ul-navigation'&gt;\n%s\n&lt;/ul&gt;\n&quot;,
	 *	'child' =&gt; &quot;\n&lt;ul class='ul-sub-navigation'&gt;\n%s\n&lt;/ul&gt;\n&quot;,
	 *	'item' =&gt; &quot;\n\t&lt;li&gt;&lt;a href='%2\$s' title='%1\$s'&gt;%1\$s&lt;/a&gt;&lt;/li&gt;&quot;,
	 *	'itemSelected' =&gt; &quot;\n\t&lt;li class='selected'&gt;&lt;a href='%2\$s' title='%1\$s'&gt;%1\$s&lt;/a&gt;&lt;/li&gt;&quot;,
	 *	'itemHasChild' =&gt; &quot;\n\t&lt;li&gt;&lt;a href='%2\$s' title='%1\$s'&gt;%1\$s&lt;/a&gt;%3\$s&lt;/li&gt;&quot;,
	 *	'itemHasChildSelected' =&gt; &quot;\n\t&lt;li class='selected'&gt;&lt;a href='%2\$s' title='%1\$s'&gt;%1\$s&lt;/a&gt;%3\$s&lt;/li&gt;&quot;);</code>
	 * @param bool $isChild Ignore. For internal use only.
	 * @param bool $inputArray Ignore. For internal use only.
	 * @return string The nav.
	 */
	public static function generateNavHTML($options, $isChild = false, $inputArray = array()) {
		if(!empty(self::$navCache) && self::$navCheck == serialize($options)) {
			return self::$navCache;
		}

		$defaults = array(
						'root' => "\n<ul class='ul-navigation'>\n%s\n</ul>\n",
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

				$url = Node::fetchUrlForId($item['id'], $item['menutitle'], $item['slug']);

				// get the children's html
				$childrenHtml = self::generateNavHTML($options, true, $item['children']);

				$childIds = array_subkeys($item['children'], 'id', 'children');

				// current page
				if(Content::currentId() == $item['id'] || in_array(Content::currentId(), $childIds)) {
					// add the html on
					$thisOutput = sprintf($options['itemHasChildSelected'], $mt, $url, $childrenHtml, $item['id']);
				}
				else {
					// add the html on
					$thisOutput = sprintf($options['itemHasChild'], $mt, $url, $childrenHtml, $item['id']);
				}

				plugin('nav_haschild_after', array($item, $thisOutput, $output, $item['id']));
				$thisOutput = filter('nav_haschild_after', $thisOutput);

				$output .= $thisOutput;
			}
			// no subpages
			else {
				$mt = UTF8::htmlentities($item['menutitle']);
				plugin('nav_nochild_before', array($item));

				$url = Node::fetchUrlForId($item['id'], $item['menutitle'], $item['slug']);

				// current page
				if(Content::currentId() == $item['id']) {
					// add the html on
					$thisOutput = sprintf($options['itemSelected'], $mt, $url, $item['id']);
				}
				else {
					// add the html on
					$thisOutput = sprintf($options['item'], $mt, $url, $item['id']);
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

	public static function createNode ($type, $args) { //$title, $menutitle, $content, $settings = array(), $weight = 0, $parent = 0) {
		$args = array_merge(array(
			'settings' => array(),
			'weight' => 0,
			'parent_id' => 0,
			'created' => time(),
			'last_modified' => time(),
		), $args);
		return Node::action('create', $type, array($args));
	}

	public static function editNode ($id, $type, $args) { //$title, $menutitle, $content, $settings = array(), $weight = 0, $parent = 0) {
		$args = array_merge(array(
			'last_modified' => time(),
		), $args);
		return Node::action('edit', $type, array($id, $args));
	}

	/*public static function createNode ($type, $args) { //$title, $menutitle, $content, $settings = array(), $weight = 0, $parent = 0) {
		$args = array(
			'settings' => array(),
			'weight' => 0,
			'parent_id' => 0,
			'created' => time(),
			'last_modified' => time(),
		) + $args;
		return Node::action('create', $type, $args);
	}*/

	public static function parseUrl ($arg = false) {
		if($arg === false) {
			$_GET['q'] = filter('content_parseurl', $_GET['q']);
			URLMap::from("*", "Content::parseURL", 1000);
		}
		else {
			$parts = array_remove_empty(explode('/', $_GET['q']));

			if(count($parts) == 1) {
				if(self::isNode('/'.$parts[0])) {
					self::setCurrent(self::nameToId('/'.$parts[0]));
				}
				else {
					self::trigger404();
				}
			}
			elseif(count($parts) > 1) {
				$page = '/'.implode('/', $parts);
				if(self::isNode($page)) {
					self::setCurrent(self::nameToId($page));
				}
				else {
					self::trigger404();
				}
			}
			else {
				self::setCurrent(Settings::get('site', 'homepage id', true));
			}
		}
	}

	/**
	 * Given a slug path like menu-test-1/menu-test-2/menu-title-1, the node id is returned.
	 *
	 * @param string $name The slug path.
	 * @return int The node id.
	 */
	public static function nameToId($name) {
		$id = array_search($name, self::$urlLookup);
		return $id;
	}

	/**
	 * Given an id like 3 it will return menu-test-1/menu-test-2/menu-title-1 .
	 *
	 * @param int $id The id of the node.
	 * @return string The url like menu-test-1/menu-test-2/menu-title-1 to the node with the id $id.
	 */
	public static function url ($id) {
		// var_dump(self::$urlLookup);
		return filter('content-url', ltrim(self::$urlLookup[$id], '/'));
	}

	/**
	 * This is called when the node is not found.
	 */
	public static function trigger404 () {
		header('HTTP/1.0 404 Not Found');

		plugin('content_404');

		$e404t = filter('content_404title', '404 Error: Page Not Found');
		$e404mt = filter('content_404menutitle', 'Page Not Found');
		$e404c = filter('content_404content', 'Page not found. Return to the <a href="'.TH_PUB_ROOT.'">homepage</a>.');

		self::setTitle($e404t);
		self::setContent($e404c);
		self::setTheme(Settings::get('site', 'theme', true));
		self::$currentId = -1;
		self::$breadcrumbs[-1] = array($e404mt);
	}

	/**
	 * Tests if a node exists.
	 *
	 * @param string $page A url slug like menu-test-1/menu-test-2/menu-title-1 .
	 * @return boolean True if the node exists, false otherwise.
	 */
	public static function isNode ($page) {
		$page = filter('content_isnode', $page);
		plugin('content_isnode', array($page));
		
		return (array_search($page, self::$urlLookup) === false ? false : true);
	}

	public static function setCurrent ($id) {
		self::$currentId = $id;
		self::$current = Node::fetchHandler($id);


		self::setContent(self::$current->getContent());
		self::setTitle(self::$current->getTitle());
		self::setMenutitle(self::$current->getMenutitle());
		self::setTheme(self::$current->getTheme());
		self::setSettings(self::$current->getSettings());
		self::setSlug(self::$current->getSlug());
	}

	/**
	 * Gets the raw instance of a decendant of NodeType.
	 *
	 * @return Object An instance of a decendant of NodeType
	 */
	public static function getCurrent () {
		return self::$current;
	}


	private static $initContent = false;
	/**
	 * Gets the content of the current node.
	 *
	 * @return string The requested item.
	 */
	public static function get () {
		if(!self::$initContent) {
			plugin('content_get_inital');
			self::$content['content'] = filter('content_get_inital', self::$content['content']);
			self::$initContent = true;
		}
		plugin('content_get');
		return filter('content_get', UTF8::htmlentities(self::$content['content']));
	}

	/**
	 * Gets the title of the current node.
	 *
	 * @return string The requested item.
	 */
	public static function getTitle () {
		plugin('content_gettitle');
		return filter('content_gettitle', UTF8::htmlentities(self::$content['title']));
	}

	/**
	 * Gets the menutitle of the current node.
	 *
	 * @return string The requested item.
	 */
	public static function getMenutitle () {
		plugin('content_getmenutitle');
		return filter('content_getmenutitle', UTF8::htmlentities(self::$content['menutitle']));
	}

	/**
	 * Gets the slug of the current node.
	 *
	 * @return string The requested item.
	 */
	public static function getSlug () {
		plugin('content_getslug');
		return filter('content_getslug', self::$content['slug']);
	}

	/**
	 * Gets the selected theme of the current node.
	 *
	 * @return string The requested item.
	 */
	public static function getTheme () {
		plugin('content_gettheme');
		return filter('content_gettheme', self::$content['theme']);
	}

	/**
	 * Gets the breadcrumbs of the current node.
	 *
	 * @return string The requested item.
	 */
	public static function getBreadcrumbs () {
		plugin('content_getbreadcrumbs');
		return self::$breadcrumbs[self::$currentId];
	}

	/**
	 * Overrides the content of the current node.
	 */
	public static function setContent ($x) {
		plugin('content_setcontent', array($x));
		$x = filter('content_setcontent', $x);
		self::$content['content'] = $x;
	}

	/**
	 * Overrides the title of the current node.
	 */
	public static function setTitle ($x) {
		plugin('content_settitle', array($x));
		$x = filter('content_settitle', $x);
		self::$content['title'] = $x;
	}

	/**
	 * Overrides the menutitle of the current node.
	 */
	public static function setMenutitle ($x) {
		plugin('content_setmenutitle', array($x));
		$x = filter('content_setmenutitle', $x);
		self::$content['menutitle'] = $x;
	}

	/**
	 * Overrides the theme of the current node.
	 */
	public static function setTheme ($x) {
		plugin('content_setthemet', array($x));
		$x = filter('content_settheme', $x);
		Themes::setCurrentTheme($x);
		self::$content['theme'] = $x;
	}

	/**
	 * Overrides the settings array of the current node.
	 */
	public static function setSettings ($x) {
		plugin('content_setsettings', array($x));
		$x = filter('content_setsettings', $x);
		self::$content['settings'] = $x;
	}

	/**
	 * Overrides the slug of the current node.
	 */
	public static function setSlug ($x) {
		plugin('content_setslug', array($x));
		$x = filter('content_setslug', $x);
		self::$content['slug'] = $x;
	}

	public static function nodeDisplay($action, $type, $row) {
		return Node::action($action, $type, array($row));
	}

	public static function contentTypes () {
		return Node::$registration;
	}
}

class Node {
	public static $registration = array();

	public static function fetchHandler ($id) {
		$smt = Database::select('content', '*', array('content_id = ?', $id));
		$row = $smt->fetch(PDO::FETCH_ASSOC);
		$type = $row['type'];

		if(!array_key_exists($type, self::$registration)) {
			$type = 'page';
		}

		$class = self::$registration[$type];

		// bootstrap the content type
		$class = call_user_func($class.'::cc_setup', $row);

		return $class;
   	}

	public static function fetchUrlForId ($id, $menutitle = null, $slug = null) {
		if($menutitle === null) {
			$menutitle = Content::$idLookup[$id];
		}

		if($slug === null) {
			$slug = Content::$idLookupClean[$id];
		}

		$type = Content::getType($id);

		if(!array_key_exists($type, self::$registration)) {
			$type = 'page';
		}

		$class = self::$registration[$type];

		// bootstrap the content type
		return call_user_func_array($class.'::url', array($id, $menutitle, $slug));
	}

	public static function action ($method_name, $node_type, $args) {
		return call_user_func_array(self::$registration[$node_type].'::'.$method_name, $args);
	}

	/**
	 * Registers a custom content type. All overwrite a previous bind.
	 *
	 * @param string $type The node type to associate $callback with.
	 * @param string $callback Name of the NodeType class.
	 */
	public static function register ($type, $callback) {
		self::$registration[$type] = $callback;
	}
}
$files = glob(dirname(__FILE__).'/content-types/*.php');
foreach($files as $file) {
	require $file;
}

interface NodeActions {
	public static function create($args);
	public static function edit($id, $args);
	public static function delete($id);
	public static function edit_display($row);
	public static function create_display();
	public static function cc_setup($row);
}

abstract class NodeType {
	private $settings = array();
	private $id;
	private $info = array();
	private $db_row;

	public function getId () {
		return $this->id;
	}

	public function getContent () {
		$this->checkRow();
		return filter('node_content', $this->info['content']);
	}

	public function getMenutitle () {
		$this->checkRow();
		return filter('node_menutitle', $this->info['menutitle']);
	}

	public function getTitle () {
		$this->checkRow();
		return filter('node_title', $this->info['title']);
	}

	public function getSettings () {
		$this->checkRow();
		return filter('node_settings', $this->info['settings']);
	}

	public function getCreated () {
		$this->checkRow();
		return filter('node_created', $this->info['created']);
	}

	public function getLastModified () {
		$this->checkRow();
		return filter('node_last_modified', $this->info['last_modified']);
	}

	public function getWeight () {
		$this->checkRow();
		return filter('node_weight', $this->info['weight']);
	}

	public function getParentId () {
		$this->checkRow();
		return $this->info['parent_id'];
	}

	public function getSlug () {
		$this->checkRow();
		return filter('node_slug', $this->info['slug']);
	}

	public function getType () {
		$this->checkRow();
		return filter('node_type', $this->info['type']);
	}

	public static function url ($id, $menutitle, $slug) {
		if(Settings::get('core', 'clean urls', true) !== true) {
			$r .= "?q=";
		}
		$r .= Content::url($id);

		return filter('node_url', TH_PUB_ROOT.$r);
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
				$this->info['theme'] = Settings::get('site', 'theme', true);
			}
		}
		return filter('node_theme', $this->info['theme']);
	}

	protected function checkRow ($raw_row = null) {
		if(empty($this->db_row)) {
			$row = $raw_row;
			$this->id = $row['id'];

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

// admin has its own way of doing things
if(!CC_IS_ADMIN)
	Hooks::bind('system_after_content_load', 'Content::bootstrap');

