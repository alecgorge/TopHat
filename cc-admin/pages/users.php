<?php

Admin::registerPage('users', __('admin', 'user-management'), 'UsersPage::display', 3);
// AdminSidebar::registerForPage('plugins', 'PluginAdminPage::getMore');

class UsersPage {
	public static function display () {
		$r .= sprintf("<h2>%s</h2>", __('admin', 'user-management'));

		$users = Database::select('users', '*', array('type = ?', 'user'), array('name', 'ASC'));
		$groups = Database::select('users', '*', array('type = ?', 'group'), array('name', 'ASC'));

		var_dump($users->fetchAll(PDO::FETCH_ASSOC));

		return $r;
	}
}