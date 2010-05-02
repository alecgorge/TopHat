<?php
// configuration file for CanyonCMS

/**
 * The database to use. Should be a PDO Compatible DSN: {@link:http://php.net/manual/en/pdo.construct.php}. Default: 'sqlite://content/canyoncms.sqlite'
 */
$database = 'sqlite:content/canyoncms.sqlite';

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
?>