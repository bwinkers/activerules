<?php defined('AR_VERSION') or die('No direct script access.');
/**
 * ActiveRules Response interface
 * This defines the core functionality the readable Response class needs to provide.
 * This interfaces does NOT support updating the Response object or telling it anything.
 *
 * @package    ActiveRules
 * @author     Brian Winkers
 * @copyright  (c) 2005-2013 Brian Winkers
 */
interface Activerules_Interface_Response { 
		
	/**
	 * The Response interface must provide a factory method.
	 * The storage object would be something like 
	 */
	public static function factory($storage=NULL);
	
	/**
	 * The Response interface must provide a factory interface
	 */
	public function Response_alias();

	/**
	 * Return a config variable for a Response.
	 * This should take a dot notated array path and return the configured value.
	 * 
	 * ANY module can access this through the static AR::Response method.
	 */
	public function config($dot_path, $default=FALSE);
	
}
	
	