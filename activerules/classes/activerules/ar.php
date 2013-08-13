<?php defined('AR_VERSION') or die('No direct script access.');
/**
 * AR (ActiveRules) library.
 * This gets loaded as a singleton for all request.
 * It loads a site config and performs the core ActiveRules bootstrapping.
 * 
 *
 * @package    ActiveRules
 * @author     Brian Winkers
 * @copyright  (c) 2005-2013 Brian Winkers
 */
class Activerules_AR {
		
	// Security check that is added to all generated PHP files
	const FILE_SECURITY = '<?php defined(\'AR_VERSION\') or die(\'No direct script access.\');';

	/**
	 * @var  boolean  True if Kohana is running from the command line
	 */
	public static $is_cli = FALSE;

	/**
	 * @var  boolean  True if Kohana is running on windows
	 */
	public static $is_windows = FALSE;

	/**
	 * @var  boolean  True if [magic quotes](http://php.net/manual/en/security.magicquotes.php) is enabled.
	 */
	public static $magic_quotes = FALSE;

	/**
	 * @var  boolean  Should errors and exceptions be logged
	 */
	public static $log_errors = FALSE;

	/**
	 * @var  boolean  TRUE if PHP safe mode is on
	 */
	public static $safe_mode = FALSE;

	/**
	 * @var  string
	 */
	public static $content_type = 'text/html';

	/**
	 * @var  string  character set of input and output
	 */
	public static $charset = 'utf-8';

	/**
	 * @var  boolean  Whether to use internal caching for find_file
	 */
	public static $caching = FALSE;

	/**
	 * @var  boolean  Enable catching and displaying PHP errors and exceptions by ActiveRules
	 */
	public static $trap_errors = TRUE;

	/**
	 * @var  array  Types of errors to display at shutdown
	 */
	public static $shutdown_errors = array(E_PARSE, E_ERROR, E_USER_ERROR);

	/**
	 * @var  Log  logging object
	 */
	public static $log;
	
	/**
	 * @var Site object
	 */
	public static $_site = NULL;

	/**
	 * @var  boolean  Has init been called?
	 */
	protected static $_init = FALSE;

	/**
	 * @var  array   Currently active modules
	 */
	protected static $_modules = array();

	/**
	 * @var  array  Include paths that are used to find files
	 */
	protected static $_paths = array(ACTIVEPATH);

	/**
	 * @var  array   File path cache, used when caching is true 
	 */
	protected static $_files = array();
	
	/**
	 * @var  boolean  Has the file path cache changed during this execution?  Used internally when when caching is true 
	 */
	protected static $_files_changed = FALSE;
	
	protected static $_storage = 'file';
	protected static $_cacher = NULL;
	protected static $_logger = NULL;
	
	private static $instance;
	
	private function __construct()
	{
		/* PRIVATE */
	}
	
	/**
	 * Create a singleton instance of the AR class
	 * 
	 * @return object Self reference
	 */
	public static function instance()
	{
		if (!isset(self::$instance)) 
		{
            $class_name = __CLASS__;
            self::$instance = new $class_name;
        }
		
		/**
		 * Return an the current object for chaining
		 */
        return self::$instance;
	}
	
	/**
	 * Add a module path
	 * This is used by the cascading file system used for autolaoding classes.
	 * 
	 * @param string Module path
	 */
	public static function add_module($module, $end=TRUE)
	{
		// if the module isn't a directory don;t bother adding it.
		if(is_dir($module))
		{  		
			if($end)
			{
				self::$_modules[] = $module;
				return TRUE;
			}
			else
			{
				array_unshift(self::$_modules, $module);
				return TRUE;
			}
		}
		
		// The module was not able to be added
		return FALSE;
	}
	
	/**
	 * Configure the base services for ActiveRules site boot process.
	 * Return itself for method chaining.
	 * 
	 * @param type $config_array
	 * @return object Self reference
	 */
	public function configure($config_array=NULL)
	{
		// Set the AR storage if defined
		if(isset($config_array['config_storage']))
		{
			self::set_storage($config_array['config_storage']);
		}

		// Set the AR cacher if defined
		if(isset($config_array['cacher']))
		{
			self::set_cacher($config_array['cacher']);
		}

		// Set the AR logger if defined
		if(isset($config_array['logger']))
		{
			self::set_logger($config_array['logger']);
		}

		/**
		 * Return an the current object for chaining
		 */
		return self::$instance;
	}
	
