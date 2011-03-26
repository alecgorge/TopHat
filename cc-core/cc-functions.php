<?php

function array_remove_empty($arr) {
	foreach($arr as $k => $v) {
		if(!empty($v)) {
			$r[] = $v;
		}
	}
	return $r;
}

function mail_utf8($to, $subject = '(No subject)', $message = '', $header = '') {
	$header_ = 'MIME-Version: 1.0' . "\r\n" . 'Content-type: text/plain; charset=UTF-8' . "\r\n";
	mail($to, '=?UTF-8?B?'.base64_encode($subject).'?=', $message, $header_ . $header);
}

function array_subkeys ($haystack, $needle, $recursive = false) {
	$results = array();
	foreach($haystack as $k => $v) {
		foreach($v as $key => $value) {
			if($key == $needle) {
				array_push($results, $value);
			}
			else if ($key == $recursive) {
				array_merge($results, array_subkeys($v[$recursive], $needle, $recursive));
			}
		}
	}
	return $results;
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

/**
 * Checks $_POST for each of the arguments passed, or optionally the first argument can be an array of keys to check. Uses array_key_exists.
 *
 * @param string $first What to check for in $_POST. Optionally, can be an array of keys to check.
 * @return boolean If all are present, return true.
 */
function check_post ($first) {
	if(is_array($first)) {
		return array_key_exists($first, $_POST);
	}
	else {
		$args = func_get_args();
		foreach($args as $val) {
			if(!array_key_exists($val, $_POST)) {
				return false;
			}
		}
		return true;
   	}
}