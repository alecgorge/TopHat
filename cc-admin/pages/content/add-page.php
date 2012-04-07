<?php

Admin::registerSubpage('content', 'create-page', __('admin', 'add-page'), 'CreatePage::display', -10);
AdminSidebar::registerForPage('content/create-page', 'EditPage::viewAll', -10);

class CreatePage {
	public static $invalid = false;
	public static $row = array();

	public static function display () {
		$type = $_GET['type'];
		$types = Content::contentTypes();

		if(array_key_exists($type, $types) === false && array_key_exists($_POST['type'], $types) === false) {
			$opt_list = array();
			foreach($types as $single_type => $class) {
				$opt_list[$single_type] = call_user_func($class.'::name');
			}

			$form = new Form('self', 'post', 'add_node_1');
			$form->addSelectList(__('admin', 'content-type'), 'type', $opt_list, true, 'page');
			$form->addSubmit('', 'continue', __('admin', 'continue'));

			return array(__('admin', 'add-page'), $form->endAndGetHTML());
		}
		if(!$type && $_POST['type']) {
			if(array_key_exists('continue', $_POST)) {
				cc_redirect(Admin::link($_GET['page'], array('type' => $_POST['type'])));
			}
			else {
				return;
			}
		}

		AdminSidebar::registerForPage('content/create-page', 'EditPage::fileUploadBlock');

		return Content::nodeDisplay('create_display', $type, array());
   	}
}
