<?php

Admin::registerPage('content', 'Content Management', 'ContentPage::display', -10);
AdminSidebar::registerForPage('content', 'ContentPage::createContent');
//AdminSidebar::registerForPage('content', 'EditPage::pageInfoBlock', -1);

class ContentPage {
	public static $navArray;

	public static function display () {
		echo "<h2>".__("admin", "content-management")."</h2>";


   	}

	public static function getPageHierarchy($options, $isChild = false, $inputArray = array()) {
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

				$page = Node::fetchHandler($item['id']);
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

				$page = Node::fetchHandler($item['id']);
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

	public static function getPages () {
		$smt = Database::select('content', '*', array('type = ?', 'page'));

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
				plugin('admin_parsenavigation_before', array($thisref, $data, $options, &$continue));

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

				plugin('admin_parsenavigation_after', array($thisref, $data, $options));

				$list2[$thisref['id']] = $thisref['menutitle'];
				$list3[$thisref['id']] = $thisref['slug'];
				$list4[$thisref['id']] = (int) $data['parent_id'];
			}

			plugin('admin_parsenavigation_afterloop', array($list, $list2, $list3, $list4));
			$list = filter('admin_parsenavigation_nav', $list);

			self::$navArray = $list;

			plugin('content_parsenavigation_aftergeneration');

			return self::$navArray;
		}
		else {
			return self::$navArray;
		}

	}

	public static function createContent () {
		return sprintf("<a href='%s' class='action'>%s</a>", Admin::link('content/add-page'), __('admin', 'add-page'));
	}
}

?>
