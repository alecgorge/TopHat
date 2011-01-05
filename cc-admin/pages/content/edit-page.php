<?php

Admin::registerSubpage('content', 'edit-page', __('admin', 'edit-page'), 'EditPage::display');
AdminSidebar::registerForPage('content/edit-page', 'EditPage::viewAll', -10);
AdminSidebar::registerForPage('content/edit-page', 'EditPage::fileUploadBlock');
AdminSidebar::registerForPage('content/edit-page', 'EditPage::viewPage', -10);
AdminSidebar::registerForPage('content/edit-page', 'EditPage::pageInfoBlock', -1);

class EditPage {
	public static function viewAll () {
		return sprintf("<a href='%s' class='action'>%s%s</a>", Admin::link('content'), icon('pages_all'), __('admin', 'view-all-pages'));
	}

	public static function viewPage () {
		return sprintf("<a href='%s' target='_blank' class='action'>%s%s</a>", Node::fetchUrlForId($_GET['id']), icon('page_magnify'), __('admin', 'view-page-on-site'));
	}

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

		$pageInfo = Database::select('content', '*', array('content_id = ?', $id));

		$row = $pageInfo->fetch(PDO::FETCH_ASSOC);

		if(!$row) {
			$r = self::invalidIdError();
		    i18n::restore();
			cc_redirect(Admin::link('content'));

			return $r;
		}

		$row['settings'] = unserialize($row['settings']);

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

		$uploader = new Uploader('editFiles', 'EditPage::handleUpload', false, array(
			'multi' => true
		));

		load_library('jquery-tools');
		queue_js_string("$(function(){\$('.view-uploads').overlay({
	mask: {
		color: '#000',
		loadSpeed: 200,
		opacity: 0.7
	},

	closeOnClick: true
});});");

		$uploadedButton =  sprintf("<a href='#' class='action view-uploads' rel='#cc_uploaded_overlay'>%s%s</a>", icon('folder_picture'), __('admin', 'view-all-files'));

		$uploadedFiles = <<<EOT
<div class="cc_uploaded_files">
$uploadedButton
	<div id="cc_uploaded_overlay" class="cc_modal">
		<h2>%s</h2>
		<ul class="cc_file_list">
			%s
		</ul>
	</div>
</div>
EOT;
		//foreach(Uploads::getAllFiles() as $)

		$r .= sprintf("<h3>%s</h3>%s%s", __('upload-files'), sprintf($uploadedFiles, __('uploaded-files'), $files), $uploader->createHTML());

		return $r;

		i18n::restore();
	}

	public static function handleUpload ($file) {
		var_dump($file);
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
