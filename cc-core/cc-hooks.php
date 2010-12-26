<?php
/**
 * A class that keeps track of all of the hooks registered and the associated binds.
 */
class Hooks {
	/**
	 * @var array The list of registered hooks.
	 */
	private static $hooks = array();
	
	/**
	 * @var array An associative array of hook names and an array of callbacks.
	 */
	private static $registered = array();
	
	/**
	 * Register a valid hook.
	 * 
	 * This registers $hook as a valid hook.
	 *
	 * @param string $hook A string to be a valid hook.
	 * @return bool True if hook is previously unused. False if the hook has been set previously.
	 */
	public static function register ($hook) {
		if(self::hookExists($hook)) {
			$hooks = &self::getAllHooks();
			$hooks[] = $hook;
			return true;
		}
		return false;
	}
	
	/**
	 * Run callbacks bound to a hook.
	 *
	 * This runs all the callbacks bound to a specified hook with the giver arguments. If you want the hook to be able to modify one of the arguments, pass it as 
	 * a reference using the ampersand: <code>Hooks::execute('hook', array(&$var));</code>
	 *
	 * @param string $hook The hook name bound to all of the callbacks for the hook.
	 * @param array Optional. This will call the callbacks with the specified arguments.
	 * @return bool True if all the callbacks were successfully run, false otherwise.
	 */
	public static function execute ($hook, $arguments = array()) {
		if(!self::hookExists($hook)) {
			self::register($hook);
		}

		$arguments = (array) $arguments;

		$callbacks = self::hookCallbacks($hook);
		if(empty($callbacks)) {
			return true;
		}

		ksort($callbacks);

		$r = true;
		foreach($callbacks as $callback_group) {
			foreach($callback_group as $callback) {
				if(empty($arguments)) {
					if(call_user_func($callback) === false) {
						$r = false;
					}
				}
				else {
					if(call_user_func_array($callback, $arguments) === false) {
						$r = false;
					}
				}
			}
		}
		return $r;
	}
	
	/**
	 * Bind a callback to a hook.
	 *
	 * @param string $hook The name of the hook.
	 * @param callback $callback A valid callback.
	 * @param int Optional. The priority of the callback. Order is smallest to largest number. Default: 0
	 * @return bool True if the hook exists and the callback is valid and added, false if not.
	 */
	public static function bind($hook, $callback, $priority = 0) {
		if(!is_callable($callback)) return false;
		if(!self::hookExists($hook)) {
			self::register($hook);
		}
		
		if(!array_key_exists($hook, self::$registered)) {
			self::$registered[$hook] = array();
		}
		
		self::$registered[$hook][$priority][] = $callback;
		return true;
	}

	/**
	 * Get all of the callbacks bound to $hook.
	 *
	 * @param string $hook The name of the hook to test.
	 * @return array All of the callbacks bound to $hook.
	 */
	public static function hookCallbacks($hook) {
		if(array_key_exists($hook, self::$registered)) {
			return self::$registered[$hook];
		}
		else {
			return array();
		}
	}
	
	/**
	 * Whether or not a hook already exists.
	 *
	 * @param string $hook The hook to check
	 * @return bool True if it exists, false if it doesn't;
	 */
	protected static function hookExists ($hook) {
		return (array_search($hook, self::getAllHooks()) === false ? false : true);
	}

	/**
	 * Get the array of registered hooks.
	 *
	 * @return array The array of registered hooks.
	 */
	protected static function getAllHooks () {
		return self::$hooks;
	}

	/**
	 * Sets up hooks for form submission.
	 */
	public static function setupPostHandles () {
		if(!empty($_POST['cc_form'])) {
			Hooks::execute('post_'.$_POST['cc_form']);
		}
	}
}
Hooks::bind('system_complete', 'Hooks::setupPostHandles');

/**
 * Wrapper for Hooks::execute().
 */
function plugin ($name, $args = array()) {
	return Hooks::execute($name, $args);
}

/**
 * Fundamentally the same as Hooks, but modifies the variable each time and returns the new value.
 */
class Filters extends Hooks {
	/**
	 * @var array The list of registered filters.
	 */
	private static $hooks = array();

	/**
	 * @var array An associative array of filter names and an array of callbacks.
	 */
	private static $registered = array();

	/**
	 * Register a valid filter.
	 *
	 * This registers $hook as a valid filter.
	 *
	 * @param string $hook A string to be a valid filter.
	 * @return bool True if hook is previously unused. False if the hook has been set previously.
	 */
	public static function register ($hook) {
		if(self::hookExists($hook)) {
			$hooks = &self::getAllHooks();
			$hooks[] = $hook;
			return true;
		}
		return false;
	}


	/**
	 * Run callbacks bound to a filter.
	 *
	 * This runs all the callbacks bound to a specified filter with $value as the argument. The callbacks overwrite the variable and return it.
	 *
	 * @param string $hook The hook name bound to all of the callbacks for the hook.
	 * @param mixed The value for the callbacks to mutate.
	 * @return bool True if all the callbacks were successfully run, false otherwise.
	 */
	public static function execute ($hook, $value = "") {
		if(!self::hookExists($hook)) {
			self::register($hook);
		}

		$callbacks = self::hookCallbacks($hook);
		if(empty($callbacks)) {
			return $value;
		}

		ksort($callbacks);
		
		foreach($callbacks as $callback_group) {
			foreach($callback_group as $callback) {
				$value = call_user_func_array($callback, array($value));
			}
		}
		return $value;
	}

	/**
	 * Bind a callback to a filter.
	 *
	 * @param string $hook The name of the filter.
	 * @param callback $callback A valid callback.
	 * @param int Optional. The priority of the callback. Order is smallest to largest number. Default: 0
	 * @return mixed The new value.
	 */
	public static function bind($hook, $callback, $priority = 0) {
		if(!is_callable($callback)) return false;
		if(!self::hookExists($hook)) {
			self::register($hook);
		}

		if(!array_key_exists($hook, self::$registered)) {
			self::$registered[$hook] = array();
		}

		self::$registered[$hook][$priority][] = $callback;
		return true;
	}

	/**
	 * Get all of the callbacks bound to $hook.
	 *
	 * @param string $hook The name of the filter to test.
	 * @return array All of the callbacks bound to $hook.
	 */
	public static function hookCallbacks($hook) {
		if(array_key_exists($hook, self::$registered)) {
			return self::$registered[$hook];
		}
		else {
			return array();
		}
	}

	/**
	 * Whether or not a filter already exists.
	 *
	 * @param string $hook The filter to check
	 * @return bool True if it exists, false if it doesn't;
	 */
	protected static function hookExists ($hook) {
		return (array_search($hook, self::getAllHooks()) === false ? false : true);
	}

	/**
	 * Get the array of registered filters.
	 *
	 * @return array The array of registered filters.
	 */
	protected static function getAllHooks () {
		return self::$hooks;
	}
}

/**
 * Wrapper for Filters::execute();
 */
function filter ($name, $orig_val) {
	return Filters::execute($name, $orig_val);
}
?>