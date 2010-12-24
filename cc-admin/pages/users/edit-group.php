<?php

Admin::registerSubpage('users', 'edit-group', __('admin', 'edit-group'), 'GroupPage::display', 3);

class GroupPage {
	public static function display () {
		$p = Permissions::getAll();

		$p_table = new Table('permissions');
		foreach($p as $k => $v) {

		}
		return sprintf("<h2>%s</h2>", __('admin', 'permissions')).$p_table->html();
	}
}