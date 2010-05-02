<?php
/**
 * @todo document class log
 */
class Log {
	private static $log;

	public static function add($val) {
		self::$log[] = array(
			'time' => microtime(true),
			'val' => $val
		);
	}

	public static function getLog() {
		return self::$log;
	}
}
?>
