<?php
/**
 * Copyright 2013 - Brian Winkers
 */

/**
 * Start output buffering
 * This is done here to catch output at this level.
 */
//ob_start();

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
 * Include the file that defines the AR(ActiveRules) class
 */
require_once(ACTIVEPATH.'classes'.DIRECTORY_SEPARATOR.'activerules'.DIRECTORY_SEPARATOR.'ar'.EXT);

/**
 * Add the Activerules autoloader
 */
spl_autoload_register(array('Activerules_AR', 'autoload'));

// Enable ActiveRules exception handling, adds stack traces and error source.
//set_exception_handler(array('Activerules_Exception', 'handler'));

// Enable ActiveRulesa error handling, converts all PHP errors to exceptions.
//set_error_handler(array('Activerules_AR', 'error_handler'));

// Enable the ActiveRules shutdown handler, which catches E_FATAL errors.
//register_shutdown_function(array('Activerules_AR', 'shutdown_handler'));

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
 * 
 *	1. Load the Site class.
 *     This class provides site specfifc base level configuration of modules and services
 * 
 *  2. Load the Request class. 
 *     This should encapsulate all of the global variables
 *     The class loaded to handle this will be controlled by the order the modules are loaded.
 *     If no Request class is found in a module the Activerules Request class will be used.
 * 
 *  3. Route the Request. 
 *     The Route class will determine how to route the request.
 *     The class loaded to handle this will be controlled by the order the modules are loaded.
 *     If no Route class is found in a module the Activerules Route class will be used.
 * 
 *  4. Service the Request
 *     This class will service the Request and provide a Response
 * 
 *  5. Send the Response
 *     This sends the response to the client.
 *     This could respond with mock data in certain circumstances.
 * 
 * Ideally it should send the whole response
 * But it needs to provide core services to various sub levels
 *     
 */
	$ar = AR::instance()
		// Configure the ActiveRules
		->configure($ar_bootstrap_configs)
		// Load the Site
		->load_site()
		// have the Site process the Request	
		->process_request();
/**
 * Flush the bufer to get rid of any screen output at this level
 */
//ob_end_clean();

?>
