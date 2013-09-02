<?php
use ActiveRules\Core\Site;
use ActiveRules\Core\Debug;

/**
 * This is the ActiveRules index.php file.
 * This is the Front Controller.
 * The web server environment needs to route all request to this file
 * 
 * @package ActiveRules
 * @subpackage	Core
 * @author Brian Winkers
 * @copyright (c) 2005-2013 Brian Winkers
 * 
 */

/**
 * Define the ActiveRules version.
 * Modules may use this to determien if they are properly supported.
 * Files look for it as an indication the request was accessed through the index.php file
 */
if ( ! defined('AR_VERSION'))
{
    define('AR_VERSION', '7.1');
}

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
 * PSR0 Autoloader
 *
 * @package ActiveRules
 * @subpackage	Core
 * @author Brian Winkers
 * @copyright (c) 2005-2013 Brian Winkers
 */
spl_autoload_register(function ($classname) {
    $classname = ltrim($classname, "\\");
    preg_match('/^(.+)?([^\\\\]+)$/U', $classname, $match);
    $classname = str_replace("\\", "/", $match[1])
        . str_replace(array("\\", "_"), "/", $match[2])
        . EXT;
    include_once $classname;
});

/**
 * Add the Core path first so its libraries take precedence.
 */
set_include_path(get_include_path() . PATH_SEPARATOR . '../bootstrap');

/**
 * Include the vendor path so we can consume PSR compliant libraries
 */
set_include_path(get_include_path() . PATH_SEPARATOR . '../vendor');

/**
 * Define where the Site Host configs are 
 */

define('SITE_CONFIG_DIR', realpath('../config'));

/**
 * Create the static Site singleton representing the site of the original web request.
 */
$site = Site::singleton()->initialize();

Debug::it(Site::VERSION, 'Site version');

//echo EXT;