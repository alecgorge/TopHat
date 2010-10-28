<?php

Admin::registerPage('users', __('admin', 'user-management'), 'UsersPage::display', 3);
// AdminSidebar::registerForPage('plugins', 'PluginAdminPage::getMore');

class UsersPage {
	public static function display () {
		$r .= sprintf("<h2>%s</h2>", __('admin', 'user-management'));

		$users = Database::select('users', '*', array('type = ?', 'user'), array('name', 'ASC'));
		$groups = Database::select('users', '*', array('type = ?', 'group'), array('name', 'ASC'));
		$groups_array = array();


		$groups_table = new Table("groups", array('', 'actions'));
		$groups_table->addHeader(array(__('admin',"group-name"), __('admin', "actions")));
		foreach($groups->fetchAll(PDO::FETCH_ASSOC) as $key => $value) {
			$group_array[$value['id']] = $value['name'];
			$groups_table->addRow(array(
				$value['name'],
				icon('user_edit', Admin::link('users/user-edit', array('id' => $value['id']))).icon('user_delete', Admin::link('users/user-delete', array('id' => $value['id'])))
			));
		}
		$groups_table = $groups_table->html();

		$users_table = new Table("users", array('', '', 'actions'));
		$users_table->addHeader(array(__('admin', 'name'), __('admin', 'group'), __('admin', 'actions')));
		foreach($users->fetchAll(PDO::FETCH_ASSOC) as $key => $value) {
			$users_table->addRow(array(
				$value['name'],
				$group_array[$value['group']],
				icon('user_edit', Admin::link('users/group-edit', array('id' => $value['id']))).icon('user_delete', Admin::link('users/group-delete', array('id' => $value['id']))),
			));
		}
		$r .= "<h3>".__('admin', 'users')."</h3>".$users_table->html()."<h3>".__('admin', 'groups')."</h3>".$groups_table;

		return $r;
	}
}