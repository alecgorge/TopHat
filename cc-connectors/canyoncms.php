<?php

require_once dirname(__FILE__).'/../cc-config.php';

// include hooking capabilites
require_once CC_CORE . 'cc-hooks.php';

// include include manager
require_once CC_CORE . 'cc-includes.php';

cc_core_include(array(
	'cc-functions.php',
	'cc-message.php',
	'cc-log.php',
	'cc-utf8.php',
	'cc-database.php',
	'cc-table.php',
	'cc-forms.php',
	'cc-uploadify.php',
	'cc-users.php'
));

plugin('system_after_content_load');