<?php

Admin::registerSubpage('users', 'add-group', __('admin', 'add-group'), 'AddGroupPage::display');

class AddGroupPage {
	public static function display () {
		if($_POST['cc_form'] === 'add-group') {
			$group = $_POST['group'];

			$rows = Database::select('users', 'name', array('name = ? AND type = ?', $group, 'group'), null, 1)->fetch(PDO::FETCH_ASSOC);
			if(!empty($rows)) {
				$messages .= Message::error(__('admin', 'group-in-use'));
			}
			else {
				$result = Database::insert('users', array(
					'name' => filter('admin_add_group_name', $group),
					'type' => 'group',
					'group' => '-1',
					'data' => serialize(filter('admin_add_group_data', array()))
				));

				if($result === 1) {
					$messages .= Message::success(__('admin', 'group-added'));
				}
			}
		}

		$r = sprintf("<h2>%s</h2>\n",__('admin', 'add-group'));
		$form = new Form('self', 'post', 'add-group');

		$form->startFieldset(__("admin", 'group-information'));
			$form->addInput(__('admin', 'group'), 'text', 'group', self::get('group'));
			plugin('admin_add_group_custom_fields', array(&$form));
		$form->endFieldset();

		plugin('admin_add_group_custom_fieldset', array(&$form));

		$form->startFieldset(__('admin', 'save'));
			$form->addSubmit('', 'add-group', __('admin', 'add-group'));
		$form->endFieldset();

		$form = $form->endAndGetHTML();

		return $r.$messages.$form;
	}
	public static function get ($x) {
	    if(array_key_exists($x, $_POST)) return $_POST[$x];
	    //if(array_key_exists($x, self::$row)) return self::$row[$x];
	    return '';
	}
}