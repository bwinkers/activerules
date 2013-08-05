<?php
/**
 * Copyright 2013 - Brian Winkers
 */

/**
 * Define the start time of the application, used for profiling.
 */
if ( ! defined('AR_START_TIME'))
{
	define('AR_START_TIME', microtime(TRUE));
}

/**
 * Define the memory usage at the start of the application, used for profiling.
 */
if ( ! defined('AR_START_MEMORY'))
{
	define('AR_START_MEMORY', memory_get_usage());
}

/**
 * Define PHP extension
 */
if ( ! defined('EXT'))
{
	define('EXT', '.php');
}

/**
 * Define the ActiveRules version.
 * Modules may use this to determien if they are properly supported.
 * Files look for it as an indication the request was accessed through the index.php file
 */
define('AR_VERSION', '7.0');

/**
 * Define DOCROOT as the full path to the docroot
 */
define('DOCROOT', realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR);

/**
 * Include the installation specific config file.
 * This configures where certain directories are.
 * This should be set once in coordination with a sysadmin.
 */
require_once(DOCROOT.'config'.DIRECTORY_SEPARATOR.'bootstrap_config'.EXT);

/**
 * Define ACTIVEPATH
 * This is the path where the activerules module is located and is defined in bootstrap_config.php
 */
define('ACTIVEPATH', realpath($activerules).DIRECTORY_SEPARATOR);

/**
 * Pass processing over to the bootstrap file
 */
// require_once(ACTIVEPATH.'bootstrap'.EXT);

/**
 * Include the file that defines the AR(ActiveRules) class
 */
require_once(ACTIVEPATH.'classes'.DIRECTORY_SEPARATOR.'activerules'.DIRECTORY_SEPARATOR.'ar'.EXT);

/**
 * Add the Activerules autoloader
 */
spl_autoload_register(array('Activerules_AR', 'autoload'));

/**
 * Define config array based on the bootstrap configs
 */
$ar_bootstrap_configs = array(
	'config_storage'=>$activerules_storage,
	'cacher'=>$activerules_cacher,
	'logger'=>$activerules_logger
);

/**
 * Create a new AR class instance
 */
$ar = new AR($ar_bootstrap_configs);

/**
 * Load the site.
 * The site will handle the request after that.
 */
$ar->load_site();

echo '<br>Finished in index.php'

?>
