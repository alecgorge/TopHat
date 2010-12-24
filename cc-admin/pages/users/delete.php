<?php

if($_GET['page'] == 'users/delete' && is_numeric($_GET['id'])) {
	$res = Database::delete('users', array('users_id = ?', $_GET['id']));
	if($res !== false) {
		echo Message::success(__('admin', $_GET['type'].'-delete-success'));
		exit();
	}
	var_dump($res);
	exit();
}
