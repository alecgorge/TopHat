<?php
/**
 * Includes the specified file in the TH_CORE directory.
 * 
 * @param string $file
 * @return string The full path to the included file.
 */
function cc_core_include ($file) {
	foreach((array)$file as $f) {
		plugin('core_include', array($f));
		$f = filter('core_include', TH_CORE.$f);
		require_once $f;
	}
	return $file;
}

/**
 * Includes the theme file for the given theme. (it is pretty important).
 *
 * @param string $theme The name of the theme!
 */
function cc_theme_include ($theme) {
	plugin('core_theme_include', array($theme));
	$file = filter('core_theme_include', TH_ROOT.TH_THEMES.$theme.'/index.tpl.php');

	if(file_exists($file)) {
		require_once $file;
	}
}

function cc_admin_include ($file) {
	require_once TH_ADMIN.$file;
}