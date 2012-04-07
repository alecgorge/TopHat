<?php

if($_GET['page'] == 'content/delete-page' && is_numeric($_POST['id'])) {
	$type = Content::getType($_POST['id']);
	$res = Node::action('delete', $type, array($_POST['id']));

	if($res !== false) {
		echo Message::success(__('admin', 'page-delete-success'));
		exit();
	}
	var_dump($res);
	exit();
}
