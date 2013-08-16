<?php
/**
 * Process library.
 *
 * @package    ActiveRules
 * @author     Brian Winkers
 * @copyright  (c) 2005-2013 Brian Winkers
 */
class Activerules_Process implements Interface_Process {
	
	/**
	 * Process a Request object
	 * The processing will be responsible for updating the View and Response objects appropriately
	 */
	public function process_request($request)
	{
		
	}
	
	/**
	 * Convert a translated URL into a code Route
	 */
	public function reverse_route($route, $context)
	{
		
	}
	
	/**
	 * Take a code Route and return the translated route.
	 */
	public function translate_route($route, $context)
	{
		
	}	
}