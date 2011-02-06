<?php

class Tokenizer {
	public static $tokens = array();

	public static function perform ($string, $keys, $values = null) {
		if($values === null) {
			self::$tokens = $keys;
		}
		else {
			self::$tokens = array_combine($keys, $value);
		}

		return preg_replace('/[\\\]{1}(\{[0-9A-Za-z-_]+\})/', '\1', preg_replace_callback('/([^\\\]{1})(\{[0-9A-Za-z-_]+\})/', 'Tokenizer::replace', $string));
	}

	public static function replace ($matches) {
		$key = trim($matches[2], "{}");
		if(array_key_exists($key, self::$tokens)) {
			if(is_callable(self::$tokens[$key])) {
				return $matches[1].call_user_func(self::$tokens[$key]);
			}
			else {
				return $matches[1].self::$tokens[$key];
			}
		}
		else {
			error_log("$key isn't a valid token!", E_USER_WARNING);
			return $matches[1].'';
		}
	}
}

class Tokens {
	public static $tokens = array();

	/**
	 * Register a token to be used with the tokenizer filter.
	 *
	 * @param mixed $name Can be string or array. If array and $value is null, then $name must be associative. Otherwise, $name can be an array of keys and $value an array of values.
	 * @param mixed $value See $name.
	 */
	public static function register ($name, $value = null) {
		if(is_array($name)) {
			if(is_null($value)) {
				foreach($name as $key => $val) {
					self::$tokens[$key] = $val;
				}
			}
			else {
				foreach($name as $key => $val) {
					self::$tokens[$val] = $value[$key];
				}
			}
		}
		else {
			self::$tokens[$name] = $value;
		}
	}

	public static function registerDefaults () {
		Tokens::register(array(
			"theme_dir" => Themes::$curr_theme->getPublicPath(),
			"public_root" => CC_PUB_ROOT,
			"root" => CC_ROOT,
			"public_content_dir" => CC_PUB_ROOT.CC_CONTENT,
			"content_dir" => CC_ROOT.CC_CONTENT,
			"uploads_dir" => CC_ROOT.CC_UPLOADS,
			"public_uploads_dir" => CC_PUB_ROOT.CC_UPLOADS
		));
	}

	public static function fetch ($name) {
		return self::$tokens[$name];
	}

	public static function filter ($value) {
		return Tokenizer::perform($value, self::$tokens);
	}
}

Hooks::bind("system_after_themes_load", "Tokens::registerDefaults");
Filters::bind("content_get_inital", "Tokens::filter");