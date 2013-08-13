<?php defined('AR_VERSION') or die('No direct script access.');
/**
 * ActiveRules Router interface
 * This defines the core functionality the readable Router class needs to provide.
 * This interfaces does NOT support updating the Router object or telling it anything.
 *
 * @package    ActiveRules
 * @author     Brian Winkers
 * @copyright  (c) 2005-2013 Brian Winkers
 */
interface Activerules_Interface_Router { 
		
	/**
	 * The Router interface must provide a factory method.
	 * The storage object would be something like 
	 */
	public static function factory($storage=NULL);
	
	/**
	 * The Router interface must provide a factory interface
	 */
	public function Router_alias();

	/**
	 * Return a config variable for a Router.
	 * This should take a dot notated array path and return the configured value.
	 * 
	 * ANY module can access this through the static AR::Router method.
	 */
	public function config($dot_path, $default=FALSE);
	
}
	
	