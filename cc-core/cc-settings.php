<?php
/**
 * A simple class used to store the settings for CanyonCMS. Contains a combination of CC_CONFIG file and db loaded ones.
 */
class Settings {
	/**
	 * @var array An associative array of all the settings stored.
	 */
	private static $settings = array();
	/**
	 * @var array The raw information loaded from the database.
	 */
	private static $db_load = array();
	
	/**
	 * Set a setting.
	 * 
	 * This will overwrite previous entries at $k.
	 *
	 * @param string $package The package containing $k.
	 * @param mixed $k The key of the setting.
	 * @param mixed $v The value assigned to $k.
	 * @param bool $local If true, it will not be committed to the database.
	 * @return mixed Returns $v or false if a plugin canceled setting of the variable.
	 */
	public static function set($package, $k, $v, $local = false) {
		if(empty($v)) return false;

		self::packageCreateIfNotExist($package);

		plugin('cc_settings_set', array($package, $k, $v));
		$v = filter('cc_settings_set', $v);

		$isUpdate = array_key_exists($k, self::$settings[$package]);
		
		self::$settings[$package][$key] = $v;

		if($isUpdate && !$local) {
			DB::update('settings', array('data','package', 'name', 'last_modified'), array(serialize($v), $package, $k, time()), array('id = ?', self::$settings[$package][$key]['id']));
		}
		elseif(!$local) {
			DB::insert('settings', array('package','name','data', 'last_modified'), array($package, $k, serialize($v), time()));
		}
		
		return $v;
	}
	
	/**
	 * Set multiple settings at a time.
	 *
	 * Give two arrays, or one associative array, adds them to the settings list. If given two arrays, the first array is the list of keys and the second the list of values.
	 *
	 * @param string $package The package the values are to go in.
	 * @param array $k The array of keys or the associative array.
	 * @param array $v Optional. The list of values. Required if $k is not associative.
	 * @return mixed The number of items added, or false if the parameters were incorrect. Plugins can skip setting a varible, so that may change results.
	 */
	public static function setAll($package, $k, $v = null) {
		if(!is_array($k)) return false;
		self::packageCreateIfNotExist($package);

		// the number of items added
		$count = 0;
		
		foreach($k as $key => $val) {
			if($v === null) {
				self::set($package, $key, $val);
			}
			else {
				self::set($package, $val, $v[$k]);
			}
			$count++;
		}

		return $count;
	}
	
	/**
	 * Get a setting.
	 * 
	 * This will retrived the value at $k in the package $package.
	 *
	 * @param string $package The package that $k is in.
	 * @param mixed $k The key of the setting.
	 * @param bool If this is true, it returns the value instead of the array.
	 * @return mixed Returns the value assigned to $k or null if the key doesn't exist.
	 */
	public static function get($package, $k, $ret = false) {
		$r = null;
		if(array_key_exists($k, self::$settings[$package])) {
			$r = self::$settings[$package][$k];
		}

		plugin('cc_settings_get', array($package, $k, &$ret));
		$r = filter('cc_settings_get', $r);

		if($ret) {
			$r = $r['value'];
		}
		
		return $r;
	}
	
	/**
	 * Get all the settings.
	 *
	 * This retrieves all the settings in a associative array.
	 *
	 * @return array The settings set so far.
	 */
	public static function getAll() {
		return filter('cc_settings_getall', self::$settings);
	}

	/**
	 * Load settings from db on startup
	 */
	public static function bootstrap () {
		$results = DB::select('settings', '*');
		self::$db_load = $results->fetchAll(PDO::FETCH_ASSOC);
		
		foreach(self::$db_load as $key => $value) {
			self::addToList('core', array(
				$value['name'] => array(
					'id' => $value['id'],
					'last_modified' => $value['last_modified'],
					'value' => unserialize($value['data'])
				)
			));
		}
	}

	private static function packageCreateIfNotExist ($package) {
		if(!array_key_exists($package, self::$settings)) {
			 self::$settings[$package] = array();
		 }
	 }

	 /* Acutally adds to array without adding to DB */
	private static function addToList($package, $k, $v = null) {
		if(is_array($k)) {
			foreach($k as $key => $val) {
				if($v === null) {
					plugin('cc_db_settings_set', array($package, $key, $val));
					self::$settings[$package][$key] = filter('cc_db_settings_set', $val);
				}
				else {
					plugin('cc_db_settings_set', array($package, $val, $v[$key]));
					self::$settings[$package][$val] = filter('cc_db_settings_set', $v[$key]);
				}
			}

		}
		else {
			self::$settings[$s][$k] = $v;
		}
	}
}
Hooks::bind('system_ready', 'Settings::bootstrap');
?>