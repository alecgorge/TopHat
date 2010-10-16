<?php
/**
 * Timers in CanyonCMS aren't guaranteed to be run at the specified time (whether that is 1
 * hour in the future, or a specific time). They are guaranteed to not be run BEFORE the 
 * specified time, but it will run on the first page load AFTER the time.
 * 
 * Timers are executed at the hook "system_ready"
 */
class Timers {
	/**
	 * Format:
	 */
	public static $timers = array();

	/**
	 * Sets up a timer.
	 *
	 * @param mixed $time If $specificTime is true then $time is an absolute timestamp. Otherwise, $time that a number seconds in the future. Also things like +1 Week work.
	 * @param callback $callback The callback to be run. The first and only argument is whatever you set $time as.
	 * @param boolean $specificTime Defaults to false. If true
	 */
	public static function add ($time, $callback, $specificTime = false) {
		if(!is_callable($callback)) {
			error_log('$callback is not a valid callback in Timer::add', E_USER_ERROR);
		}

		$orig = $time;

		if(is_numeric($time) && !$specificTime) {
			$time = time() + $time;
		}
		else if (is_string($time) && !$specificTime) {
			$time = strtotime($time);
		}

		self::$timers[] = array(
			'time' => (int)$time,
			'orig-time' => $orig,
			'callback' => $callback
		);
	}

	public static function callTimers () {
		$time = time();
		foreach(self::$timers as $t) {
			if($time >= $t['time']) {
				call_user_func_array($t['callback'], array($t['orig-time']));
			}
		}
	}
}

function timer ($time, $callback, $specificTime = false) {
	Timers::add($time, $callback, $specificTime);
}

Hooks::bind("system_ready", "Timers::callTimers");
