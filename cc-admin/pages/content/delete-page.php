<?php

if($_GET['page'] == 'content/delete-page' && is_numeric($_GET['id'])) {
	$type = Content::getType($_GET['content_id']);
	$res = Node::action('delete', $type, array($_GET['id']));

	if($res !== false) {
		echo Message::success(__('admin', 'page-delete-success'));
		exit();
	}
	var_dump($res);
	exit();
}
