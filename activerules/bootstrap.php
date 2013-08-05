<?php  defined('AR_VERSION') or die('No direct script access.');

/**
 * Include the file that defines the AR(ActiveRules) class
 */
require_once(ACTIVEPATH.'classes'.DIRECTORY_SEPARATOR.'activerules'.DIRECTORY_SEPARATOR.'ar'.EXT);

/**
 * Add the Activerules autoloader
 */
spl_autoload_register(array('Activerules_AR', 'autoload'));

/**
 * Create a new AR class instance
 */
$ar = new AR;

/**
 * Load the site configs from storage or cache
 */
$ar->load_site();

/**
 * Process the request
 */
$ar->process();