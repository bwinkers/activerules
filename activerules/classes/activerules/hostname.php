<?php defined('AR_VERSION') or die('No direct script access.');
/**
 * Core ActiveRules Hostname library.
 *
 * @package    ActiveRules
 * @author     Brian Winkers
 * @copyright  (c) 2005-2013 Brian Winkers
 */
class Activerules_Hostname implements Interface_Hostname {
	
	/**
	 * This is the first hostname recieved.
	 * Prcoessing may trim it or redirect it.
	 */	
	private $_original_hostname;
		
	/**
	 * This is used for hostname specfic directories etc.
	 * This is the hostname that refers back to this host
	 */	
	private $_supported_hostname = FALSE;
	
	/**
	 * This is the hostname a configuration was found for.
	 * It may be lower than the original or supported hostanme, but never higher.
	 */		
	private $_configured_hostname = FALSE;
	
	/**
	 * This is the type of storage to use for looking up hostanme data.
	 */	
	private $_storage = 'file';
	
	/**
	 * This is used for hostname specfic directories etc.
	 */
	private $_site_alias;
	
	/**
	 * This is the host data array.
	 * It will override any site level data.
	 */
	private $_host_data;

	/**
	 * This defines the number of sub domain levls supported 
	 */
	private $_subdomain_levels = FALSE;
	
	/**
	 * Return supported hostname.
	 * Activerules supports dynamic domain structures.
	 * It is name used for the services at the hostname to refer itself.
	 * This is NOT the canonical hostname.
	 */
	public function get_supported()
	{
		return $this->_supported_hostname;
	}
	
	/**
	 * Return the hostname that a config file was found for.
	 * For dynamic domain hostingthe domain name used for configuration may not match the "supported hostname".
	 */
	public function get_configured()
	{
		return $this->_configured_hostname;
	}
	
	/**
	 * Return the hostname that a config file was found for.
	 * For dynamic domain hostingthe domain name used for configuration may not match the "supported hostname".
	 */
	public function get_original()
	{
		return $this->_original_hostname;
	}
	
	/**
	 * Return the site alias associated with this hostname.
	 * Associating hostnames and sites is a core ActiveRules service.
	 */
	public function get_site_alias()
	{
		return $this->_site_alias;
	}
	
	/**
	 * Provides a config array for the host.
	 * ActiveRules allows host level data to override site level data.
	 */
	public function get_host_data()
	{
		return $this->_host_data;
	}
	
	/**
	 * Map a hostname to a supported hostname
	 * 
	 * @param object $hostname  Hostname object
	 * @return 
	 */
	public function process($hostname=NULL)
	{
		if($hostname===NULL)
		{
			$hostname = $this->_requested_hostname;
		}

		// check if the host is configured
		$host_check = $this->_configured_hostname($hostname);

		if($host_check)
		{
			// Set the hostname that was found
			$this->_configured_hostname = $host_check['hostname'];
			
			// At minimum we need a site defined
			$this->_site_alias = $host_check['data']['site_alias'];
			
			// We remove the site_alias and set the rest of the host data in the object
			unset($host_check['data']['site_alias']);
			$this->_host_data = $host_check['data'];
			
			// We also check to see if the host supports subhosts
			// and if subhosts are supported how many levels are supported
			if(isset($host_check['data']['subdomain_levels']))
			{
				// trim the subdomains to the maximum level
				$remainder = trim(rtrim($this->_requested_hostname, $this->_configured_hostname), '.');
				
				// Create an arry on the remaining dot separated parts
				$remaining_parts = explode('.', $remainder);
				
				// Change the number of subhosts supported to negative
				$num = -1 * abs($host_check['data']['subdomain_levels']);
				
				// Reverse the array
				$valid_parts = array_slice($remaining_parts, $num);
				
				// Assemble the longest supported domain name from the requested hostname
				$this->_supported_hostname = implode('.', $valid_parts).'.'.$this->_configured_hostname;
			}
			
			// We then check to see if the host defines any redirections
			// and if subhosts are supported how many levels are supported
			if(isset($host_check['data']['redirect']))
			{
				$this->_redirect_host($host_check['data']['redirect']);
			}
		}
		else
		{
			echo '404';
		}
	}
	
	/**
	 * $hostname string Hostname to start from or HTTP_HOST
	 */
	public function __construct($hostname=NULL)
	{
		if($hostname===NULL)
		{
			$hostname = $_SERVER['HTTP_HOST'];
		}

		$this->_requested_hostname = $hostname;
	}
	
	/**
	 * Set the storage method used for hostname configs.
	 * This method is public because the Site class needs to use it to pass in a storage object.
	 * 
	 * @param object Storage object 
	 */
	public function set_storage($storage)
	{
		$this->_storage = $storage;
	}
	
	/**
	 * This provides redirects in a very basic way for handling host level redirects.
	 * It is private because other modules should NOT try to use this.
	 * 
	 * @param type $redirect_config_array
	 * @return type 
	 */
	private function _redirect_host($redirect_config_array)
	{
		// get the final URL
		switch($redirect_config_array['target'])
		{
			case 'supported_hostname':
				$redirect_target = $this->_supported_hostname;
				break;

			case 'configured_hostname':
				$redirect_target = $this->_configured_hostname;
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
	 * This method is private becasue nothing should care HOW Hostname goes about its business.
	 */
	private function _configured_hostname($hostname)
	{
		// If the host is valid there will be a config group with that name

		// Load the config.
		// This will return a hostname config array or FALSE
		$host_data = $this->_storage->load_group('host'.DIRECTORY_SEPARATOR.$hostname);

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

				return $this->_configured_hostname($next_hostname);
			}
		}
		
		return FALSE;
	}
	
	
	public function __toString()
	{
		return  $this->_supported_hostname;
	}
	
} // End Activerules_Hostname Class
