<?php defined('AR_VERSION') or die('No direct script access.');
/**
 * ActiveRules Request interface
 * This defines the core functionality the readable Request class needs to provide.
 * This interfaces does NOT support updating the Request object or telling it anything.
 *
 * @package    ActiveRules
 * @author     Brian Winkers
 * @copyright  (c) 2005-2013 Brian Winkers
 */
interface Activerules_Interface_Request { 
		
	
	public function get_data($dot_path, $default);
	
	
	public function set_doc_type($type);
	
	
	public function get_doc_type();
	
	
	public function set_protocol($protocol);
	
	
	public function get_protocol();
	
	
	public function set_http_status($status);
	
	
	public function get_http_status();
	
	
	public function set_header($header, $replace=TRUE, $response_code='200');
	
	
	public function get_headers();
	
}
	
	