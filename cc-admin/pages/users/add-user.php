<?php

Admin::registerSubpage('users', 'add-user', __('admin', 'add-user'), 'AddUserPage::display');

class AddUserPage {
	public static function display () {
        $messages = "";
		if($_POST['cc_form'] === 'add-user') {
			$username = $_POST['username'];
			$password = $_POST['password'];
			$cpassword = $_POST['confirm-password'];
			$group = $_POST['group'];

			if($password != $cpassword) {
				$messages .= Message::error(__('admin', 'passwords-dont-match'));
			}
			else {
				$rows = Database::select('users', 'name', array('name = ? AND type = ?', $username, 'user'), null, 1)->fetch(PDO::FETCH_ASSOC);
				if(!empty($rows)) {
					$messages .= Message::error(__('admin', 'username-in-use'));
				}
				else {
					$hash = hash('whirlpool', $password);

					$result = Database::insert('users', array(
						'name' => filter('admin_add_user_username', $username),
						'value' => $hash,
						'type' => 'user',
						'group' => filter('admin_add_group', $group),
						'data' => serialize(filter('admin_add_user_data', array()))
					));

					if($result === 1) {
						$messages .= Message::success(__('admin', 'user-added'));
					}
				}
			}
		}

		$form = new Form('self', 'post', 'add-user');

		$groups = Users::allGroups();
		foreach ($groups as $key => $value) {
			$groups[$value->getId()] = $value->getName();
		}

		$form->startFieldset(__("admin", 'user-information'));
			$form->addInput(__('admin', 'username'), 'text', 'username', self::get('username'));
			$form->addInput(__('admin', 'password'), 'password', 'password');
			$form->addInput(__('admin', 'confirm-password'), 'password', 'confirm-password');
			$form->addSelectList(__('admin', 'group'), 'group', $groups, true, self::get('group'));
			plugin('admin_add_user_custom_fields', array(&$form));
			$form->addSubmit('', 'add-user', __('admin', 'add-user'));
		$form->endFieldset();

		plugin('admin_add_user_custom_fieldset', array(&$form));

		$form = $form->endAndGetHTML();

		return array(__('admin', 'add-user'), $messages.$form);
	}
	public static function get ($x) {
	    if(array_key_exists($x, $_POST)) return $_POST[$x];
	    //if(array_key_exists($x, self::$row)) return self::$row[$x];
	    return '';
	}
}