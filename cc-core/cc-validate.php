<?php
/**
 * A class with many static methods to validate various kinds of things.
 */

class Validate {
	/**
	 * Validates an ABSOLUTE link.
	 *
	 * @param string $url The string to preform the test on.
	 * @return bool True if is a valid link, false otherwise.
	 */
	public static function link ($url) {
		$r = filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED);
		Hooks::execute('validate_link', array(&$url, &$r));
		return $r;
	}
}
?>
