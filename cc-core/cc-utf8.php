<?php

//--------------------------------------------------------------------
/**
* US-ASCII transliterations of Unicode text
* Ported Sean M. Burke's Text::Unidecode Perl module (He did all the hard work!)
* Warning: you should only pass this well formed UTF-8!
* Be aware it works by making a copy of the input string which it appends transliterated
* characters to - it uses a PHP output buffer to do this - it means, memory use will increase,
* requiring up to the same amount again as the input string
* @see http://search.cpan.org/~sburke/Text-Unidecode-0.04/lib/Text/Unidecode.pm
* @param string UTF-8 string to convert
* @param string (default = ?) Character use if character unknown
* @return string US-ASCII string
* @package utf8_to_ascii
*/
function utf8_to_ascii($str, $unknown = '?') {

	# The database for transliteration stored here
	static $UTF8_TO_ASCII = array();

	# Variable lookups faster than accessing constants
	$UTF8_TO_ASCII_DB = UTF8_TO_ASCII_DB;

	if ( strlen($str) == 0 ) { return ''; }

	$len = strlen($str);
	$i = 0;

	# Use an output buffer to copy the transliterated string
	# This is done for performance vs. string concatenation - on my system, drops
	# the average request time for the example from ~0.46ms to 0.41ms
	# See http://phplens.com/lens/php-book/optimizing-debugging-php.php
	# Section  "High Return Code Optimizations"
	ob_start();

	while ( $i < $len ) {

		$ord = NULL;
		$increment = 1;

		$ord0 = ord($str{$i});

		# Much nested if /else - PHP fn calls expensive, no block scope...

		# 1 byte - ASCII
		if ( $ord0 >= 0 && $ord0 <= 127 ) {

			$ord = $ord0;
			$increment = 1;

		} else {

			# 2 bytes
			$ord1 = ord($str{$i+1});

			if ( $ord0 >= 192 && $ord0 <= 223 ) {

				$ord = ( $ord0 - 192 ) * 64 + ( $ord1 - 128 );
				$increment = 2;

			} else {

				# 3 bytes
				$ord2 = ord($str{$i+2});

				if ( $ord0 >= 224 && $ord0 <= 239 ) {

					$ord = ($ord0-224)*4096 + ($ord1-128)*64 + ($ord2-128);
					$increment = 3;

				} else {

					# 4 bytes
					$ord3 = ord($str{$i+3});

					if ($ord0>=240 && $ord0<=247) {

						$ord = ($ord0-240)*262144 + ($ord1-128)*4096
							+ ($ord2-128)*64 + ($ord3-128);
						$increment = 4;

					} else {

						ob_end_clean();
						trigger_error("utf8_to_ascii: looks like badly formed UTF-8 at byte $i");
						return FALSE;

					}

				}

			}

		}

		$bank = $ord >> 8;

		# If we haven't used anything from this bank before, need to load it...
		if ( !array_key_exists($bank, $UTF8_TO_ASCII) ) {

			$bankfile = UTF8_TO_ASCII_DB. '/'. sprintf("x%02x",$bank).'.php';

			if ( file_exists($bankfile) ) {

				# Load the appropriate database
				if ( !include  $bankfile ) {
					ob_end_clean();
					trigger_error("utf8_to_ascii: unable to load $bankfile");
				}

			} else {

				# Some banks are deliberately empty
				$UTF8_TO_ASCII[$bank] = array();

			}
		}

		$newchar = $ord & 255;
		if ( $newchar >= 0 && $newchar <= 127 ) {
			echo $str{$i};
		}
		else {
			if ( array_key_exists($newchar, $UTF8_TO_ASCII[$bank]) ) {
				echo $UTF8_TO_ASCII[$bank][$newchar];
			} else {
				echo $unknown;
			}
		}

		$i += $increment;

	}

	$str = ob_get_contents();
	ob_end_clean();
	return $str;

}


/**
 * A few utilites to work with UTF-8 strings. Very useful for localization/internationalization/compatibility
 */
class UTF8 {
	/**
	 * Converts a non UTF-8 string into a UTF-8 string.
	 *
	 * @param string $str A non UTF-8 String
	 * @return string The UTF-8 string.
	 */
	public static function convertToUTF8($str) {
		if( mb_detect_encoding($str,"UTF-8, ISO-8859-1, GBK")!="UTF-8" ) {
			return  iconv("gbk","utf-8",$str);
		}
		else {
			return $str;
		}
	}

