<?php

require_once '../../../cc-connectors/TopHat.php';

if(!Users::isValid()) {
	die('You are not logged in!');
}

