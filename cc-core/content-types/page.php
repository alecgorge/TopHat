<?php
/**
 * The default implementation of NodeType
 */
class PageNode extends NodeType implements NodeActions {
	public function __construct($row) {
		$this->checkRow($row);
    }

	public static function name () {
		return __('admin', 'content-type-page');
	}

  	public static function cc_setup ($row) {
		$class = new PageNode($row);
		return $class;
   	}

	public static function buildParentOptions () {
		return array(0 => '-- None --') + Content::optionListArrayFromArray(Content::parseNavigation());
	}

	public static function create($args) {
		/**
		 * @todo Fix bug if menutitles are different but slugs are the same. (Blarg!!!! and Blarg!!!).
		 */
		$run = DB::select('content', 'COUNT(menutitle)', array('`menutitle` = ? AND `parent_id` = ?', $args['menutitle'], $args['parent_id']));
		$count = $run->fetchColumn();
		if($count > 0) {
			return false;
		}
		$smt = DB::insert('content', $args + array(
			'type' => 'page'
		));

		return $smt;
	}

	public static function edit($id, $args) {
		return DB::update('content', array('last_modified' => time()) + $args, null, array('content_id = ?', $id));
	}

	public static function delete($id) {
		return DB::delete('content', array('content_id = ?', $id));
	}

	public static $row = array();
	public static function get ($x) {
	    if(array_key_exists($x, $_POST)) return $_POST[$x];
		
		if($x == 'content_area') $x = 'content';
	    if(array_key_exists($x, self::$row)) return self::$row[$x];
	    return '';
	}

	public static function edit_display($row) {
		//$row['data'] = unserialize($row['data']);
		self::$row = $row;
	    i18n::set('admin');

	    if($_POST['cc_form'] == 'edit_page') {
			plugin('admin_edit_post_pre_processing');

			$id				= $_GET['id'];
			$title			= filter('admin_edit_post_title', self::get('title'));
			$content		= filter('admin_edit_post_content', self::get('content_area'));
			$last_modified	= filter('admin_edit_post_last_modified', time());
			$settings		= filter('admin_edit_post_settings', self::get('settings'));
			$weight			= filter('admin_edit_post_weight', self::get('weight'));
			$menutitle		= filter('admin_edit_post_menutitle', self::get('menutitle'));
			$parent_id		= filter('admin_edit_post_parent_id', self::get('parent_id'));
			$type			= filter('admin_edit_post_type', self::get('content_type'));
			$slug			= filter('admin_edit_post_slug', self::get('slug'));

			$values = array(
				'title' => $title,
				'content' => $content,
				'settings' => unserialize($settings),
				'weight' => $weight,
				'menutitle' => $menutitle,
				'parent_id' => $parent_id,
				'slug' => $slug

			);

			plugin('admin_edit_post_post_processing');
			$values = filter('admin_edit_post_posted_values', $values);

			$values['settings'] = serialize($values['settings']);

			$res = Content::editNode($id, $type, $values);

			if($res) {
				$message = Message::success('Page updated successfully!');
			}
			else {
				$message = Message::success('Page update failed (DB Error)!');
			}
			//Hooks::bind('post_edit_page', 'EditPage::handlePost');
	    }

	    $r .= sprintf("<h2>%s</h2>%s", __('edit-page'), $message);

		//var_dump(self::$row);
		$themeList = Themes::getThemeList();
		foreach($themeList as $k=>$v) {
			$tl[$k] = $v['name'];
		}
		$tl['-1'] = 'Default Theme';
		ksort($tl);


		$form = new Form('self', 'post', 'edit_page');

		$form->addHidden('settings', serialize(self::get('settings')));

		$form->startFieldset(__('page-info'));
			$form->addInput(__('page-title'), 'text', 'title', self::get('title'), array('class' => 'large'));
			$form->addHidden('content_type', self::get('type'));
			$form->addSelectList(__('theme-override'), 'theme', $tl, true, self::get('theme'));
			$form->addSelectList(__('parent'), 'parent_id', self::buildParentOptions(), true, self::get('parent_id'));
			$form->addInput(__('weight'), 'text', 'weight', self::get('weight'));
		$form->endFieldset();

		plugin('admin_edit_custom_fields', array(&$form));

		$form->startFieldset(__('menu-settings'));
			$form->addInput(__('menu-title'), 'text', 'menutitle', self::get('menutitle'));
			$form->addInput(__('slug'), 'text', 'slug', self::get('slug'));
		$form->endFieldset();

		plugin('admin_edit_custom_fields2', array(&$form));

		$form->startFieldset(__('content'));
			$form->addEditor('', 'content_area', self::get('content_area'));
		$form->endFieldset();

		plugin('admin_edit_custom_fields3', array(&$form));

		$form->startFieldset(__('save'));
			$form->addSubmit('', 'save', __('save'));
		$form->endFieldset();

		i18n::restore();

		return $r.$form->endAndGetHTML();
	}

