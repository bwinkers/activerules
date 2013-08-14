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
	 * Route a Request object
	 */
	public function route_request($request);
	
	/**
	 * Convert a translated URL into a code Route
	 */
	public function reverse_route($route, $context);
	
	/**
	 * Take a code Route and return the translated route.
	 */
	public function translate_route($route, $context);
	
}
	
	