	/**
	 * Start the ActiveRules processing
	 * 
	 * @return object Self reference
	 */
	public function load_site()
	{
		try
		{	
			/**
			 * Create the Site class.
			 * If we passed in a site name it would load that site.
			 */
			$site = Site::factory(self::$_storage);

			/**
			 * Set the sites error_reporting
			 */
			error_reporting($site->config('errors.error_reporting', 0));
	
			/**
			 * Load the modules from the Site host config
			 */
			$modules = $site->get_modules();

			if($modules)
			{
				foreach($modules as $module)
				{	
					$mod_path = DOCROOT.'modules'.DIRECTORY_SEPARATOR.$module.DIRECTORY_SEPARATOR;

					$this->add_module($mod_path);
				}
			}

			// Bootstrap the modules
			// We wait until all modules are loaded to reduce issues with load order dependencies.
			self::_bootstrap_modules();
		
			/**
			 * Store the Site object witihn the AR singleton
			 * The Site object also has the Hostname object avilable to it.
			 */
			self::$_site = $site;

		}
		catch ( Exception $e)
		{
			Activerules_Exception::handler($e);
		}

		/**
		 * Return an the current object for chaining
		 */
		return self::$instance;
	}
	
	
	/**
	 * Use the Site Route to determine how to process the request.
	 * Call that method with the request objects and the Activerules Site Host boot process is complete.
	 */
	public function process_request()
	{
		try
		{
			self::$_site->takeover_request();
		}
		catch( Exception $e)
		{
			Activerules_Exception::handler($e);
		}
	}
	
	/**
	 * Return results from the Site config data.
	 * 
	 * @param type $dot_config
	 * @param type $default
	 * @return type 
	 */
	public static function site($dot_config=NULL, $default=NULL)
	{
		// The Site config method should perform ALL logic about what to return
		return self::$_site->config($dot_config, $default);
	}
	
	/**
	 * Set the storage method used for site configs
	 */
	public static function set_storage($storage)
	{
		switch($storage)
		{
			case 'file':
				// Load Config class with file driver and ACTIVEPATH
				self::$_storage = new Config('file', array('filepath'=>ACTIVEPATH));
				break;
				
			default:
				echo $storage;
				break;
		}
	}
	
	/**
	 * Provides auto-loading support of classes that follow ActiveRules's 
	 *
	 * Class names are converted to file names by making the class name
	 * lowercase and converting underscores to slashes:
	 *
	 * You should never have to call this function, as simply calling a class
	 * will cause it to be called.
	 *
	 * This function must be enabled as an autoloader in the index file:
	 *
	 *     spl_autoload_register(array('Activerules_AR', 'autoload'));
	 *
	 * @param   string   class name
	 * @return  boolean
	 */
	public static function autoload($class)
	{
		try
		{
			// Transform the class name into a path
			$file = str_replace('_', DIRECTORY_SEPARATOR, strtolower($class));

			$path = self::find_file('classes', $file);

			if ($path)
			{
				// Load the class file
				require $path;

				// Class has been found
				return TRUE;
			}

			// Class is not in the filesystem
			return FALSE;
		}
		catch (Activerules_Exception $e)
		{
			Activerules_Exception::handler($e);
			die;
		}
	}
	
