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

spl_autoload_register(function ($classname) {
    $classname = ltrim($classname, "\\");
    preg_match('/^(.+)?([^\\\\]+)$/U', $classname, $match);
    $classname = str_replace("\\", "/", $match[1])
		. str_replace(array("\\", "_"), "/", $match[2])
        . ".php";
    include_once $classname;
});

/**
 * Define DOCROOT as the full path to the docroot
 */
define('DOCROOT', realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR);

/**
 * Include the Core path first so its libraries take precedence.
 */
set_include_path(get_include_path() . PATH_SEPARATOR . '../bootstrap');

/**
 * Include the vendor path so we can consume PSR compliant libraries
 */
set_include_path(get_include_path() . PATH_SEPARATOR . '../vendor');

use ActiveRules\Core;
use ActiveRules\Core\Site;

$site = Site::singleton()->initialize();

var_export($site);

echo Site::version();



//echo EXT;