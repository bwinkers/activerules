<?php defined('AR_VERSION') or die('No direct script access.');
/**
 * AR (ActiveRules) Config library.
 *
 * @package    ActiveRules
 * @author     Brian Winkers
 * @copyright  (c) 2005-2013 Brian Winkers
 */
class Activerules_Config {
	
	public $config;
	
	/**
	 * Construct the object
	 * 
	 * @param string $type
	 * @param array $param 
	 */
	public function __construct($driver, $param_array)
	{
		try 
		{
			// Append the driver to root class to create final class name
			$driver_class = 'Config_'.ucfirst($driver);
			
			// Create the config object
			$this->config = new $driver_class($param_array);
			
		} 
		catch(Exception $e)
		{
			throw new Activerules_Exception( 'Ooops...', 0, $e);
		}
	}
	
	/**
	 * Load a config group.
	 * For file based configs a group maps to a file name within the root.
	 * the file name may include config subdirectories.
	 * 
	 * @param string $group 
	 */
	public function load_group($group)
	{
		return $this->config->load_group($group);
	}
	
	/**
	 * Load all configs from a root.
	 * For file based configs a group maps to a file name within the root.
	 * the file name may include config subdirectories.
	 * 
	 * @param string $group 
	 */
	public function load_groups($group)
	{
		return $this->config->load_groups($group);
	}

	
} // End AR(ActiveRules) Config Class
