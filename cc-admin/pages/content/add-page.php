<?php

Admin::registerSubpage('content', 'create-page', __('admin', 'add-page'), 'CreatePage::display', -10);

class CreatePage {
	public static $invalid = false;
	public static $row = array();

	public static function display () {
		$type = (array_key_exists('type', $_GET) ? $_GET['type'] : $_POST['type']);
		$types = Content::contentTypes();

		if(array_key_exists($type, $types) === false) {
			$opt_list = array();
			foreach($types as $single_type => $class) {
				$opt_list[$single_type] = call_user_func($class.'::name');
			}

			$form = new Form('self', 'post', 'add_node_1');
			$form->addSelectList(__('admin', 'content-type'), 'type', $opt_list, true, 'page');
			$form->addSubmit('', 'continue', __('admin', 'continue'));

			return $form->endAndGetHTML();
		}
		else {
			if($_POST['continue'] == __('admin', 'continue')) {
				cc_redirect(Admin::link($_GET['page'], array('type' => $_POST['type'])));
			}
		}

		AdminSidebar::registerForPage('content/create-page', 'EditPage::fileUploadBlock');
		AdminSidebar::registerForPage('content/create-page', 'EditPage::pageInfoBlock', -1);

		return Content::nodeDisplay('create_display', $type, array());
   	}

	public static function invalidIdError() {
		self::$invalid = true;
		return Message::error(__('admin', "edit-page-invalid-id"));
	}
}

?>
