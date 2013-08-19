<?php defined('AR_VERSION') or die('No direct script access.');
/**
 * ActiveRules Process interface
 * This defines the core functionality the readable Process class needs to provide.
 * This interfaces does NOT support updating the Process object or telling it anything.
 *
 * @package    ActiveRules
 * @author     Brian Winkers
 * @copyright  (c) 2005-2013 Brian Winkers
 */
interface Activerules_Interface_Process { 
		
	
	public function render();
	
	
	public function set($dot_path, $data);
		
}
	
	