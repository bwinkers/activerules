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
	 * Set a dot path variable for the reesponse.
	 */
	public function set_data($dot_path, $value);
	
}
