<?php
/**
 * Hostname library.
 *
 * @package    ActiveRules
 * @author     Brian Winkers
 * @copyright  (c) 2005-2013 Brian Winkers
 */
class Activerules_Hostname {
	
	/**
	 * This is used for hostname specfic directories etc.
	 * @var string The hostname alias 
	 */
	private static $_requested_hostname;
	
	private static $_supported_hostname = FALSE;
	
	private static $_configured_hostname = FALSE;
	
	private static $_storage = 'file';
	
	private static $_site_alias;
	
	private static $_host_data;
	
	private static $_subdomain_levels = FALSE;
	
	
	/**
	 * $hostname string Hostname to start from or HTTP_HOST
	 */
	public function __construct($hostname=NULL)
	{
		if($hostname===NULL)
		{
			$hostname = $_SERVER['HTTP_HOST'];
		}

		self::$_requested_hostname = $hostname;

	}
	
	/**
	 * Determine the base supported domain.
	 * Activerules supports dynamic domain structures.
	 */
	public static function supported()
	{
		return self::$_found_hostname;
	}
	
	/**
	 * Map a hostname to a supported hostname
	 * 
	 * @param object $hostname  Hostname object
	 * @return 
	 */
	public static function process($hostname=NULL)
	{
		if($hostname===NULL)
		{
			$hostname = self::$_requested_hostname;
		}

		// check if the host is configured
		$host_check = self::configured_hostname($hostname);

		if($host_check)
		{
			// Set the hostname that was found
			self::$_configured_hostname = $host_check['hostname'];
			
			// At minimum we need a site defined
			self::$_site_alias = $host_check['data']['site_alias'];
			
			// We remove the site_alias and set the rest of the host data in the object
			unset($host_check['data']['site_alias']);
			self::$_host_data = $host_check['data'];
			
			// We also check to see if the host supports subhosts
			// and if subhosts are supported how many levels are supported
			if(isset($host_check['data']['subdomain_levels']))
			{
				// trim the subdomains to the maximum level
				$remainder = trim(rtrim(self::$_requested_hostname, self::$_configured_hostname), '.');
				
				// Create an arry on the remaining dot separated parts
				$remaining_parts = explode('.', $remainder);
				
				// Change the number of subhosts supported to negative
				$num = -1 * abs($host_check['data']['subdomain_levels']);
				
				// Reverse the array
				$valid_parts = array_slice($remaining_parts, $num);
				
				// Assemble the longest supported domain name from the requested hostname
				self::$_supported_hostname = implode('.', $valid_parts).'.'.self::$_configured_hostname;
			}
			
			// We then check to see if the host defines any redirections
			// and if subhosts are supported how many levels are supported
			if(isset($host_check['data']['redirect']))
			{
				self::redirect_host($host_check['data']['redirect']);
			}
		}
		else
		{
			echo '404';
		}
	}
	
	public function redirect_host($redirect_config_array)
	{
		// get the final URL
		switch($redirect_config_array['target'])
		{
			case 'supported_hostname':
				$redirect_target = self::$_supported_hostname;
				break;

			case 'configured_hostname':
				$redirect_target = self::$_configured_hostname;
				break;

			default:
				$redirect_target = $redirect_config_array['target'];
				break;
		}

		// define the protocol, default to http if https isn't defined.
		if(isset($redirect_config_array['https']))
		{
			$protocol = 'https';
		}
		else
		{
			$protocol = 'http';
		}

		// handle setting the proper headers for the redirect type
		switch($redirect_config_array['status'])
		{
			case '301':
				header("HTTP/1.1 301 Moved Permanently");
				break;

			default:
				// Do nothing if we didn't get a permanent redirect type
				// The dfault of location is 302, temporary.
				break;
		}
		
		// Verify the configured redirect isn't the same as the current request
		if(! empty($_SERVER['HTTPS']))
		{
			if($protocol==='https' AND $redirect_target == $_SERVER['HTTP_HOST'])
			{
				return;
			}
		}
		else
		{
			if($protocol==='http' AND $redirect_target == $_SERVER['HTTP_HOST'])
			{
				return;
			}
		}

		// Perform the redirect
		header("Location: $protocol://$redirect_target"); 
	}
	
	/**
	 * Determines if there is a valid config for this hostname.
	 */
	public function configured_hostname($hostname)
	{
		// If the host is valid there will be a config group with that name

		// Load the config.
		// This will return a hostname config array or FALSE
		$host_data = self::$_storage->load_group('host'.DIRECTORY_SEPARATOR.$hostname);

		if($host_data)
		{
			return array('hostname'=>$hostname,
							'data'=>$host_data);
		}
		else
		{
			// If there is still one dot left we have a possibly valid domain name
			// So we keep recursing
			$dot_location = stripos($hostname, '.');

			if($dot_location)
			{
				// Strip off the first part of the domain and check that domain
				$next_hostname = substr($hostname, $dot_location+1);

				return self::configured_hostname($next_hostname);
			}
		}
		
		return FALSE;
	}
	
	
	/**
	 * Set the storage method used for hostname configs
	 */
	public static function set_storage($storage)
	{
		self::$_storage = $storage;
	}
	
	public static function get_requested_hostname()
	{
		return self::$_requested_hostname;
	}
	
	public static function get_configured_hostname()
	{
		return self::$_configured_hostname;
	}
	
	public static function get_supported_hostname()
	{
		return self::$_supported_hostname;
	}
	
	public static function get_site_alias()
	{
		return self::$_site_alias;
	}
	
	public static function get_host_data()
	{
		return self::$_host_data;
	}
	
	public function __toString()
	{
		return  self::$_supported_hostname;
	}
	
} // End Hostname Class
