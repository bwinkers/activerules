<?php
/**
 * AR (ActiveRules) library.
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
	public static $site = NULL;

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
	
	public function __construct($config_array)
	{
		// Set the AR storage if defined
		if(isset($config_array['config_storage']))
		{
			$this->set_storage($config_array['config_storage']);
		}

		// Set the AR cacher if defined
		if(isset($config_array['cacher']))
		{
			$this->set_cacher($config_array['cacher']);
		}

		// Set the AR logger if defined
		if(isset($config_array['logger']))
		{
			$this->set_logger($config_array['logger']);
		}
	}
	
	/**
	 * Start the ActiveRules processing
	 */
	public function load_site()
	{
		/**
		 * Create the Site class.
		 * If we passed in a site name it would load that site.
		 */
		$site = new Site();
	
		/** 
		 * Pass the ActiveRules config object onto the site storage class.
		 * This is the primary purpose of defining the AR storage.
		 * It allows us to look up basic site configuration.
		 */
		$site->set_storage(self::$_storage);

		/**
		 * Load the site
		 */
		$site->init_site();
		
		/**
		 * Load the modules from the Site host config
		 */
		$modules = $site->get_modules();
		
		if($modules)
		{
			foreach($modules as $module)
			{
				$mod_path = DOCROOT.'modules'.DIRECTORY_SEPARATOR.$module;
				
				$this->add_module($mod_path);
			}
		}
		
		// Create a new Request object
		$request = new Request;
		
		// Bootstrap the modules
		// We wait untill all modules are loaded to reduce issues with laod order dependencies.
		$this->_bootstrap_modules();
	}
	
	public static function add_module($module)
	{
		if(is_dir($module))
		{  		
			self::$_paths[] = $module;
			self::$_modules[] = $module;
		}
	}
	
	private function _bootstrap_modules()
	{
		foreach(self::$_modules AS $module)
		{
			// Check to see if there is an ini file for the module
			$module_bootstrap = $module.DIRECTORY_SEPARATOR.'bootstrap'.EXT;

			if(is_file($module_bootstrap))
			{
				require_once($module_bootstrap);
			}
		}
	}
	
	/**
	 * Set the storage method used for site configs
	 */
	public function set_storage($storage)
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
	 * Set the cache method used for site configs
	 */
	public function cacher($cacher)
	{
		switch($cacher)
		{
			default:
				echo $cacher;
				break;
		}
	}
	
	/**
	 * Set the logger method used for site configs
	 */
	public function logger($logger)
	{
		switch($logger)
		{
			default:
				echo $logger;
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
	 * This function must be enabled as an autoloader in the bootstrap:
	 *
	 *     spl_autoload_register(array('AR', 'autoload'));
	 *
	 * @param   string   class name
	 * @return  boolean
	 */
	public static function autoload($class)
	{
		try
		{
			// Transform the class name into a path
			$file = str_replace('_', '/', strtolower($class));

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
		catch (Exception $e)
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

			foreach ($places as $dir)
			{
				if (is_file($dir.$path))
				{
					//echo '<br>full: '.$dir.$path;
					// This path has a file, add it to the list
					$found[] = $dir.$path;
				}
			}
		}
		else
		{
			// The file has not been found yet
			$found = FALSE;

			foreach ($places as $dir)
			{
				if (is_file($dir.$path))
				{
					//echo '<br>full: '.$dir.$path;
					// A path has been found
					$found = $dir.$path;

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


	
} // End AR(ActiveRules) Class
