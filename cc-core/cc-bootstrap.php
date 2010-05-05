<?php
// include hooking capabilites
require CC_CORE.'cc-hooks.php';

// include include manager
require CC_CORE.'cc-includes.php';

// fix the $_GET variable
cc_core_include('cc-functions.php');

// utf-8 utils
cc_core_include('cc-utf8.php');

// loggin utils
cc_core_include('cc-log.php');

// include redirection utils
cc_core_include('cc-redirect.php');

// have we installed yet? this checks if $database and $timezone are set in the CC_CONFIG file.
define('INSTALLED', (isset($database) && isset($timezone)));
if(!INSTALLED) {
	cc_redirect('cc-admin/install/', true);
}

// the all important db abstraction layer
cc_core_include('cc-database.php');

// get the validation methods
cc_core_include('cc-validate.php');

// setup settings manager
cc_core_include('cc-settings.php');

// setup plugin architecture
cc_core_include('cc-plugins.php');

// let some things run (pulling settings, etc) before we go on to pull the page info
plugin('system_ready');

// the all important theme
cc_core_include('cc-themes.php');

plugin('system_after_themes_load');

// and finally: the content
cc_core_include('cc-content.php');

plugin('system_after_content_load');

// let's display something :)
cc_theme_include(Content::getTheme());

plugin('system_complete');

register_shutdown_function(function () {
	$time = microtime(true) - CC_START;
	echo "<p>Took: ".round($time, 3)." seconds or ".round($time*1000, 3)." miliseconds.</p>";
});
?>