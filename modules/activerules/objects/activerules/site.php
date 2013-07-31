<?php
/**
 * Site library.
 *
 * @package    ActiveRules
 * @author     Brian Winkers
 * @copyright  (c) 2005-2013 Brian Winkers
 */
class Activerules_Site {

	// Session singleton
	protected static $instance;

	// Configuration
	protected static $config;

	// Boolean for whether the site the is dynamic, meaning supported by ActiveRules.
	protected static $active = FALSE;

	/**
	 * Instance of Site.
	 */
	public static function & instance($hostname=FALSE)
	{
		if (Activerules_Site::$instance == NULL)
		{
			// Create a new instance
			Activerules_Site::$instance = new Activerules_Site($hostname);
		}

		return Activerules_Site::$instance;
	}

	/**
	 * On first site instance creation, it creates site.
	 */
	public function __construct($hostname)
	{
		// This part only needs to be run once
		if (Activerules_Site::$instance === NULL)
		{
			// The site and host caches reside in separate dirs within a shared dir at the cache root
			
			// Set the cahce dirs if either cching is enabled
			if(SITE_CACHING OR HOST_CACHING)
			{
				// Set the shared cache root
				$site_host_cache = CACHEROOT.'site_host'.DIRECTORY_SEPARATOR;
			
				// Set host cache dir if enabled
				if(HOST_CACHING)
				{
					$host_cache_dir = $site_host_cache.'host'.DIRECTORY_SEPARATOR;
					
					// Check if the directory already exists
					if(is_dir($host_cache_dir))
					{
						self::$host_cache_dir = $host_cache_dir;
					}
					else
					{
						// If it doesn't exist try to create the cache dir
						try
						{
							if(mkdir($host_cache_dir, 0750, TRUE))
							{
								self::$host_cache_dir = $host_cache_dir;
							}
						}
						catch( Exception $e)
						{
							// @TODO Hmmmm... too early to call logging
							// Log::file('Unable to create host cache directory: '.$host_cache_dir, 'error');
						}
					}
					
					unset($host_cache_dir);
				}
	
				// Set site cache dir if enabled
				if(SITE_CACHING)
				{
					$site_cache_dir = $site_host_cache.'site'.DIRECTORY_SEPARATOR;
					
					// Check if the directory already exists
					if(is_dir($site_cache_dir))
					{
						self::$site_cache_dir = $site_cache_dir;
					}
					else
					{  	// If it doesn't exist try to create the cache dir
						try
						{
							if(mkdir($site_cache_dir, 0750, TRUE))
							{
								self::$site_cache_dir = $site_cache_dir;
							}
						}
						catch( Exception $e)
						{
							// @TODO Hmmmm... too early to call logging
							//Log::file('Unable to create site cache directory: '.$site_cache_dir, 'error');
							//echo 'Unable to create: '.$site_cache_dir;
						}
					}
					
					unset($site_cache_dir);
				}
				
				unset($site_host_cache);
			}

			// Load the site configs from cache or files.
			// This also looks for certain site configs that affect further processing.
			// If it doesn't have any configs you get pretty much normal Kohana processing.
			self::_init_site($hostname);

			// Singleton instance
			Activerules_Site::$instance = $this;
		}
	}
	
