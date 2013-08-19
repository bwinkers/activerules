<?php defined('AR_VERSION') or die('No direct script access.');
/**
 * ActiveRules Context interface
 * This defines the core functionality the readable Context class needs to provide.
 * This interfaces does NOT support updating the Context object or telling it anything.
 *
 * @package    ActiveRules
 * @author     Brian Winkers
 * @copyright  (c) 2005-2013 Brian Winkers
 */
interface Activerules_Interface_Context { 
		
	
	public function set_context($context);
	
	
	public function get_context();
		
}
	
	