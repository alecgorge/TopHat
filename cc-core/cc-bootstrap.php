<?php
// include hooking capabilites
require TH_CORE.'cc-hooks.php';

// include include manager
require TH_CORE.'cc-includes.php';

cc_core_include('cc-urlmap.php');

// some helper functions
cc_core_include('cc-functions.php');

cc_core_include('cc-message.php');

cc_core_include('cc-timers.php');

cc_core_include('cc-library.php');

cc_core_include('cc-tokenizer.php');

// forms
cc_core_include('cc-forms.php');

cc_core_include('cc-table.php');

cc_core_include('cc-icons.php');

cc_core_include('cc-image.php');

cc_core_include('cc-uploadify.php');

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

// i18n is important!
cc_core_include('cc-i18n.php');

cc_core_include('cc-editors.php');

cc_core_include('cc-users.php');

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

if(TH_DEBUG)
register_shutdown_function(function () {
	$time = microtime(true) - CC_START;
	$dbTime = Database::$totalTime;
	echo "<p>Took: ".round($time, 4)." seconds or ".round($time*1000, 3)." milliseconds (DB: ".round($dbTime*1000, 4) ." milliseconds ".round($dbTime/$time, 6)."%).</p>";
	echo "<pre>";
	print_r(Log::getLog());
	echo "</pre>";
});
?>