	/**
	 * Initialize the site
	 */
	private function _init_site($hostname)
	{
        // We'll assume this is the root supported hostname
        self::$root_host = $hostname;
		
		self::calc_enviro();

        // If the site has any config entries its a dynamic framework site.
		// If it doesn't have any configs you get pretty much normal Kohana processing.
		if(self::_load_configs($hostname))
		{
			// The site is dynamic, enable all ActiveRules goodness.
			self::$active = TRUE;

			// Signal constructor method all is good
			return TRUE;
		}

		// Configs weren't found so we'll try the last 2-3 array elements for the root hostname
		$name_array = explode('.',$hostname);

		// Set TLD and 2nd Level name
		$tld = array_pop($name_array);
		$level_2 = array_pop($name_array);

		// If the 2nd level element is a common resold one use the 3rd element as well.
		// This supports domains under co.uk, com.cn etc.
		if($level_2 == 'co' OR $level_2 == 'com' OR $level_2 == 'org' OR $level_2 == 'net')
		{
			 // Set third level name to hostname, purposely overwrite larger hostname.
			 $hostname = array_pop($name_array);

			 // Create hostname string.
			 self::$root_host = $level_3.'.'.$level_2.'.'.$tld;
		}
		else
		{
			// We have our hostname already, purposely overwrite larger hostname.
			self::$hostname = $level_2;

			// Create full hostname string.
			self::$root_host = $level_2.'.'.$tld;
		}

		// Drop name_array in case it was big
		unset($name_array);

		if (self::_load_configs(self::$root_host))
		{
			// If this site supports dynamic sub_hosts set the subhost name and return
			if(self::$get('sub_hosts'))
			{
				// This is the root supported hostname
				self::$root_host = $hostname;

				// The site is dynamic, enable all ActiveRules goodness.
				self::$active = TRUE;

				// Set the sub_host name for sites that use it as an ActiveRules dimension.
				self::$sub_host =self::$hostname;
		
				// Signal constructor method all is good
				return TRUE;
			}
			else
			{
				url::redirect('http://'.self::$root_host);
			}
		}

		// We didn't find any configs for this hostname.
		return FALSE;
	}

	/**
	 * Load configs or throw error
	 */
	private function _load_configs($hostname)
	{
        // Load the defined site
        if(self::_load_site($hostname))
		{
			return TRUE;
		}
		else
    	{
            // Put the hostname into an array
            $name_array = explode('.', $hostname);

            // Keep removing hostname parts until we're out of pieces
            while(count($name_array) > 2)
            {
                 // Removed initial hostname part since we know that doesn't match
                array_shift($name_array);

                $hostname = implode('.', $name_array);

                if(self::_load_site($hostname))
				{
					//check for redirect in site/host config
                    //self::site_redirect_check();

					return TRUE;
                }
            }
        }

		return FALSE;
	}

    private function _load_site($hostname)
    {
		$cached_host = self::get_host_cache_name($hostname);

		if(HOST_CACHING AND file_exists($cached_host))
		{	
			require_once($cached_host);
		}
		else
		{
			// Define path to ActiveRules Host config files
			$active_host_path = CONFIGPATH.'host'.DIRECTORY_SEPARATOR;

			// Define path to the specific ActiveRules Host config file
			$host_file = $active_host_path.$hostname.'.host'.EXT;

			// If the host fiel doesn't exist we can't continue
			if(!file_exists($host_file))
			{
				return FALSE;
			}

			// Initial site array
			$site = array();

			// Include the file that defines a $host config array
			include_once($host_file);

			// Check to see if a 'site' is defined by the host, if so load the site config file.
			if(array_key_exists('site', $host))
			{
				$cached_site = self::get_site_cache_name($host['site']);

				if(SITE_CACHING AND file_exists($cached_site))
				{	
					require_once($cached_site);
				}
				else // Load the individual config files
				{
					// Define path to ActiveRules Site config files
					$active_site_dir = CONFIGPATH.'site'.DIRECTORY_SEPARATOR.$host['site'];

					// Check to see if the site directory exists
					try
					{
						$site_dir = dir($active_site_dir);

						$full_site = array();

						// Loop through site config files
						while (false !== ($entry = $site_dir->read())) 
						{
						   if($entry)
						   {
							   if(substr($entry,0, 1) != '.')
							   {
								   $site_inc = $active_site_dir.DIRECTORY_SEPARATOR.$entry;
								   include_once($site_inc);
								   $full_site = array_merge($full_site, $site);
							   }
						   }
						}

						// We don't need either of these variables and we have more processing to do
						unset($site, $site_dir);

						// If site caching is turned on we export the array and write it to a file
						if(SITE_CACHING)
						{
							self::_write_cache('site', $host['site'], $full_site);
						}
					}
					catch (Exception $e)
					{
						echo 'There are no configs for this site';
					}
				}

				// Set the configs, merging the arrays so that the host array overrides values in the site array.
				$merged_configs = array_merge($full_site, $host);

				// If host caching is turned on we export the array and write it to a file
				if(HOST_CACHING)
				{
					self::_write_cache('host', $hostname, $merged_configs);
				}

				// If a specfic enviro was set overload the calculated enviro
				if(isset($merged_configs['enviro']))
				{
					self::set_enviro($merged_configs['enviro']);
				}
			}
			else // No site defined for this host
			{
				return FALSE;
			}
		}

		// Set the configs to the static property
		self::$config = $merged_configs;

		return TRUE;
    }

