<?php
/**
 * This is the ActiveRules index.php file.
 * This is the Front Controller.
 * The web server environment needs to route all request to this file
 * 
 * @package ActiveRules
 * @subpackage Bootstrap
 */

/**
 * Define the ActiveRules version.
 * Modules may use this to determien if they are properly supported.
 * Files look for it as an indication the request was accessed through the index.php file
 */
define('AR_VERSION', '7.1');

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
 * Define DOCROOT as the full path to the docroot
 */
define('DOCROOT', realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR);

/**
 * Include the bootstrap path first so its libraries take precedence.
 */
set_include_path(get_include_path() . PATH_SEPARATOR . '../bootstrap');

/**
 * Include the vendor path so we can consume PSR compliant libraries
 */
set_include_path(get_include_path() . PATH_SEPARATOR . '../vendor');



require_once('ActiveRules/Bootstrap/test'.EXT);

//echo EXT;