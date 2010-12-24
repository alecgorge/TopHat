<?php

Admin::registerSubpage('users', 'edit-group', __('admin', 'edit-group'), 'GroupPage::display', 3);

class GroupPage {
	public static function display () {
		if(!is_numeric($_GET['id'])) {
			cc_redirect(Admin::link('users'));
		}

		$p = Permissions::getAll();
		$g = new Group((int)$_GET['id']);

		$p_form = new Form();
			$p_table = new Table('permissions');
			$p_table->addHeader(array(
				'Name', 'Allowed'
			));
			foreach($p as $k => $v) {
				$p_table->addRow(array(
					__('permissions', $v['name']),
					sprintf('<input type="checkbox" name="%s"%s/>', $v['name'], ($g->isAllowed($v['name']) ? ' checked="checked"' : ''))
				));
			}
		$p_form->addHTML($p_table->html());
		$p_form->addHTML(sprintf('<input type="submit" name="%s" value="%s" class="input-submit" />', 'save-permissions', __('admin', 'save-permissions')));

		return sprintf("<h2>%s: %s</h2>", __('admin', 'permissions'), $g->getName()).$p_form->html();
	}
}