	public function site_redirect_check()
	{
		if($redirect_url = Site::conf('redirect_url', FALSE))
		{
			header('Location: '.$redirect_url);
		}
	}
	
	/**
	 * Is this Request supported by ActiveRules?
	 * If not you get pretty much default Kohana handling
	 */
	public static function active()
	{
		return self::$active;
	}
	
	/**
	 * Is this Request supported by ActiveRules?
	 * If not you get pretty much default Kohana handling
	 */
	public static function get_enviro()
	{
		return self::$enviro;
	}
	
	/**
	 * Is this Request supported by ActiveRules?
	 * If not you get pretty much default Kohana handling
	 */
	public static function set_enviro($enviro)
	{
		self::$enviro = $enviro;
	}
	
	/**
	 * Calculate the enviro based on the hostname
	 */
	protected function calc_enviro()
	{
		// Set the presumed ENVIRO
		$host = strstr($_SERVER['HTTP_HOST'], '.', true);

		switch($host)
		{
			case 'stage':
				$enviro = 'stage';
				break;

			case 'beta':
				$enviro = 'beta';
				break;

			case 'dev':
				$enviro = 'dev';
				break;

			default:
				$enviro = 'prod';
				break;
		}
		
		self::set_enviro($enviro);
	}

	/**
	 * Get site config
	 */
	public static function conf($var_name, $default=NULL)
	{
        // the var_name has a dot, it means this is a dot notated array request
        if (strpos($var_name, '.') !== FALSE)
        {
            return arr::path(Site::$config, $var_name, $default);
        }
        elseif(isset(self::$config[$var_name]))
        {
            return self::$config[$var_name];
        }

        return $default;
	}
	
	/**
	 * Get ALL site configs
	 */
	public static function configs()
	{
        return self::$config;
	}

	/**
	 * Get site subhost
	 */
	public function sub_host()
	{
		return self::$sub_host;
	}

	/**
	 * Create a Site cache file.
	 */
	private function _cache_site($site, $data)
	{
		self::_write_cache('site', $site, $data);
	}
	
	/**
	 * Write a cache file
	 */
	private function _write_cache($type, $name, $data)
	{
		$data = var_export($data, TRUE);
						
		// Wrap the content in PHP tags before writing
		if($type == 'site')
		{
			// Define the cache file
			$cache_file = self::get_site_cache_name($name);
			
			// Write out the contents
			file_put_contents($cache_file,"<?php\n\$full_site=$data;");
		}
		elseif($type == 'host')
		{
			// Define the cache file
			$cache_file = self::get_host_cache_name($name);
			
			// Write out the contents
			file_put_contents($cache_file,"<?php\n\$merged_configs=$data;");
		}

	}
	
	/**
	 * Return the name of a site cache file
	 */
	public function get_site_cache_name($site)
	{
		return self::$site_cache_dir.$site.EXT;
	}
	
	/**
	 * Return the name of a hostname cache file
	 */
	public function get_host_cache_name($host)
	{
		// Replace dots with underscore
		$host = str_replace('.', '_', $host);
		
		return self::$host_cache_dir.$host.EXT;
	}
	
	/**
	 * Load a cached hostname file.
	 * This will include the site information as well.
	 */
	private function _get_cached_host($hostname)
	{
		
	}
	
	/**
	 * Load a cached site file.
	 * Uncached hostname info will be merged into this.
	 */
	private function _get_cached_site($site)
	{
		
	}
	
	/**
	 * Delete a hostname cache file.
	 */
	private function _delete_cached_host($hostname)
	{
		
	}
	
	/**
	 * Delete a Site cache file.
	 */
	private function _delete_cached_site($site)
	{
		
	}

} // End Site Class
