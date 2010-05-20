<?php

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

		// Cyrillic Letters
		$iso = array(
		   "Є"=>"YE","І"=>"I","Ѓ"=>"G","і"=>"i","№"=>"#","є"=>"ye","ѓ"=>"g",
		   "А"=>"A","Б"=>"B","В"=>"V","Г"=>"G","Д"=>"D",
		   "Е"=>"E","Ё"=>"YO","Ж"=>"ZH",
		   "З"=>"Z","И"=>"I","Й"=>"J","К"=>"K","Л"=>"L",
		   "М"=>"M","Н"=>"N","О"=>"O","П"=>"P","Р"=>"R",
		   "С"=>"S","Т"=>"T","У"=>"U","Ф"=>"F","Х"=>"X",
		   "Ц"=>"C","Ч"=>"CH","Ш"=>"SH","Щ"=>"SHH","Ъ"=>"'",
		   "Ы"=>"Y","Ь"=>"","Э"=>"E","Ю"=>"YU","Я"=>"YA",
		   "а"=>"a","б"=>"b","в"=>"v","г"=>"g","д"=>"d",
		   "е"=>"e","ё"=>"yo","ж"=>"zh",
		   "з"=>"z","и"=>"i","й"=>"j","к"=>"k","л"=>"l",
		   "м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
		   "с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"x",
		   "ц"=>"c","ч"=>"ch","ш"=>"sh","щ"=>"shh","ъ"=>"",
		   "ы"=>"y","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya","đ"=>"dz","Đ"=>"DZ"
		);

		// More Cyrillic Letters
		$iso2_k = array(
		"Щ", "Ш", "Ч", "Ц","Ю", "Я", "Ж", "А","Б","В","Г","Д","Е","Ё","З","И","Й","К","Л","М","Н",
		"О","П","Р","С","Т","У","Ф","Х", "Ь","Ы","Ъ","Э","Є","Ї","І","Ґ",
		"щ", "ш", "ч", "ц","ю", "я", "ж", "а","б","в","г","д","е","ё","з","и","й","к","л","м","н",
		"о","п","р","с","т","у","ф","х", "ь","ы","ъ","э","є","ї","і","ґ");
		$iso2_v = array(
		"Shh","Sh","Ch","C","Ju","Ja","Zh","A","B","V","G","D","Je","Jo","Z","I","J","K","L","M",
		"N","O","P","R","S","T","U","F","Kh","","Y", "`","E","Je","Ji","I","G",
		"shh","sh","ch","c","ju","ja","zh","a","b","v","g","d","je","jo","z","i","j","k","l","m",
		"n","o","p","r","s","t","u","f","kh","","y", "","e","je","ji","i","g"
		);

		// Greek letters
		$greekTranslit = array(
			"α"=>"a","β"=>"b","γ"=>"g","δ"=>"d","ε"=>"e","ζ"=>"z","η"=>"h","θ"=>"h",
			"ι"=>"i","κ"=>"k","λ"=>"l","μ"=>"m","ν"=>"n","ξ"=>"s","ο"=>"o","π"=>"p",
			"ρ"=>"r","σ"=>"s","τ"=>"t","υ"=>"y","φ"=>"f","χ"=>"h","ψ"=>"s","ω"=>"w"
		);

		// put the Cyrillic together
		foreach($iso2_k as $key => $value) {
			$iso2[$value] = $iso2_v[$key];
		}

		// some iconv installations suck so we have to help with the most
		// simple ones
		$german_and_french = array(
			"ä" => "ae", "Ä" => "Ae",
			"ö" => "oe", "Ö" => "Oe",
			"ü" => "ue", "Ü" => "Ue",
			"ß" => "ss",
			"ç" => "c", "Ç" => "C",
			"æ" => "ae", "Æ" => "AE", "œ" => "oe", "Œ" => "OE",
			"é" => "e", "É" => "E", "ê" => "e", "Ê" => "E", "è" => "e", "È" => "E",
			"á" => "a", "Á" => "A", "à" => "a", "À" => "A",
			"ò" => "o", "Ò" => "O", "ô" => "o", "Ô" => "O", "ó" => "o", "Ó" => "O"
		);

		// transliterate some Cyrillic
		$string = strtr($string, $iso);

		// transliterate the rest of the Cyrillic
		$string = strtr($string, $iso2);

		// transliterate the Greek
		$string = strtr($string, $greekTranslit);

		// transliterate the "common" accents.
		$string = strtr($string, $german_and_french);

		// try to transliterate anything else with the iconv installation.
		$string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);

		// slugs are always lowercase
		$string = strtolower($string);

		// iconv can add some random chars to denote accents.
		$string = str_replace(array('"',"'","^","~",'`'), "", $string);

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
