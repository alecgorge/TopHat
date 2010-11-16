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
			$username = $_POST['eusername'];
			$password = $_POST['epassword'];
			$cpassword = $_POST['econfirm-password'];
			$group = $_POST['egroup'];

			if($password != $cpassword) {
				$messages .= Message::error(__('admin', 'passwords-dont-match'));
			}
			else {
				$result = Database::select('users', '*', array('id = ?', $id));

				$row = $result->fetch(PDO::FETCH_ASSOC);

				var_dump($password, $cpassword);

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

		$result = Database::select('users', '*', array('id = ?', $id));

		if(empty($result)) {
		    i18n::restore();
			cc_redirect(Admin::link('users'));
		}

		self::$row = $result->fetch(PDO::FETCH_ASSOC);

		$r = sprintf("<h2>%s</h2>\n",__('admin', 'edit-user'));
		$form = new Form('self', 'post', 'edit-user');

		$form->startFieldset(__("admin", 'user-information'));
			$form->addInput(__('admin', 'username'), 'text', 'eusername', self::get('name'));
			$form->addInput(__('admin', 'password'), 'password', 'epassword');
			$form->addInput(__('admin', 'confirm-password'), 'password', 'econfirm-password');
			$form->addSelectList(__('admin', 'group'), 'egroup', Users::allGroups(), true, self::get('group'));
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