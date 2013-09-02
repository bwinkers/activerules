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
        if(!$hostname) {           
            $hostname = $_SERVER['HTTP_HOST'];
        }

        self::$configs = self::loadHost($hostname);

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
}
?>
