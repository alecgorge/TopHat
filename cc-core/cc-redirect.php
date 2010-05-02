<?php
/**
 * This function allows you to redirect people.
 *
 * This function uses an HTTP redirect to redirect people. That means it MUST be done before anycontent is displayed! The second argument is whether or not it is relative to the root of the site. For example,
 * if you should use <code>cc_redirect('http://google.com/');
 * cc_redirect('cc-admin/', true);</code>
 *
 * @param string $url The path to redirect to.
 * @param bool $relative Whether or not to redirect relative to CC_CORE
 * @return null This method will never return anything.
 */
function cc_redirect ($url, $relative = false) {
	$continue = true;
	
	Hooks::execute('cc_redirect', array(&$url, &$relative, &$continue));
	
	if($continue === false) return;

	if($relative) {
		header(sprintf('location: %s', $url));
	}
	
	header(sprintf('location: %s', $url));
}
?>