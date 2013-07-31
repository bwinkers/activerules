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
 * Define the ActiveRules version.
 * Modules may use this to determien if they are properly supported.
 * Files look for it as an indication the request was accessed through the index.php file
 *  * http://docs.activerules.com/term/ar_version
 */
define('AR_VERSION', '7.0');

/**
 * Define DOCROOT as the full path to the docroot
 * http://docs.activerules.com/term/docroot
 */
define('DOCROOT', realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR);

/**
 * Include the installation specific config file.
 * This configures where certain directories are.
 * http://docs.activerules.com/term/bootstrap_config.php
 */
include(DOCROOT.'bootstrap_config.php');

/**
 * Define ACTIVEPATH
 * This is the path where modules come from and is defined in bootstrap_config.php
 * http://docs.activerules.com/term/activepath
 */
define('ACTIVEPATH', realpath($activerules_modules).DIRECTORY_SEPARATOR);

/**
 * Load the Site class
 * http://docs.activerules.com/term/site
 */
include(ACTIVEPATH.'objects/site.php');

?>
