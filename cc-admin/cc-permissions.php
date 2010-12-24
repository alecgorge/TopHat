<?php

/**
 * How this class works:
 *	- loads all the std permissions from the db
 *	- creates 21
 */
class Permissions {
	public static $items = array();
	public static $byUser = array();
	public static function bootstrap () {
		$handle = DB::select('permissions');
		$raw = $handle->fetchAll(PDO::FETCH_ASSOC);
		foreach($raw as $row) {
			if($row['type'] == 'permission') {
				self::$items[] = $row;
			}
			else if(is_numeric($row['type'])) {
				if(!is_array(self::$byUser[$row['type']])) {
					self::$byUser[$row['type']] = array();
				}
				self::$byUser[$row['type']][$row['name']] = $row['value'];
			}
		}
	}

	public static function register ($name, $default, $type = '') {
		$r = (bool)DB::insert('permissions', array('name', 'value', 'type'), array($name, $default, $type));
		if($r) {
			self::bootstrap();
		}
		return $r;
	}

	public static function deregister ($name) {
		if(is_numeric($name)) {
			$r = (bool)DB::delete('permissions', array('permissions_id = ?', $name));
		}
		else {
			$r = (bool)DB::delete('permissions', array('name = ?', $name));
		}

		if($r) {
			self::bootstrap();
		}
		return $r;
	}

	/**
	 *
	 * @return array All the permission items.
	 */
	public static function getAll () {
		return self::$items;
	}
}

Permissions::bootstrap();
