<?php
namespace ActiveRules\Core;

/**
 * Site library
 * 
 * Creates a global Site object with populated attributes.
 * The attribute values are defined on a site level and can be overridden or extended for each hostname related to that site.
 * Think of its as a way to replace ENVIRONMENT varaiables with far more dynamic data.
 * 
 */

class Site {	
	
	/**
	 * Version of Site
	 */
	protected static $version = '7.1';
	
	/**
	 * The singleton instance
	 */
	protected static $singleton;
	
	/**
	 * The Site specific configuration
	 * This will be based on the hostname.
	 */
	protected static $config;
	
	/**
	 * the cosntructor is protected by 
	 */
	protected function __construct()
	{
		
	}
	
	public static function version()
	{
		return self::$version;
	}
	
	/**
	 * Initialize a new request and store the config
	 * @param type $hostname
	 * @return type 
	 */
	public function initialize($hostname=NULL)
	{
		if(!$hostname) {
			$hostname = $_SERVER['HTTP_HOST'];
		}
		
		self::$config = self::process_hostname($hostname);
		
		return self::$singleton;
	}
	
	/**
	 * Process a hostname and return its config.
	 * This is public so it can be called to process a domain for admin purposes.
	 * 
	 * @param type $hostname 
	 */
	public function process_hostname($hostname)
	{
		//
	}
	
	/**
	 * Create a singleton instance of the Site class
	 * 
	 * @return object Self reference
	 */
	public function singleton()
	{
		if (!isset(self::$singleton)) 
		{
            $class_name = __CLASS__;
            self::$singleton = new $class_name;
        }
		
		/**
		 * Return an the current object for chaining
		 */
        return self::$singleton;
	}
}
?>
