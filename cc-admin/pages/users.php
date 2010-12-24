<?php

Admin::registerPage('users', __('admin', 'user-management'), 'UsersPage::display', 3);
AdminSidebar::registerForPage(array('users','users/add-user', 'users/edit-user','users/add-group', 'users/edit-group'), 'UsersPage::addUser');
AdminSidebar::registerForPage(array('users','users/add-group', 'users/edit-group','users/add-user', 'users/edit-user'), 'UsersPage::addGroup', -1);
AdminSidebar::registerForPage(array('users','users/add-group', 'users/edit-group','users/add-user', 'users/edit-user'), 'UsersPage::viewAll', -2);

class UsersPage {
	public static function display () {
		$r .= sprintf("<h2>%s</h2>", __('admin', 'user-management'));

		$users = Database::select('users', '*', array('type = ?', 'user'), array('name', 'ASC'));
		$groups = Database::select('users', '*', array('type = ?', 'group'), array('name', 'ASC'));
		$groups_array = array();


		$groups_table = new Table("groups", array('', 'actions'));
		$groups_table->addHeader(array(__('admin',"group-name"), __('admin', "actions")));
		foreach($groups->fetchAll(PDO::FETCH_ASSOC) as $key => $value) {
			$group_array[$value['users_id']] = $value['name'];
			$groups_table->addRow(array(
				$value['name'],
				icon('group_edit', Admin::link('users/edit-group', array('id' => $value['users_id']))).icon('group_delete', Admin::link('users/delete', array('id' => $value['users_id'], 'type' => 'group')), false, array('class' => 'delete-link'))
			));
		}
		$groups_table = $groups_table->html();

		$users_table = new Table("users", array('', '', 'actions'));
		$users_table->addHeader(array(__('admin', 'name'), __('admin', 'group'), __('admin', 'actions')));
		foreach($users->fetchAll(PDO::FETCH_ASSOC) as $key => $value) {
			$users_table->addRow(array(
				$value['name'],
				$group_array[$value['group']],
				icon('user_edit', Admin::link('users/edit-user', array('id' => $value['users_id'])))
					.icon('user_delete', Admin::link('users/delete', array('id' => $value['users_id'], 'type' => 'user')), false, array('class' => 'delete-link')),
			));
		}
		$r .= "<h3>".__('admin', 'users')."</h3>".$users_table->html()."<h3>".__('admin', 'groups')."</h3>".$groups_table;

		return $r;
	}

	public static function addUser () {
		return sprintf("<a href='%s' class='action'>%s%s</a>", Admin::link('users/add-user'), icon('user_add'), __('admin', 'add-a-user'));
	}

	public static function addGroup () {
		return sprintf("<a href='%s' class='action'>%s%s</a>", Admin::link('users/add-group'), icon('group_add'), __('admin', 'add-a-group'));
	}

	public static function viewAll () {
		return sprintf("<a href='%s' class='action'>%s%s</a>", Admin::link('users'), icon('group'), __('admin', 'view-all-users-and-groups'));
	}
}