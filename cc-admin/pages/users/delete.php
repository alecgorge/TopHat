<?php

if($_GET['page'] == 'users/delete' && is_numeric($_GET['id'])) {
	$type = Content::getType($_GET['id']);

	$res = Database::delete('users', array('id = ?', $_GET['id']));
	if($res !== false) {
		echo Message::success(__('admin', 'group-delete-success'));
		exit();
	}
	var_dump($res);
	exit();
}
