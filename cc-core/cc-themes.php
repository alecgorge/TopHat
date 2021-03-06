<?php

/**
 * @todo doc themes
 */

class Themes {
	private static $themes = array();
	public static $set_theme;
	public static $curr_theme;

	public static function bootstrap () {
		self::$set_theme = Settings::get('site', 'theme', true);
		self::setCurrentTheme(self::$set_theme);
	}

	private static function checkThemes () {
		$g = glob(TH_ROOT.TH_THEMES.'*/theme.json');
		foreach($g as $v) {
		    $parts = explode('/', str_replace('\\', '/', $v));
			$ini = (array)json_decode(file_get_contents($v));
		    $themes[$parts[count($parts)-2]] = $ini;
		}
		self::$themes = $themes;
	}

	/**
	 *
	 * @return Theme Returns an instance of Theme containing the current theme.
	 */
	public static function getCurrentTheme () {
		return self::$curr_theme;
	}

	public static function setCurrentTheme ($x) {
		self::$set_theme = $x;

		$theme = new Theme(self::$set_theme);

		if($theme->validate()) {
			self::$curr_theme = $theme;
		}
	}

	public static function getThemeList () {
		if(empty(self::$themes)) {
			self::checkThemes();
		}
	    return self::$themes;
	}
}

class Theme {
	private $absolute_path;

	private $public_path;

	private $has_config;

	private $config;

	private $config_path;

	private $folder;

	private $name;

	public function __construct ($name) {
		$this->folder = $name;

		if($this->validate()) {
			$this->config_path = '/'.ltrim(TH_ROOT.TH_THEMES.$this->getFolderName().'/theme.json', '/');
			$this->absolute_path = '/'.ltrim(TH_ROOT.TH_THEMES.$this->getFolderName().'/', '/');
			$this->public_path = '/'.ltrim(TH_PUB_ROOT.TH_THEMES.$this->getFolderName().'/', '/');
			if($this->hasConfig()) {
				$this->getConfig();
			}
			else {
				$this->config = array(
					'name' => $name,
					'version' => 'unknown',
					'author' => 'unknown',
					'description' => 'none',
					'interface' => false,
					'interfaceName' => false
				);
				$this->name = $name;
			}
		}
	}

	public function validate () {
		if(is_dir(TH_ROOT.TH_THEMES.$this->getFolderName().'/') && file_exists(TH_ROOT.TH_THEMES.$this->getFolderName().'/index.tpl.php')) {
			return true;
		}
		else {
			return false;
		}
	}

	public function hasConfig () {
		$this->has_config = file_exists($this->getConfigPath());
		return $this->has_config;
	}
	
	public function getConfigPath() {
		return $this->config_path;
	}

	public function getConfig () {
		if(empty($this->config)) {
			$this->config = (array)json_decode(file_get_contents($this->getConfigPath()));
			foreach($this->config as $k => $v) {
				$this->config[$k] = $v;
			}
			
			if(!empty($this->config['interface'])) {
				if(!file_exists($this->getAbsolutePath().$this->config['interface'])) {
					die("Interface for ".$this->config['name']." doesn't exist (".$this->getAbsolutePath().$this->config['interface'].")");
				}
				else {
					$this->config['interface'] = $this->getAbsolutePath().$this->config['interface'];
				}
			}

			$this->name = $this->config['name'];
		}
		return $this->config;
	}

	public function hasInterfaceName () {
		$config = $this->getConfig();
		return !empty($config['interfaceName']);
	}

	public function getInterfaceName () {
		$config = $this->getConfig();
		return $config['interfaceName'];
	}

	public function getVersion () {
		$config = $this->getConfig();
		return $config['version'];
	}

	public function getAuthor () {
		$config = $this->getConfig();
		return $config['author'];
	}

	public function getFolderName () {
		return $this->folder;
	}

	public function getAbsolutePath () {
		return $this->absolute_path;
	}

	public function getName () {
		return $this->config['name'];
	}

	public function getDescription () {
		return $this->config['description'];
	}

	public function hasInterface () {
		return (bool) $this->config['interface'];
	}

	public function getInterface () {
		return $this->config['interface'];
	}

	public function getPublicPath () {
		return $this->public_path;
	}
}

Themes::bootstrap();

/**
 * Returns the title of the current page.
 * 
 * @package Theme
 * @return string The title. 
 */
function title() {
	return Content::getTitle();
}

/**
 * Returns the contents of the current page.
 *
 * @package Theme
 * @return string The content.
 */
function content() {
	return Content::get();
}

/**
 * Returns a breadcrumb system.
 *
 * @param string $sep The separator between the items.
 * @param bool $rev Reverse the order of the breadcrumbs?
 * @return string The breadcrumbs.
 */
function breadcrumbs ($sep = ' &lsquo; ', $rev = false) {
	$b = Content::getBreadcrumbs();

	if(!$rev) {
		$b = array_reverse($b, true);
	}

	return filter('content_breadcrumbs', UTF8::htmlentities(implode($sep, $b)));
}

/**
 * This function returns HTML from the site's navigation.
 *
 * @param array $options The options for the nav generation. It is an associative array. Any items not named will use the default: <code>array('root' => "\n<ul id='ul-navigation'>\n%s\n</ul>\n",
 *	'child' => "\n<ul class='ul-sub-navigation'>\n%s\n</ul>\n",
 *	'item' => "\n\t<li><a href='%2\$s' title='%1\$s'>%1\$s</a></li>",
 *	'itemSelected' => "\n\t<li class='selected'><a href='%2\$s' title='%1\$s'>%1\$s</a></li>",
 *	'itemHasChild' => "\n\t<li><a href='%2\$s' title='%1\$s'>%1\$s</a>%3\$s</li>",
 *	'itemHasChildSelected' => "\n\t<li class='selected'><a href='%2\$s' title='%1\$s'>%1\$s</a>%3\$s</li>");</code>
 * @return string The navigation in HTML form.
 */
function nav ($options = array()) {
	return Content::generateNavHTML($options);
}

function theme_dir () {
	return Themes::$curr_theme->getPublicPath();
}
?>
