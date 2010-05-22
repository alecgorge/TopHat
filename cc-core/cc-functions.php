<?php
function array_remove_empty($arr) {
	foreach($arr as $k => $v) {
		if(!empty($v)) {
			$r[] = $v;
		}
	}
	return $r;
}

/**
 * Gets the path to a CSS stylesheet at (by default) content/css/*.css
 *
 * @param string $what_css
 * @return string
 */
function get_css ($what_css) {
	if(file_exists(CC_ROOT.CC_CONTENT.'css/'.$what_css.'.css')) {
		return CC_PUB_ROOT.CC_CONTENT.'css/'.$what_css.'.css';
	}
}