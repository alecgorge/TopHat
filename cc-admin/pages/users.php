<?php

Admin::registerPage('users', __('admin', 'user-management'), 'UsersPage::display', 3);
// AdminSidebar::registerForPage('plugins', 'PluginAdminPage::getMore');

class UsersPage {
	public static function display () {
		$r .= sprintf("<h2>%s</h2>", __('admin', 'user-management'));

		$users = Database::select('users', '*', array('type = ?', 'user'), array('name', 'ASC'));
		$groups = Database::select('users', '*', array('type = ?', 'group'), array('name', 'ASC'));
		$groups_array = array();


		$groups_table = new Table("groups");
		$groups_table->addHeader(array(__('admin',"group-name"), __('admin', "actions")));
		foreach($groups->fetchAll(PDO::FETCH_ASSOC) as $key => $value) {
			$group_array[$value['id']] = $value['name'];
			$groups_table->addRow(array($value['name']));
		}
		$groups_table = $groups_table->html();

		$users_table = new Table("users");
		$users_table->addHeader(array(__('admin', 'name'), __('admin', 'group'), __('admin', 'actions')));
		foreach($users->fetchAll(PDO::FETCH_ASSOC) as $key => $value) {
			$users_table->addRow(array($value['name'], $group_array[$value['group']]));
		}
		$r .= "<h3>".__('admin', 'users')."</h3>".$users_table->html()."<h3>".__('admin', 'groups')."</h3>".$groups_table;
		// var_dump);

		return $r;
	}
}