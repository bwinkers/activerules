<?php defined('AR_VERSION') or die('No direct script access.');
/**
 * ActiveRules Site interface
 * This defines the core functionality the readable Site class needs to provide.
 * This interfaces does NOT support updating the Site object or telling it anything.
 *
 * @package    ActiveRules
 * @author     Brian Winkers
 * @copyright  (c) 2005-2013 Brian Winkers
 */
interface Activerules_Interface_Site { 
		
	/**
	 * The site interface must provide a factory method.
	 * The storage object would be something like 
	 */
	public static function factory($storage=NULL);
	
	/**
	 * The site interface must provide a factory interface
	 */
	public function site_alias();

	/**
	 * Return a config variable for a site.
	 * This should take a dot notated array path and return the configured value.
	 * 
	 * ANY module can access this through the static AR::site method.
	 */
	public function config($dot_path, $default=FALSE);
	
	/**
	 * Takeover processing the request.
	 * ActiveRules shouldn't have sent any headers or output yet
	 */
	public function takeover_request();
	
}
	
	