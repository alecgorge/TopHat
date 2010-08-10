<?php

Admin::registerSubpage('content', 'edit-page', 'Edit Page', 'EditPage::display');
AdminSidebar::registerForPage('content/edit-page', 'EditPage::fileUploadBlock');
AdminSidebar::registerForPage('content/edit-page', 'EditPage::pageInfoBlock', -1);

class EditPage {
	public static $invalid = false;
	public static $row = array();

	public static function display () {
		$id = $_GET['id'];

		if(!is_numeric($id)) {
			$r = self::invalidIdError();
		    i18n::restore();
			cc_redirect(Admin::link('content'));

			return $r;
		}

		$pageInfo = Database::select('content', '*', array('id = ?', $id));

		$row = $pageInfo->fetch(PDO::FETCH_ASSOC);
		self::$row = $row;
		return Content::nodeDisplay('edit_display', $row['type'], $row);
   	}

	public static function invalidIdError() {
		self::$invalid = true;
		return Message::error(__('admin', "edit-page-invalid-id"));
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
		, __('admin', 'page-summary'), __('admin', 'date-last-modified'), date('D, M y h:m:sa', self::$row['last_modified']));
	}
}

?>
