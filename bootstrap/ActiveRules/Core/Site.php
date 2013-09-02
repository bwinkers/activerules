<?php
namespace ActiveRules\Core;

/**
 * Site library
 * 
 * Creates a global Site object with populated attributes.
 * The attribute values are defined on a site level and can be overridden or extended for each hostname related to that site.
 * Think of its as a way to replace ENVIRONMENT variables with far more dynamic data.
 * 
 * @package ActiveRules
 * @subpackage	Core
 * @author Brian Winkers
 * @copyright (c) 2005-2013 Brian Winkers
 * 
 */

class Site {	
	
    /**
     * Version of Site
     */
    const VERSION = '7.1';

    /**
     * The singleton instance
     */
    static protected $singleton;

    /**
     * The Site specific configuration
     * This will be based on the hostname.
     */
    static protected $configs;

    /**
     * The Host object representing the hostname
     */
    static protected $host;
    
    /**
     * The constructor is protected by 
     */
    protected function __construct()
    {

    }

    /**
     * Initialize a new request and store the config
     * 
     * @param type $hostname
     * @return type 
     */
    public function initialize($hostname=NULL)
    {
        // If the hostname isn't provided use the SERVER HTTP HOST
        if(!$hostname) {           
            $hostname = $_SERVER['HTTP_HOST'];
        }

        // Create a host object if configs are found for the host name
        $host = self::loadHost($hostname);
        
        if($host)
        {
            // Load the Site configs using the Hosts' site_alias
            $site_configs = self::loadConfiguration($host->getConfig('site_alias'));

            $merged_configs = array_merge_recursive($host->getConfigs(), $site_configs);
            
            self::$configs = $merged_configs;
            
            unset($host, $merged_configs, $site_configs);

        }  

        // Return self reference for chaining
        return self::$singleton;
    }

    /**
     * Process a hostname and return its config.
     * This is public so it can be called to process a domain for admin purposes.
     * 
     * @param type $hostname 
     */
    public function loadHost($hostname)
    {
        $host = new Host($hostname);
        
        if($host->getSupported()) {
            return $host;
        }
        
        return false;
    }
    
    /**
	 * Return one config value
	 */
	public static function getConfig($dot_path=false, $default=false)
	{
		$array = self::$configs;
        
        if($dot_path) {
            $keys = explode('.', $dot_path);
            
            // loop through each part and extract its value
            foreach ($keys as $key) {
                if (isset($array[$key])) {
                    // replace current value with the child
                    $array = $array[$key];
                } else {
                    // key doesn't exist, fail
                    return $default;
                }
            }
        }
        
        return $array;
	}
    
    /**
     * Determine if the site is valid enough to continue processing
     * 
     * @return boolean
     */
    public static function front_controller()
    {  
        return self::getConfig('routes.front_controller', false);
    }
   
    /**
     * Create a singleton instance of the Site class
     * 
     * @return object Self reference
     */
    public static function singleton()
    {
        if (!isset(self::$singleton)) {            
            $class_name = __CLASS__;
            self::$singleton = new $class_name;
        }

        /**
         * Return an the current object for chaining
         */
        return self::$singleton;
    }
    
    /**
     * Load all config files for a Site
     */
    private function loadConfiguration($site) 
    {
        $configs = array();
        
        // The directory where the site configs should be
        $site_directory = SITE_CONFIG_DIR.DIRECTORY_SEPARATOR.'site'.DIRECTORY_SEPARATOR.$site;

        // Does the site config directory exist?
        if(is_dir($site_directory)) {
           
            // Create a new directory iterator
			$dir = new \DirectoryIterator($site_directory);

			foreach ($dir as $file)	{
				// Get the file name
				$filename = $file->getFilename();

				if ($filename[0] === '.' OR $filename[strlen($filename)-1] === '~')	{
					// Skip all hidden files and UNIX backup files
					continue;
				}
                
                // Only process PHP files
                if($file->getExtension() == 'php') {
                    // Relative filename is the array key
                    $full_path = $site_directory.DIRECTORY_SEPARATOR.$filename;

                    require_once($full_path);
                    
                    // Get the base filename to use for namespacing the config
                    $config_key = $file->getBasename(".php");

                    // Add the config to the array
                    $configs[$config_key] = $config;
                }
			}
        }
        
        // Return whatever the configs are
        return $configs;
    }
}
?>
