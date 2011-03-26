<?php

require_once '../../../cc-connectors/canyoncms.php';

if(!Users::isValid()) {
	die('You are not logged in!');
}

