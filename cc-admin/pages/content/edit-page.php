<?php

//Admin::registerSubpage('content', 'edit-page', 'Edit Page', 'EditPage::display');
AdminSidebar::registerForPage('content/edit-page', 'EditPage::fileUploadBlock');
AdminSidebar::registerForPage('content/edit-page', 'EditPage::pageInfoBlock', -1);

class EditPage {
	public static $invalid = false;

	public static function display () {
		i18n::set('admin');

       	echo sprintf("<h2>%s</h2>", __('edit-page'));

		$id = $_GET['id'];

		if(!is_numeric($id)) {
			self::invalidIdError();
			i18n::restore();

			return;
		}

		$pageInfo = Database::select('content', '*', array('id = ?', $id));

		$row = $pageInfo->fetch(PDO::FETCH_ASSOC);

		var_dump($row);

      	$form = new Form('self', 'post', 'edit_page');

		$form->startFieldset(__('page-info'));
			$form->addInput(__('page-title'), 'text', 'page_title', '', array('class' => 'large'));
			$form->addSelectList(__('content-type'), 'content_type', array('asdf' => 'Page', 'asdf2' => 'Blog Post'), NULL, 'asdf2');
			$form->addSelectList(__('theme-override'), 'theme', array('-1' => 'Default Theme'));
       	$form->endFieldset();

		$form->startFieldset(__('menu-settings'));
			$form->addInput(__('menu-title'), 'text', 'menutitle');
			$form->addInput(__('slug'), 'text', 'slug');
		$form->endFieldset();

		plugin('admin_editpage_custom_fields', array(&$form));

		$form->startFieldset(__('content'));
			$form->addEditor('', 'edit-content');
		$form->endFieldset();

		plugin('admin_editpage_custom_fields2', array(&$form));

		$form->startFieldset(__('save'));
			$form->addSubmit('Save Changes', 'save');
		$form->endFieldset();

		i18n::restore();

		echo $form->endAndGetHTML();
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
	<p><strong>%s:</strong> %s</p>
	<p><strong>%s:</strong> %s</p>
EOT
		, __('admin', 'page-summary'), __('admin', 'created-by'), 'admin', __('admin', 'date-created'), 'xyz', __('admin', 'date-last-modified'), 'xyz');
	}
}

?>
