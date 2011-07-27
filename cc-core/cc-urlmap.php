<?php

class URLMap {
	private static $map = array();

	public static function from($urlPattern, $to, $weight = 0) {
		if(!array_key_exists($weight, self::$map)) {
			self::$map[$weight] = array();
		}
		self::$map[$weight][$urlPattern] = $to;
	}

	public static function process () {
		$q = array_key_exists('q', $_GET) ? $_GET['q'] : "";

		$url_parts = explode("/", rtrim($q, "/")."/");

		ksort(self::$map);
		foreach(self::$map as $weight => $arr) {
			foreach($arr as $url => $callback) {
				if($url == "*") {
					call_user_func($callback, $q);
					return;
				}
				$parts = array_remove_empty(explode("/", ltrim($url, "/")));

				preg_match_all("#:([a-zA-Z_]+)#", $url, $matches);
				$expecting = count($matches[1]);

				// if there is a blank at the end it will be one longer
				if(count($url_parts) == count($parts) || count($url_parts) - 1 == count($parts)) {
					foreach($parts as $k => $v) {
						if($v == "*") {
							$ret_vals[] = implode("/", array_slice($url_parts, $k));
						}
						elseif(substr($v, 0, 1) == ":") {
							$ret_vals[substr($v, 1)] = $url_parts[$k];
						}
						elseif($url_parts[$k] == $v) {
							continue;
						}
						else {
							break;
						}
					}

					if(count($ret_vals) == $expecting) {
						call_user_func($callback, $ret_vals);
						return;
					}
				}
				else {
					continue;
				}
			}
		}
	}
}

Hooks::bind("system_after_content_load", "URLMap::process", 100);