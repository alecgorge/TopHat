<?php

/**
 * How this class works:
 *	- loads all the std permissions from the db
 *	- creates 21
 */
class Permissions {
	private static $items = array();
	public static function bootstrap () {
		$handle = DB::select('permissions');
		self::$items = $handle->fetchAll(PDO::FETCH_ASSOC);
	}

	public static function register ($name, $type, $default) {
		$r = (bool)DB::insert('permissions', array('name', 'default_value', 'type'), array($name, $default, $type));
		if($r) {
			self::bootstrap();
		}
		return $r;
	}

	public static function deregister ($name) {
		$r = (bool)DB::delete('permissions', array('name = ?', $name));
		if($r) {
			self::bootstrap();
		}
		return $r;
	}
}