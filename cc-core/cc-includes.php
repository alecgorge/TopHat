<?php
/**
 * Includes the specified file in the CC_CORE directory.
 * 
 * @param string $file
 * @return string The full path to the included file.
 */
function cc_core_include ($file) {
	plugin('core_include', array($file));
	$file = filter('core_include', CC_CORE.$file);
	require_once $file;
	return $file;
}

/**
 * Includes the theme file for the given theme. (it is pretty important).
 *
 * @param string $theme The name of the theme!
 */
function cc_theme_include ($theme) {
	plugin('core_theme_include', array($theme));
	$file = filter('core_theme_include', CC_ROOT.CC_THEMES.$theme.'/index.tpl.php');

	if(file_exists($file)) {
		require_once $file;
	}
}
?>