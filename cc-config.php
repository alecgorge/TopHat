<?php
// configuration file for CanyonCMS

/**
 * The database to use. Should be a PDO Compatible DSN: {@link:http://php.net/manual/en/pdo.construct.php}. Default: 'sqlite:'.dirname(__FILE__).'/content/canyoncms.sqlite'
 */
$database = 'sqlite:'.dirname(__FILE__).'/content/canyoncms.sqlite';

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
 * This is the root directory of the CanyonCMS installation. Contains trailing slash.
 */
define('CC_ROOT', dirname(__FILE__).'/');

/**
 * This is the root PUBLIC directory of the CanyonCMS installation. Contains trailing slash.
 */
function cc_find_cc_pub_root () {
	global $admin_path;
	
	$path = dirname($_SERVER['PHP_SELF']).'/';
	if(substr($path, '-'.strlen($admin_path)) == $admin_path) {
		return preg_replace('/[^\/]+\/\.\.\//', '', $path.'../');
	}
	return $path;
}
define('CC_PUB_ROOT', cc_find_cc_pub_root());

/**
 * This is the public path to the current file
 */
define('CC_PUB', rtrim($_SERVER['REQUEST_URI'], '/').'/');

/**
 * The location of required CanyonCMS files like the bootstrapper. Should contain trailing slash. Default: CC_ROOT.'core/'
 */
define('CC_CORE', CC_ROOT.'cc-core/');

/**
 * The public location of the admin panel. If this is changed then the url needed to access the admin panel changes. Should contain trailing slash. Default: CC_PUB_ROOT.'admin/'
 */
define('CC_PUB_ADMIN', CC_PUB_ROOT.$admin_path);

/**
 * The location of the admin panel relative to CC_ROOT. If this is changed then the url needed to access the admin panel changes. Should contain trailing slash. Default: CC_ROOT.'admin/'.
 */
define('CC_ADMIN', CC_ROOT.$admin_path);
/**
 * The location of the folder that contains the uploads and themes directory. Should contain trailing slash. Default: CC_ROOT.'content/'
 */
define('CC_CONTENT', 'content/');

/**
 * The location of the folder that contains the site's themes. Needs to be a subfolder of CC_CONTENT. Should contain trailing slash. <code>Default: CC_CONTENT.'themes/'</code>
 */
define('CC_THEMES', CC_CONTENT.'themes/');

/**
 * The location of the folder that contains the site's uploads. Needs to be a subfolder of CC_CONTENT. Should contain trailing slash. Default: <code>CC_CONTENT.'uploads/'</code>
 */
define('CC_UPLOADS', CC_CONTENT.'uploads/');

/**
 * The location of the folder that contains the site's plugins. Should contain trailing slash. Default: <code>'plugins/'</code>
 */
define('CC_PLUGINS', 'plugins/');

/**
 * The location of the folder that contains the site's translations. Should contain trailing slash. Default: <code>CC_CONTENT.'translations/'</code>
 */
define('CC_TRANSLATIONS', CC_CONTENT.'translations/');
?>