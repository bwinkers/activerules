<?php defined('AR_VERSION') or die('No direct access');
/**
 *
 */
class Activerules_Exception extends Exception {

	/**
	 * @var  array  PHP error code => human readable name
	 */
	public static $php_errors = array(
		E_ERROR              => 'Fatal Error',
		E_USER_ERROR         => 'User Error',
		E_PARSE              => 'Parse Error',
		E_WARNING            => 'Warning',
		E_USER_WARNING       => 'User Warning',
		E_STRICT             => 'Strict',
		E_NOTICE             => 'Notice',
		E_RECOVERABLE_ERROR  => 'Recoverable Error',
	);
	
   /**
	* @var  string  error view content type
	*/
	public static $error_view_content_type = 'text/html';

	/**
	 * Creates a new translated exception.
	 *
	 *     throw new Activerules_Exception('Something went terrible wrong, :user',
	 *         array(':user' => $user));
	 *
	 * @param   string          error message
	 * @param   array           translation variables
	 * @param   integer|string  the exception code
	 * @return  void
	 */
	public function __construct($message, array $variables = array(), $code = 0)
	{
		if (defined('E_DEPRECATED'))
		{
			// E_DEPRECATED only exists in PHP >= 5.3.0
			Activerules_Exception::$php_errors[E_DEPRECATED] = 'Deprecated';
		}

		// Set the message
		$message = strtr($message, $variables);

		// Pass the message and integer code to the parent
		parent::__construct($message, (int) $code);

		// Save the unmodified code
		// @link http://bugs.php.net/39615
		$this->code = $code;
	}

	/**
	 * Magic object-to-string method.
	 *
	 *     echo $exception;
	 *
	 * @uses    Activerules_Exception::text
	 * @return  string
	 */
	public function __toString()
	{
		return Activerules_Exception::text($this);
	}

	/**
	 * Inline exception handler, displays the error message, source of the
	 * exception, and the stack trace of the error.
	 *
	 * @uses    Activerules_Exception::text
	 * @param   object   exception object
	 * @return  boolean
	 */
	public static function handler(Exception $e)
	{
		try
		{
			// Get the exception information
			$type    = get_class($e);
			$code    = $e->getCode();
			$message = $e->getMessage();
			$file    = $e->getFile();
			$line    = $e->getLine();
			
			// Get the exception backtrace
			$trace = $e->getTrace();

			if ($e instanceof ErrorException)
			{
				if (isset(Activerules_Exception::$php_errors[$code]))
				{
					// Use the human-readable error name
					$code = Activerules_Exception::$php_errors[$code];
				}
			}

			// Create a text version of the exception
			$error = Activerules_Exception::text($e);

			if ( ! headers_sent())
			{
				// Make sure the proper http header is sent
				$http_header_status = ($e instanceof HTTP_Exception) ? $code : 500;

				header('Content-Type: '.Activerules_Exception::$error_view_content_type.'; charset='.AR::$charset, TRUE, $http_header_status);
			}

			// Start an output buffer
			ob_start();

			echo $error;
			echo '<pre>';
			foreach($trace as $stack)
			{
				echo "\n".Dbg::path($stack['file']).' ['.$stack['line'].']';
				echo "\n".$stack['class'].'::'.$stack['function'];
				echo "\n";
				foreach($stack['args'] as $arg)
				{
					switch(gettype($arg))
					{
						case 'string':
							echo $arg;
							break;
						
						case 'object':
						case 'array':
							var_export($arg);
							break;
					}
					echo "\n";
				}
			}
			echo "\n";
			
			// Display the contents of the output buffer
			echo ob_get_clean();

			exit(1);
		}
		catch (Exception $e)
		{
			// Clean the output buffer if one exists
			ob_get_level() and ob_clean();

			// Display the exception text
			echo Activerules_Exception::text($e), "\n";

			// Exit with an error status
			exit(1);
		}
	}

	/**
	 * Get a single line of text representing the exception:
	 *
	 * Error [ Code ]: Message ~ File [ Line ]
	 *
	 * @param   object  Exception
	 * @return  string
	 */
	public static function text(Exception $e)
	{
		return sprintf('%s [ %s ]: %s ~ %s [ %d ]',
		get_class($e), $e->getCode(), strip_tags($e->getMessage()), Dbg::path($e->getFile()), $e->getLine());
	}

} // End Activerules_Exception
