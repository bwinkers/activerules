<?php defined('AR_VERSION') or die('No direct script access.');
/**
 * ActiveRules Hostname interface
 * This defines the core functionality the Hostname class needs to provide
 *
 * @package    ActiveRules
 * @author     Brian Winkers
 * @copyright  (c) 2005-2013 Brian Winkers
 */
interface Activerules_Interface_Hostname { 
		
	/**
	 * Return supported hostname.
	 * Activerules supports dynamic domain structures.
	 * It is name used for the services at the hostname to refer itself.
	 * This is NOT the canonical hostname.
	 */
	public function get_supported();
	
	/**
	 * Return the hostname that a config file was found for.
	 * For dynamic domain hostingthe domain name used for configuration may not match the "supported hostname".
	 */
	public function get_configured();
	
	/**
	 * Return the hostname that a config file was found for.
	 * For dynamic domain hostingthe domain name used for configuration may not match the "supported hostname".
	 */
	public function get_original();
	
	/**
	 * Return the site alias associated with this hostname.
	 * Associating hostnames and sites is a core ActiveRules service.
	 */
	public function get_site_alias();
	
	/**
	 * Provides a config array for the host.
	 * ActiveRules allows host level data to override site level data.
	 */
	public function get_host_data();
	
	/**
	 * Map a hostname to a supported hostname
	 * 
	 * @param object $hostname  Hostname object
	 * @return 
	 */
	public function process($hostname);

}
	
	