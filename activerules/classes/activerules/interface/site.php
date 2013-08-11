<?php defined('AR_VERSION') or die('No direct script access.');
/**
 * ActiveRules Site interface
 * This defines the core functionality the Site class needs to provide
 *
 * @package    ActiveRules
 * @author     Brian Winkers
 * @copyright  (c) 2005-2013 Brian Winkers
 */
interface Activerules_Interface_Site { 
		
	/**
	 * The site interface must provide a factory interface
	 */
	public static function factory();

	/**
	 * Initialize the site.
	 * Load the site data.
	 */
	public function init_site();
	
}
	
	