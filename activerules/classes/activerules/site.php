<?php
/**
 * Site library.
 *
 * @package    ActiveRules
 * @author     Brian Winkers
 * @copyright  (c) 2005-2013 Brian Winkers
 */
class Activerules_Site {
	
	/**
	 * This is used for site specfic directories etc.
	 * @var string The site alias 
	 */
	private static $_site_alias;
	
	/**
	 * Information about the requested, configured and supported hostname.
	 * @var object Hostname object
	 */
	private static $_hostname;
	
	/**
	 * Location where site specific
	 * @var object Config object
	 */
	private static $_storage;
	
	/**
	 * Config populated from storage or cache
	 */
	private static $_config;
	

	
	/**
	 * Load the site
	 */
	public function __construct($site_alias=NULL)
	{
		if($site_alias)
		{
			self::$_site_alias = $site_alias;
		}
	}
	
	/**
	 * Determine the site host based on all available data
	 */
	public function determine_host()
	{
		$hostname = self::check_hostname();

		// Store the hostname data in the site
		self::$_hostname = $hostname;
	}
	
	/**
	 *
	 * @param type $site_alias
	 * @return type 
	 */
	public function init_site()
	{
		// Determine the hostname
		$this->determine_host();
		
		// get the site alias from the hostname
		$site_alias = self::$_hostname->get_site_alias();

		// This will return a hostname config array or FALSE
		$site_data = self::$_storage->load_groups('site'.DIRECTORY_SEPARATOR.$site_alias);

		if($site_data)
		{
			// merge host data and site data
			$merged_configs = array_merge_recursive(self::$_hostname->get_host_data(), $site_data);
			
			self::$_config = $merged_configs;
		}
		
		return FALSE;
	}
	
	public function get_modules()
	{
		if(isset(self::$_config['modules']))
		{
			return self::$_config['modules'];
		}
		
		return FALSE;
	}
	
	public function set_site_alias($site_alias)
	{
		self::$_site_alias = $site_alias;
	}
	
	public function check_hostname()
	{
		// To determine the site we need to get a supported hostname object
		$hostname = new Hostname();
		
		// Pass the storage object to the hostname object
		$hostname->set_storage(self::$_storage);

		// Process the hostname
		$hostname->process();
	
		return $hostname;
	}

	/**
	 * Set the storage method used for site configs
	 */
	public static function set_storage($storage)
	{
		self::$_storage = $storage;
	}

	
} // End Site Class