	public static function create_display() {
	    i18n::set('admin');

	    if($_POST['cc_form'] == 'create_page') {
			plugin('admin_create_post_pre_proccessing');

			$id				= $_GET['id'];
			$title			= filter('admin_create_post_title', self::get('title'));
			$content		= filter('admin_create_post_content', self::get('content_area'));
			$last_modified	= filter('admin_create_post_last_modified', time());
			$settings		= filter('admin_create_post_settings', self::get('settings'));
			$weight			= filter('admin_create_post_weight', self::get('weight'));
			$menutitle		= filter('admin_create_post_menutitle', self::get('menutitle'));
			$parent_id		= filter('admin_create_post_parent_id', self::get('parent_id'));
			$slug			= filter('admin_create_post_slug', self::get('slug'));

			if(empty($menutitle) || empty($slug)) {
				$message = Message::error(__('blank-error'));
				plugin('admin_create_post_blank_error');
			}
			else {
				plugin('admin_create_post_post_proccessing');
				$values = array(
					'title' => $title,
					'content' => $content,
					'settings' => unserialize($settings),
					'weight' => $weight,
					'menutitle' => $menutitle,
					'parent_id' => $parent_id,
					'slug' => $slug

				);
				$values = filter('admin_create_post_posted_values', $values);
				$values['settings'] = serialize($values['settings']);
				
				$res = Content::createNode($_GET['type'], $values);

				if($res) {
					$message = Message::success(sprintf(__('page-creation-successful').' (<a href="%s">%s</a>)', Admin::link('content'), __('view-all-pages')));
				}
				else {
					$message = Message::error(__('page-creation-failed'));
				}
			}
			//Hooks::bind('post_edit_page', 'EditPage::handlePost');
	    }

	    $r .= sprintf("<h2>%s</h2>%s", __('add-page'), $message);

		$themeList = Themes::getThemeList();
		$themeList['-1'] = 'Default Theme';
		ksort($themeList);


		//echo "<pre>"; var_dump(self::buildParentOptions(1)); echo "</pre>";

		$form = new Form('self', 'post', 'create_page');

		$form->addHidden('settings', 'a:0:{}');

		$form->startFieldset(__('page-info'));
			$form->addInput(__('page-title'), 'text', 'title', self::get('title'), array('class' => 'large'));
			$form->addHidden('content_type', self::get('type'));
			$form->addSelectList(__('theme-override'), 'theme', $themeList);
			$form->addSelectList(__('parent'), 'parent_id', self::buildParentOptions(),true,  $_POST['parent_id'] ? $_POST['parent_id'] : '0');
			$form->addInput(__('weight'), 'text', 'weight',  $_POST['weight'] ? $_POST['weight'] : '0');
		$form->endFieldset();

		plugin('admin_create_custom_fields', array(&$form));

		$form->startFieldset(__('menu-settings'));
			$form->addInput(__('menu-title'), 'text', 'menutitle', self::get('menutitle'));
			$form->addInput(__('slug'), 'text', 'slug', self::get('slug'));
		$form->endFieldset();

		plugin('admin_create_custom_fields2', array(&$form));

		$form->startFieldset(__('content'));
			$form->addEditor('', 'content_area', self::get('content_area'));
		$form->endFieldset();

		plugin('admin_create_custom_fields3', array(&$form));

		$form->startFieldset(__('save'));
			$form->addSubmit(__('save'), 'save');
		$form->endFieldset();

		i18n::restore();

		return $r.$form->endAndGetHTML();

	}
}
Node::register('page', 'PageNode');

