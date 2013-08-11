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
	private $_hostname;
	
	/**
	 * This is used for site specfic directories etc.
	 * @var string The site alias 
	 */
	private $_site_alias;
	
	/**
	 * Location where site specific
	 * @var object Config object
	 */
	private $_storage;
	
	/**
	 * Config populated from storage or cache
	 */
	private $_config;

	/**
	 * Return a site object.
	 * 
	 * @param string $site_alias
	 * @return Site object
	 */
	public static function factory($site_alias=NULL)
	{
		$site = new Site($site_alias);
		
		return $site;		
	}
	
	/**
	 * Initialize the site.
	 * Return the Site object for method chaining.
	 *
	 * @return $this 
	 */
	public function init_site()
	{
		// Determine the hostname
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
	 * Load the site
	 */
	private function __construct($site_alias=NULL)
	{
		if($site_alias)
		{
			$this->$_site_alias = $site_alias;
		}
	}
	
	/**
	 * Determine the site host based on all available data
	 */
	private function _determine_host()
	{
		$hostname = $this->_valid_hostname();

		// Store the hostname data in the site
		$this->_hostname = $hostname;
	}
	
	
	
	/**
	 * get the modules used by this site
	 * @return type 
	 */
	public function get_modules()
	{
		if(isset($this->_config['modules']))
		{
			return $this->_config['modules'];
		}
		
		return FALSE;
	}
	
	/**
	 * Check if the hostname is supported.
	 * If it does exist return a Hostname object.
	 * 
	 * @return Hostname object
	 */
	private function _valid_hostname()
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

} // End Site Class