	/**
	 * Searches for a file in the [Cascading Filesystem], and
	 * returns the path to the file that has the highest precedence, so that it
	 * can be included.
	 *
	 * When searching the "config" or "l10n" directories, or when
	 * the `$array` flag is set to true, an array of all the files that match
	 * that path in the [Cascading Filesystem] will be returned.
	 * These files will return arrays which must be merged together.
	 *
	 * If no extension is given, the default extension (`EXT` set in
	 * `index.php`) will be used.
	 *
	 *     // Returns an absolute path to views/template.php
	 *     AR::find_file('views', 'template');
	 *
	 *     // Returns an absolute path to media/css/style.css
	 *     AR::find_file('media', 'css/style', 'css');
	 *
	 *     // Returns an array of all the "mimes" configuration files
	 *     AR::find_file('config', 'mimes');
	 *
	 * @param   string   directory name (views, l10n, classes, extensions, etc.)
	 * @param   string   filename with subdirectory
	 * @param   string   extension to search for
	 * @param   boolean  return an array of files?
	 * @return  array    a list of files when $array is TRUE
	 * @return  string   single file path
	 */
	public static function find_file($dir, $file, $ext = NULL, $array = FALSE)
	{
		if ($ext === NULL)
		{
			// Use the default extension
			$ext = EXT;
		}
		elseif ($ext)
		{
			// Prefix the extension with a period
			$ext = ".{$ext}";
		}
		else
		{
			// Use no extension
			$ext = '';
		}

		// Create a partial path of the filename
		$path = $dir.DIRECTORY_SEPARATOR.$file.$ext;

		// Create a list of places to look by combining modules before defined paths
		$places = array_merge(self::$_paths, self::$_modules);

		if (self::$caching === TRUE AND isset(self::$_files[$path.($array ? '_array' : '_path')]))
		{
			// This path has been cached
			return self::$_files[$path.($array ? '_array' : '_path')];
		}

		if ($array OR $dir === 'config' OR $dir === 'l10n')
		{
			// Include paths must be searched in reverse
			$places = array_reverse($places);

			// Array of files that have been found
			$found = array();

			foreach ($places as $place)
			{
				if (is_file($place.$path))
				{
					//echo '<br>full: '.$dir.$path;
					// This path has a file, add it to the list
					$found[] = $place.$path;
				}
			}
		}
		else
		{
			// The file has not been found yet
			$found = FALSE;

			foreach ($places as $place)
			{
				if (is_file($place.$path))
				{ 
					// A path has been found
					$found = $place.$path;

					// Stop searching
					break;
				}
			}
		}

		if (self::$caching === TRUE)
		{
			// Add the path to the cache
			self::$_files[$path.($array ? '_array' : '_path')] = $found;

			// Files have been changed
			self::$_files_changed = TRUE;
		}

		return $found;
	}
	
	/**
	 * Recursively finds all of the files in the specified directory at any
	 * location in the [Cascading Filesystem](kohana/files), and returns an
	 * array of all the files found.
	 *
	 *     // Find all view files.
	 *     $views = AR::list_files('site');
	 *
	 * @param   string  directory name
	 * @param   array   list of paths to search
	 * @return  array
	 */
	public static function list_files($directory = NULL)
	{
		if ($directory !== NULL)
		{
			// Add the directory separator
			$directory .= DIRECTORY_SEPARATOR;
		}

		// Create an array for the files
		$found = array();

		if (is_dir($directory))
		{
			// Create a new directory iterator
			$dir = new DirectoryIterator($directory);

			foreach ($dir as $file)
			{
				// Get the file name
				$filename = $file->getFilename();

				if ($filename[0] === '.' OR $filename[strlen($filename)-1] === '~')
				{
					// Skip all hidden files and UNIX backup files
					continue;
				}

				// Relative filename is the array key
				$key = $directory.$filename;

				if ($file->isDir())
				{
					// Don't recurse! 
					// Use recurse_files to go through a bunch of files
				}
				else
				{
					if ( ! isset($found[$key]))
					{
						// Add new files to the list
						$found[$key] = realpath($file->getPathName());
					}
				}
			}
		}

		return $found;
	}
	
	/**
	 * PHP error handler, converts all errors into ErrorExceptions. This handler
	 * respects error_reporting settings.
	 *
	 * @throws  ErrorException
	 * @return  TRUE
	 */
	public static function error_handler($code, $error, $file = NULL, $line = NULL)
	{
		if (error_reporting() AND $code)
		{
			// This error is not suppressed by current error reporting settings
			// Convert the error into an ErrorException
			throw new ErrorException($error, $code, 0, $file, $line);
		}

		// Do not execute the PHP error handler
		return TRUE;
	}

	/**
	 * Catches errors that are not caught by the error handler, such as E_PARSE.
	 *
	 * @uses    Activerules_Exception::handler
	 * @return  void
	 */
	public static function shutdown_handler()
	{
		ob_end_clean();
		exit(1);
	}
		
	/**
	 * Loop through the modules and run their bootstratp files.
	 */
	private function _bootstrap_modules()
	{
		foreach(self::$_modules AS $module)
		{
			self::_bootstrap_module($module);
		}
	}
	
	/**
	 * Loop through the modules and run their bootstratp files.
	 */
	private function _bootstrap_module($module)
	{
		// Check to see if there is an ini file for the module
		$module_bootstrap = $module.DIRECTORY_SEPARATOR.'bootstrap'.EXT;

		if(is_file($module_bootstrap))
		{
			require_once($module_bootstrap);
		}
	}
	
} // End AR(ActiveRules) Class
