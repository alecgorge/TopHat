<?php

Admin::registerSubpage('content', 'edit-page', 'Edit Page', 'EditPage::display');
AdminSidebar::registerForPage('content/edit-page', 'EditPage::fileUploadBlock');
AdminSidebar::registerForPage('content/edit-page', 'EditPage::pageInfoBlock', -1);

class EditPage {
	public static $invalid = false;
	public static $row = array();

	public static function handlePost () {
	    var_dump($_POST);
	}

	public static function get ($x) {
	    if($_POST[$x]) return $_POST[$x];
	    if(array_key_exists($x, self::$row)) return self::$row[$x];
	    return '';
	}

	public static function display () {
	    i18n::set('admin');

	    if($_POST['cc_form'] == 'edit_page') {
			plugin('admin_edit_post_pre');

			$id				= $_GET['id'];
			$title			= filter('admin_edit_post_title', self::get('title'));
			$content		= filter('admin_edit_post_content', self::get('content'));
			$last_modified	= filter('admin_edit_post_last_modified', time());
			$settings		= filter('admin_edit_post_settings', self::get('settings'));
			$weight			= filter('admin_edit_post_weight', self::get('weight'));
			$menutitle		= filter('admin_edit_post_menutitle', self::get('menutitle'));
			$parent_id		= filter('admin_edit_post_parent_id', self::get('parent_id'));
			$type			= filter('admin_edit_post_type', self::get('content_type'));
			$slug			= filter('admin_edit_post_slug', self::get('slug'));
			
			$res = Content::editNode($id, $type, array(
				'title' => $title,
				'content' => $content,
				'settings' => $settings,
				'weight' => $weight,
				'menutitle' => $menutitle,
				'parent_id' => $parent_id,
				'slug' => $slug

			));
			
			if($res) {
				$message = Message::success('Page updated successfully!');
			}
			else {
				$message = Message::success('Page update failed (DB Error)!');
			}
			//Hooks::bind('post_edit_page', 'EditPage::handlePost');
	    }

	    $r .= sprintf("<h2>%s</h2>%s", __('edit-page'), $message);

		$id = $_GET['id'];

		if(!is_numeric($id)) {
			self::invalidIdError();
		    i18n::restore();
			cc_redirect(Admin::link('content'));

			return;
		}

		$pageInfo = Database::select('content', '*', array('id = ?', $id));

		$row = $pageInfo->fetch(PDO::FETCH_ASSOC);
		self::$row = $row;

		$themeList = Themes::getThemeList();
		$themeList['-1'] = 'Default Theme';
		ksort($themeList);


		$form = new Form('self', 'post', 'edit_page');

		$form->addHidden('settings', self::get('settings'));

		$form->startFieldset(__('page-info'));
			$form->addInput(__('page-title'), 'text', 'title', self::get('title'), array('class' => 'large'));
			$form->addHidden('content_type', self::get('type'));
			$form->addSelectList(__('theme-override'), 'theme', $themeList);
			$form->addInput(__('weight'), 'text', 'weight', self::get('weight'));
		$form->endFieldset();

		$form->startFieldset(__('menu-settings'));
			$form->addInput(__('menu-title'), 'text', 'menutitle', self::get('menutitle'));
			$form->addInput(__('slug'), 'text', 'slug', self::get('slug'));
		$form->endFieldset();

		plugin('admin_edit_custom_fields', array(&$form));

		$form->startFieldset(__('content'));
			$form->addEditor('', 'content', self::get('content'));
		$form->endFieldset();

		plugin('admin_edit_custom_fields2', array(&$form));

		$form->startFieldset(__('save'));
			$form->addSubmit('Save Changes', 'save');
		$form->endFieldset();

		i18n::restore();

		return $r.$form->endAndGetHTML();
   	}

	public static function invalidIdError() {
		self::$invalid = true;
		Message::error(__('admin', "edit-page-invalid-id"));
	}

	public static function fileUploadBlock () {
		if(self::$invalid) return;

		i18n::set('admin');

		$r .= sprintf("<h3>%s</h3>", __('upload-files'));

		return $r;

		i18n::restore();
	}

	public static function pageInfoBlock () {
		if(self::$invalid) return;

		return sprintf(<<<EOT
	<h3>%s</h3>
	<p><strong>%s:</strong> %s</p>
EOT
		, __('admin', 'page-summary'), __('admin', 'date-last-modified'), date('D, M y h:m:s', self::$row['last_modified']));
	}
}

?>
