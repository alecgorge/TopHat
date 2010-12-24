<?php

Admin::registerSubpage('users', 'edit-group', __('admin', 'edit-group'), 'GroupPage::display', 3);

class GroupPage {
	public static function display () {
		if(!is_numeric($_GET['id'])) {
			cc_redirect(Admin::link('users'));
		}
		if($_POST['cc_form'] == 'edit-group') {
			$id = $_GET['id'];
			$previous = (array)unserialize(urldecode($_POST['previous']));
			$group = $_POST['group'];
			$permissions = (array)$_POST['permissions'];
			$new = array_merge($previous, $permissions);
			foreach($new as &$v) {
				if($v == "true") {
					$v = true;
				}
			}

			if(DB::update('users', array('name', 'data'), array($group, serialize(filter('admin_edit_group_data', array('permissions' => $new)))), array('users_id = ?', $id))) {
				$message = Message::success(__('admin', 'group-information-updated'));
			}
			else {
				$message = Message::error(__('admin', 'database-error'));
			}
		}

		$p = Permissions::getAll();
		$g = new Group((int)$_GET['id']);

		$p_form = new Form('');
		$p_form->setCC_Form('edit-group');
			$p_form->startFieldset(__('admin', 'group-information'));
				$p_form->addInput(__('admin', 'group-name'), 'text', 'group', $g->getName());
			$p_form->endFieldset();

			$p_form->addHTML(sprintf("<h3>%s</h3>", __('admin', 'permissions')));

			$p_table = new Table('permissions');
			$p_table->addHeader(array(
				'Name', 'Allowed'
			));
			foreach($p as $k => $v) {
				$previous[$v['name']] = $g->isAllowed($v['name']);

				$p_table->addRow(array(
					__('permissions', $v['name']),
					sprintf('<input type="checkbox" name="permissions[%s]"%svalue="1"/>', $v['name'], ($g->isAllowed($v['name']) ? ' checked="checked"' : ''))
				));
			}
			$p_form->addHidden('previous', urlencode(serialize($previous)));
		$p_form->addHTML($p_table->html());
		$p_form->addHTML(sprintf('<input type="submit" name="%s" value="%s" class="input-submit"/>', 'save-permissions', __('admin', 'save-changes')));

		return sprintf('<h2>%s: %s</h2>%s', __('admin', 'edit-group'), $g->getName(), $message).$p_form->html();
	}
}