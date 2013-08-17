<?php defined('AR_VERSION') or die('No direct script access.');
/**
 * Site library.
 *
 * @package    ActiveRules
 * @author     Brian Winkers
 * @copyright  (c) 2005-2013 Brian Winkers
 */
class Activerules_Site implements Interface_Site {
	
	/**
	 * Information about the requested, configured and supported hostname.
	 * @var object Hostname object
	 */
	protected $_hostname;
	
	/**
	 * This is used for site specfic directories etc.
	 * @var string The site alias 
	 */
	protected $_site_alias;
	
	/**
	 * Location where site specific
	 * @var object Config object
	 */
	protected $_storage;
	
	/**
	 * Config populated from storage or cache
	 */
	protected $_config;

	/**
	 * Return a site object.
	 * We don't do too much 
	 * 
	 * @param string $site_alias
	 * @return Site object
	 */
	public static function factory($storage=NULL)
	{
		$site = new Site($storage);
		
		// Determine the hostname.
		$site->_determine_host();

		// get the Site alias from the Hostname
		$site_alias = $site->_hostname->get_site_alias();

		// This will return a hostname config array or FALSE
		$site_data = $storage->load_groups('site'.DIRECTORY_SEPARATOR.$site_alias);

		if($site_data)
		{
			// merge host data and site data
			$merged_configs = array_merge_recursive($site->_hostname->get_host_data(), $site_data);
			
			$site->_config = $merged_configs;
		}
		
		return $site;		
	}
	
	/**
	 * Initialize the site.
	 * Return the Site object for method chaining.
	 *
	 * @return $this 
	 */
	protected function _init_site()
	{
		// Determine the hostname.
		$this->_determine_host();

		// get the site alias from the hostname
		$site_alias = $this->_hostname->get_site_alias();

		// This will return a hostname config array or FALSE
		$site_data = $this->_storage->load_groups('site'.DIRECTORY_SEPARATOR.$site_alias);

		if($site_data)
		{
			// merge host data and site data
			$merged_configs = array_merge_recursive($this->_hostname->get_host_data(), $site_data);
			
			$this->_config = $merged_configs;
		}
		
		return $this;
	}
	
	/**
	 * Set the storage method used for site configs.
	 * This is not in the Interface becasue it isn't intrinsic tot he functionality of a site.
	 */
	public function set_storage($storage)
	{
		$this->_storage = $storage;

		return $this;
	}
	
	/**
	 * Return site config.
	 * Will take a default to return if the key is not set.
	 * The key can be set with NULL, zero, FALSE etc and will still get returned.
	 * 
	 * @param type $dot_path
	 * @param type $default
	 * @return type 
	 */
	public function config($dot_path=NULL, $default=FALSE)
	{
		if($dot_path===NULL)
		{
			return $this->_config;
		}
		else
		{
			$keys = explode('.', $dot_path);
			
			$config = $this->_config;
			
			while($key = array_shift($keys))
			{
				if(isset($config[$key]))
				{
					$config = $config[$key];
				}
				else
				{
					return $default;
				}
			}

			return $config;
		}
	}
	
	/**
	 * Load the site
	 */
	protected function __construct($storage=NULL)
	{
		$this->_storage = $storage;
	}
	
	/**
	 * Prevent any bad calls from throwing fatal errors
	 */
	public function __call($name, $arguments=NULL) 
	{
		
	}
	
	/**
	 * This stes the protected variable hostname object.
	 * It calls the Hostname object without a hostname so the default HTPP_HOST is used within the Hostname object.
	 */
	protected function _determine_host()
	{
		$hostname = $this->_valid_hostname();

		// Store the hostname data in the site
		$this->_hostname = $hostname;
	}
	
	/**
	 * Check if the hostname is supported.
	 * If it does exist return a Hostname objects.
	 * This could be called multiple time as it doesn't set any variable.
	 * 
	 * @return Hostname object
	 */
	protected function _valid_hostname()
	{
		// To determine the site we need to get a supported hostname object
		$hostname = new Hostname();

		// Pass the storage object to the hostname object
		$hostname->set_storage($this->_storage);
	
		// Process the hostname
		$hostname->process();

		// Hostname obejcts returns FALSE if its unsupported.
		return $hostname;
	}
	
	public function takeover_request()
	{
		Dbg::it('The site will handle things from here on.');
		
		/*
		 * The Site believes the correct thing to do is:
		 * 	1. Create a scrubbed Request object
		 * 	2. Use the Request to determine the Route
		 *		This will result in a routed request.
		 *		The Router object will remain part of the Request
		 * 	3. Pass the Request with routed changes to the class and method defined in the route.
		 *  4. Create a Response within the request
		 *  5. Output the response correctly.		  
		 */
		
		$request = new Request;
		
		$router = new Router;
		
		$response = new Response;
			
	}
	
	/**
	 * Return the Site Alias.
	 */
	public function site_alias()
	{
		return $this->_site_alias;
	}

} // End Site Class