	/**
	 * Transliterates as many non-ascii chars as possible, then removes the rest. Makes a nice slug.
	 *
	 * For example: baño baño baño becomes bano-bano-bano
	 *
	 * @param string $string The "unclean" input.
	 * @return string The nice slug!
	 */
	public static function slugify ($string) {
		// make sure it is UTF-8
		$string = self::convertToUTF8($string);

		// backups are good
		$orig_string = $string;

		$string = filter('utf8_slugify_before', $string);

		$string = utf8_to_ascii($string);

		// strip all remaning non slug-safe chars (A-z 0-9 - _)
		$string = preg_replace("/[^a-zA-Z0-9-_]/", "-", $string);

		// remove 2 or more -'s in a row.
		$string = preg_replace('/[-]+/', '-', $string);

		// trim leading and trailing -'s
		$string = trim($string, '-');

		// whoa, we don't want an empty slug!
		if(empty($string)) {
			// we will just base64_encode the slug and remove the '=' I guess. Any better ideas?
			$string = trim(base64_encode($orig_string), '=');
	   	}

		// done!
		return filter('utf8_slugify_after', $string);
	}

	/**
	 * A UTF-8 compliant htmlentities replacement. This encodes everything correctly, meaning even chinese content
	 *
	 * @param <type> $content
	 * @return <type>
	 */
	public static function htmlentities($content) {
		$oUnicodeReplace = new unicode_replace_entities();
		$content = $oUnicodeReplace->UTF8entities($content);
		return $content;
	}
}


if(!function_exists('mb_str_replace')) {

	function mb_str_replace($search, $replace, $subject) {

		if(is_array($subject)) {
			$ret = array();
			foreach($subject as $key => $val) {
				$ret[$key] = mb_str_replace($search, $replace, $val);
			}
			return $ret;
		}

		foreach((array) $search as $key => $s) {
			if($s == '') {
				continue;
			}
			$r = !is_array($replace) ? $replace : (array_key_exists($key, $replace) ? $replace[$key] : '');
			$pos = mb_strpos($subject, $s);
			while($pos !== false) {
				$subject = mb_substr($subject, 0, $pos) . $r . mb_substr($subject, $pos + mb_strlen($s));
				$pos = mb_strpos($subject, $s, $pos + mb_strlen($r));
			}
		}

		return $subject;

	}

}

//simple task: convert everything from utf-8 into an NCR[numeric character reference]
// from http://us2.php.net/manual/en/function.htmlentities.php#92105
class unicode_replace_entities {
	public function UTF8entities($content="") {
		$contents = $this->unicode_string_to_array($content);
		$swap = "";
		$iCount = count($contents);
		for ($o=0;$o<$iCount;$o++) {
			$contents[$o] = $this->unicode_entity_replace($contents[$o]);
			$swap .= $contents[$o];
		}
		return mb_convert_encoding($swap,"UTF-8"); //not really necessary, but why not.
	}
		public function unicode_string_to_array( $string ) { //adjwilli
		$strlen = mb_strlen($string);
		while ($strlen) {
			$array[] = mb_substr( $string, 0, 1, "UTF-8" );
			$string = mb_substr( $string, 1, $strlen, "UTF-8" );
			$strlen = mb_strlen( $string );
		}
		return $array;
	}
	public function unicode_entity_replace($c) { //m. perez
		$h = ord($c{0});
		if ($h <= 0x7F) {
			return $c;
		} else if ($h < 0xC2) {
			return $c;
		}

		if ($h <= 0xDF) {
			$h = ($h & 0x1F) << 6 | (ord($c{1}) & 0x3F);
			$h = "&#" . $h . ";";
			return $h;
		} else if ($h <= 0xEF) {
			$h = ($h & 0x0F) << 12 | (ord($c{1}) & 0x3F) << 6 | (ord($c{2}) & 0x3F);
			$h = "&#" . $h . ";";
			return $h;
		} else if ($h <= 0xF4) {
			$h = ($h & 0x0F) << 18 | (ord($c{1}) & 0x3F) << 12 | (ord($c{2}) & 0x3F) << 6 | (ord($c{3}) & 0x3F);
			$h = "&#" . $h . ";";
			return $h;
		}
	}
}//


?>
