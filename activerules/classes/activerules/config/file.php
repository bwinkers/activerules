<?php
/**
 * Activerules Config File
 * 
 * Pull configuration from a file.
 * There is no paradigm supported for writing config files.
 * You should assume the server has no writeable local storage.
 *
 * @package    ActiveRules
 * @author     Brian Winkers
 * @copyright  (c) 2005-2013 Brian Winkers
 */
class Activerules_Config_File {
	
	private static $_site_name;
	
	
	private static $_root;
	
	/**
	 * Load the site
	 */
	public function __construct($param_array)
	{
		// All that is required for file based config is a matching file
		if(isset($param_array['filepath']))
		{
			self::$_root = $param_array['filepath'];
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
		$configs = FALSE;

		// Check for a config file
		$config_files = AR::find_file('config', $group);
		
		if($config_files)
		{
			$configs = array();
			
			foreach($config_files as $config_file)
			{
				include($config_file);
				$configs = array_merge($config, $configs);
			}
		}
		
		return $configs;
	}
	
	/**
	 * Load a config group.
	 * For file based configs a group maps to a file name within the root.
	 * the file name may include config subdirectories.
	 * 
	 * @param string $group 
	 */
	public function load_groups($groups)
	{
		$configs = FALSE;

		// Check for a config file
		$config_files = AR::list_files(self::$_root.'config'.DIRECTORY_SEPARATOR.$groups);
	
		if($config_files)
		{
			$configs = array();
			
			foreach($config_files as $config_file)
			{
				// Include a file with a $config array defined.
				include($config_file);
				
				// Get the base filename to use for namespacing the config
				$filename = basename($config_file, ".php");
				
				// Add the config to the array
				$configs[$filename] = $config;
			}
		}
		
		return $configs;
	}
} 
