<?php

Admin::registerSubpage('users', 'edit-user', __('admin', 'edit-user'), 'EditUserPage::display');

class EditUserPage {
	public static $row = array();
	public static function display () {
		$id = $_GET['id'];

		if(!is_numeric($id)) {
		    i18n::restore();
			cc_redirect(Admin::link('users'));

			return $r;
		}
		
		if($_POST['cc_form'] === 'edit-user') {
			$username = $_POST['name'];
			$password = $_POST['password'];
			$cpassword = $_POST['confirm-password'];
			$group = $_POST['group'];

			if($password != $cpassword) {
				$messages .= Message::error(__('admin', 'passwords-dont-match'));
			}
			else {
				$result = Database::select('users', '*', array('users_id = ?', $id));

				$row = $result->fetch(PDO::FETCH_ASSOC);

				$result = Database::select('users', '*', array('name = ?', $username));

				if(!empty($result)) {
					$userRow = $result->fetch(PDO::FETCH_ASSOC);
				}
				else {
					$result = false;
				}

				if($result && $userRow['name'] == $username && $id != $userRow['id']) {
					$messages .= Message::error(__('admin', 'username-in-use'));
				}
				else {

					if(!empty($password) && !empty($cpassword) && $password == $cpassword) {
						$hash = hash('whirlpool', $password);
					}
					else {
						$hash = $row['value'];
					}

					$data = unserialize($row['data']);

					$result = Database::update('users', array(
						'name' => filter('admin_edit_user_username', $username),
						'value' => $hash,
						'type' => 'user',
						'group' => filter('admin_edit_group', $group),
						'data' => serialize(filter('admin_edit_user_data', $data))
					), null, array(
						'id = ?',
						$id
					));

					if($result === 1) {
						$messages .= Message::success(__('admin', 'user-updated'));
					}
				}
			}
		}

		$result = Database::select('users', '*', array('users_id = ?', $id));

		if(empty($result)) {
		    i18n::restore();
			cc_redirect(Admin::link('users'));
		}

		self::$row = $result->fetch(PDO::FETCH_ASSOC);

		$r = sprintf("<h2>%s</h2>\n",__('admin', 'edit-user'));
		$form = new Form('self', 'post', 'edit-user');

		$form->startFieldset(__("admin", 'user-information'));
			$form->addInput(__('admin', 'username'), 'text', 'name', self::get('name'));
			$form->addInput(__('admin', 'password'), 'password', 'password');
			$form->addInput(__('admin', 'confirm-password'), 'password', 'confirm-password');
			$form->addSelectList(__('admin', 'group'), 'group', Users::allGroups(), true, self::get('group'));
			plugin('admin_edit_user_custom_fields', array(&$form));
		$form->endFieldset();

		plugin('admin_edit_user_custom_fieldset', array(&$form));

		$form->startFieldset(__('admin', 'save'));
			$form->addSubmit('', 'edit-user', __('admin', 'edit-user'));
		$form->endFieldset();

		$form = $form->endAndGetHTML();

		return $r.$messages.$form;
	}
	public static function get ($x) {
	    if(array_key_exists($x, $_POST)) return $_POST[$x];
	    if(array_key_exists($x, self::$row)) return self::$row[$x];
	    return '';
	}
}