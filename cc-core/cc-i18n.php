<?php

/**
 * A class that allows for different languages to be used.
 */
class i18n {
	/**
	 * @var Stores all of the available translations.
	 */
	private $translations = array();

	/**
	 * @var The current locale setting.
	 */
	public $locale = 'en_US';
	public $old_locale = null;

	/**
	 * @var The handle to this class for the static methods.
	 */
	private static $handle = null;

	/**
	 * @var string The section to make translate calls relative to.
	 */
	private static $rel = null;

	/**
	 * Constructor for i18n.
	 *
	 * This will take the locale and attempt to load a translation from CC_TRANSLATIONS with the same name. If none is found en_US is loaded.
	 *
	 * @param string $locale A valid locale.
	 */
	public function __construct ($locale) {
		putenv('LANG='.$locale);
		setlocale(LC_ALL,$locale.'.UTF8');

		$this->locale = $locale;
	}

	/**
	 * Gets the current locale.
	 */
	public function getLocale () {
		return $this->locale;
	}

	/**
	 * This will take the locale and attempt to load a translation from CC_TRANSLATIONS with the same name. If none is found en_US is loaded.
	 */
	private function loadValid () {
		$include = CC_ROOT.CC_TRANSLATIONS.$this->getLocale().'.php';
		if(!file_exists($include)) {
			$include = CC_ROOT.CC_TRANSLATIONS.'en_US.php';
		}
		require_once $include;
	}

	/**
	 * Registers a translation for use.
	 *
	 * @param string $locale The locale the translations are for (en_US, fr_CA, etc).
	 * @param string $section The category that the translation belongs. Default is 'core'. For plugins, use the plugin name.
	 * @param array $translations A key value array of translations.
	 */
	public function registerTranslation ($locale, $section, $translations) {
		if(!array_key_exists($locale, $this->translations)) {
			$this->translations[$locale] = array();
		}
		if(!array_key_exists($section, $this->translations[$locale])) {
			$this->translations[$locale][$section] = array();
		}
		$this->translations[$locale][$section] = $translations;
	}

	/**
	 * Return a translated string from the key and section.
	 *
	 * @param string $section The section the key is in. Default should be 'core'.
	 * @param string $key The translation key.
	 * @param string $local The locale to use when looking for translatations. Default is 'DEFAULT'.
	 * @param mixed ... Any more arguments passed will be passed to sprintf when translating.
	 * @return string The translated value.
	 */
	public function translateFromKey ($section, $key = null, $locale = 'DEFAULT') {
		plugin('translate', array($section, $key, $locale));
		if($locale === 'DEFAULT') {
			$locale = $this->getLocale();
		}

		if(is_null($key)) {
			$key = $section;
			$section = self::$rel;
		}

		//var_dump(func_get_args());
		if(!array_key_exists($key, (array)$this->translations[$locale][$section])) {
			trigger_error("'$key' doesn't exist in the translation for '$locale' in the section '$section'.");
			return null;
		}

		
		if(func_num_args() > 3 || (func_num_args() > 2 && $locale === 'DEFAULT')) {
			return filter('translated_value', call_user_func_array('sprintf', array_merge(array($this->translations[$locale][$section][$key]), array_slice(func_get_args(), 3))));
		}

		return filter('translated_value', $this->translations[$locale][$section][$key]);
	}

	/**
	 * Return a translated string from the key and section.
	 *
	 * @param string $section The section the key is in. Default should be 'core'.
	 * @param string $key The translation key.
	 */
	public static function translate ($section, $key = null, $locale = 'DEFAULT') {
		//var_dump(func_get_args(), $locale, func_num_args(), (func_num_args() > 2 && $locale !== 'DEFAULT'));
		if(func_num_args() > 3 || (func_num_args() > 2 && $locale !== 'DEFAULT')) {
			$args = func_get_args();
			$args = array_merge(array_slice($args, 0, 2, false), array('DEFAULT'), array_slice($args, 2));

			return call_user_func_array(array(self::$handle, 'translateFromKey'), $args);
		}

		return self::$handle->translateFromKey($section, $key, $locale = 'DEFAULT');
	}

	public static function boostrap () {
		$locale = Settings::get('core', 'locale');

		if(empty($locale)) {
			$locale = 'en_US';
		}

		self::$handle = new i18n($locale);
		self::$handle->loadValid();
	}

	/**
	 * Takes the handle and registers the plugin.
	 */
	public static function register ($locale, $section, $translations) {
		self::$handle->registerTranslation($locale, $section, $translations);
	}

	/**
	 * This is used to simply translating. You can use i18n::set($section) to make all calls to _e() and __() and i18n::translate() in the section $section instead of defining the setting. Don't forget to call i18n::restore() when you are done.
	 *
	 * @param string $section The section to set all _e and __ calls relative to.
	 * @param string $locale The locale to use.
	 * @return boolean True on success, false on failure.
	 */
	public static function set ($section, $locale = 'DEFAULT') {
		if($locale === 'DEFAULT') {
			$locale = self::$handle->getLocale();
		}
		if(!array_key_exists($section, (array)self::$handle->translations[$locale])) {
			trigger_error("'$section' doesn't exist in the translation for '$locale'.");
			return false;
		}
		self::$handle->old_locale = self::$handle->locale;
		self::$handle->locale = $locale;
		self::$rel = $section;
		return true;
	}

	/**
	 * Undoes what i18n::set() did.
	 */
	public static function restore () {
		self::$rel = null;
		self::$handle->locale = null;
		return true;
	}
}
Hooks::bind('system_ready', 'i18n::boostrap');



/**
 * A wrapper for i18n::translate();
 *
 * You can use it as if it has 2 arguments and then pass the rest of the arguments as arguments to go to sprintf.
 */
function __($section, $key, $locale = 'DEFAULT') {
	if(func_num_args() > 3 || (func_num_args() > 2 && $locale !== 'DEFAULT')) {
		$args = func_get_args();
		return call_user_func_array('i18n::translate', $args);
	}
	return i18n::translate($section, $key, $locale);
}

/**
 * Like __() but echos the output.
 */
function _e($section, $key, $locale = 'DEFAULT') {
	if(func_num_args() > 3 || (func_num_args() > 2 && $locale !== 'DEFAULT')) {
		$args = func_get_args();
		echo call_user_func_array('i18n::translate', $args);
		return;
	}
	echo __($section,$key,$locale);
}


?>
