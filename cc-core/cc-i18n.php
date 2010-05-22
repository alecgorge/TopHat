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
	private $locale = 'en_US';

	/**
	 * @var The handle to this class for the static methods.
	 */
	private static $handle = null;

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
	 */
	public function translateFromKey ($section, $key, $locale = 'DEFAULT') {
		if($locale === 'DEFAULT') {
			$locale = $this->getLocale();
		}

		if(!array_key_exists($key, (array)$this->translations[$locale][$section])) {
			trigger_error("'$key' doesn't exist in the translation for '$locale' in the section '$section'.");
			return null;
		}

		return $this->translations[$locale][$section][$key];
	}

	/**
	 * Return a translated string from the key and section.
	 *
	 * @param string $section The section the key is in. Default should be 'core'.
	 * @param string $key The translation key.
	 */
	public static function translate ($section, $key, $locale = 'DEFAULT') {
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
}
Hooks::bind('system_ready', 'i18n::boostrap');



/**
 * @todo sprintf support
 * A wrapper for i18n::translate();
 */
function __($section, $key, $locale = 'DEFAULT') {
	return i18n::translate($section, $key, $locale);
}

/**
 * Like __() but echos the output.
 */
function _e($section, $key, $locale = 'DEFAULT') {
	echo __($section,$key,$locale);
}

?>
