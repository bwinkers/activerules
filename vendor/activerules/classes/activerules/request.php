<?php  defined('AR_VERSION') or die('No direct script access.');
/**
 * Request library.
 *
 * @package    ActiveRules
 * @author     Brian Winkers
 * @copyright  (c) 2005-2013 Brian Winkers
 */
class Activerules_Request implements Interface_Request {
	
	/**
	 * Private object to hold the scrubbed and processed SESSION
	 */
	private $_session;
	
	/**
	 * Private object to hold the scrubbed and processed GET
	 */
	private $_get;
	
	/**
	 * Private object to hold the scrubbed and processed POST
	 */
	private $_post;
	
	/**
	 * Private object to hold the scrubbed and processed server
	 */
	private $_server;
	
	/**
	 * @var  string  the x-requested-with header which most likely
	 *               will be xmlhttprequest
	 */
	protected $_requested_with;

	/**
	 * @var  string  method: GET, POST, PUT, DELETE, HEAD, etc
	 */
	protected $_method = 'GET';

	/**
	 * @var  string  protocol: HTTP/1.1, FTP, CLI, etc
	 */
	protected $_protocol;

	/**
	 * @var  boolean
	 */
	protected $_secure = FALSE;

	/**
	 * @var  string  referring URL
	 */
	protected $_referrer;

	/**
	 * @var  Router object for this request
	 */
	protected $_router;

	/**
	 * @var  Kohana_HTTP_Header  headers to sent as part of the request
	 */
	protected $_header;

	/**
	 * @var  string the body
	 */
	protected $_body;

	/**
	 * @var  string  the URI of the request
	 */
	protected $_uri;

	/**
	 * @var  boolean  external request
	 */
	protected $_external = FALSE;

	/**
	 * @var array cookies sent with the request
	 */
	protected $_cookies = array();

	/**
	 * @var Kohana_Request_Client
	 */
	protected $_client;

	
	
	public function get_data($dot_path, $default)
	{
		
	}
	
	public function set_protocol($protocol)
	{
	
	}
	
	
	public function get_protocol()
	{
		
	}
	
	
	public function set_http_status($status)
	{
		
	}
	
	
	public function get_http_status()
	{
		
	}
	
	
	public function set_header($header, $replace=TRUE, $response_code='200')
	{
		
	}
	
	
	public function get_headers()
	{
		
	}
	

} 
