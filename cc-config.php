<?php
// configuration file for TopHat

/**
 * The database to use. Should be a PDO Compatible DSN: {@link:http://php.net/manual/en/pdo.construct.php}. Default: 'sqlite:'.dirname(__FILE__).'/content/TopHat.sqlite'
 */
$database = 'sqlite:'.dirname(__FILE__).'/content/TopHat.sqlite';

/**
 * Database username (if applicable). Default: null
 */
$db_username = null;

/**
 * Database password (if applicable). Default: null
 */
$db_password = null;

/**
 * Database prefix for the tables. Default: 'cc_'
 */
$db_prefix = 'cc_';

/**
 * The server's timezone. Should be one listed on this page: {@link:http://us2.php.net/manual/en/timezones.php}. Default: 'America/New_York'
 */
$timezone = "America/New_York";


$admin_path = "cc-admin/";

/* The definitions for directories. All paths are relative to this (index.php) file and CONTAIN THE TRAILING FORWARD SLASH */

/**
 * This is the root directory of the TopHat installation. Contains trailing slash.
 */
define('TH_ROOT', dirname(__FILE__).'/');

/**
 * This is the public path to the current file
 */
define('TH_PUB', rtrim($_SERVER['REQUEST_URI'], '/').'/');

/**
 * The location of required TopHat files like the bootstrapper. Should contain trailing slash. Default: TH_ROOT.'core/'
 */
define('TH_CORE', TH_ROOT.'cc-core/');

/**
 * This is the root PUBLIC directory of the TopHat installation. Contains trailing slash.
 */
function cc_find_cc_pub_root () {
	global $admin_path;
	
	$path = dirname($_SERVER['PHP_SELF']).'/';
	if(substr($path, '-'.strlen($admin_path)) == $admin_path) {
		$path = preg_replace('/[^\/]+\/\.\.\//', '', $path.'../');
	}
	$r = rtrim(str_replace(TH_ROOT, '', $_SERVER['DOCUMENT_ROOT']), '/');
	while(!file_exists($r.$path.'cc-config.php')) {
		$path = preg_replace('/[^\/]+\/\.\.\//', '', $path.'../');
	}
	return $path;
}
define('TH_PUB_ROOT', rtrim(cc_find_cc_pub_root(), '/').'/');

/**
 * The public location of the admin panel. If this is changed then the url needed to access the admin panel changes. Should contain trailing slash. Default: TH_PUB_ROOT.'admin/'
 */
define('TH_PUB_ADMIN', rtrim(TH_PUB_ROOT.$admin_path, '/').'/');

/**
 * The location of the admin panel relative to TH_ROOT. If this is changed then the url needed to access the admin panel changes. Should contain trailing slash. Default: TH_ROOT.'admin/'.
 */
define('TH_ADMIN', rtrim(TH_ROOT.$admin_path, '/').'/');
/**
 * The location of the folder that contains the uploads and themes directory. Should contain trailing slash. Default: TH_ROOT.'content/'
 */
define('TH_CONTENT', 'content/');

/**
 * The location of the folder that contains the site's themes. Needs to be a subfolder of TH_CONTENT. Should contain trailing slash. <code>Default: TH_CONTENT.'themes/'</code>
 */
define('TH_THEMES', TH_CONTENT.'themes/');

/**
 * The location of the folder that contains the site's uploads. Needs to be a subfolder of TH_CONTENT. Should contain trailing slash. Default: <code>TH_CONTENT.'uploads/'</code>
 */
define('TH_UPLOADS', TH_CONTENT.'uploads/');

/**
 * The location of the folder that contains the site's plugins. Should contain trailing slash. Default: <code>'plugins/'</code>
 */
define('TH_PLUGINS', 'plugins/');

/**
 * The location of the folder that contains the site's translations. Should contain trailing slash. Default: <code>TH_CONTENT.'translations/'</code>
 */
define('TH_TRANSLATIONS', TH_CONTENT.'translations/');

date_default_timezone_set($timezone);

define("TH_DEBUG", false);

