<?php defined('AR_VERSION') or die('No direct access');
/**
 *
 * @package ActiveRules
 * @subpackage	Helpers
 * @author     Brian Winkers
 * @copyright  (c) 2010-2011 Brian Winkers
 */
class Activerules_Dbg {
	
	/**
	 * Removes docroot from a filename,
	 * replacing them with the plain text equivalents. Useful for debugging
	 * when you want to display a shorter path.
	 *
	 *     // Displays SYSPATH/classes/kohana.php
	 *     echo Debug::path(Kohana::find_file('classes', 'kohana'));
	 *
	 * @param   string  path to debug
	 * @return  string
	 */
	public static function path($file)
	{
		if (strpos($file, DOCROOT) === 0)
		{
			$file = 'DOCROOT'.DIRECTORY_SEPARATOR.substr($file, strlen(DOCROOT));
		}
		
		return $file;
	}


	/**
	 * Echo out a debug.
	 *
	 * @var mixed variable 
	 * @var string Comment to explain what the debug contains [optional]
	 */
	public static function it($var, $comment=NULL, $position=0)
	{
		// Echo comment if one was provided
		if($comment)
		{
			echo "\n<br>".$comment;
		}
		
		//Use debug backtrace to find the file and line where this debug was called
		$trace =debug_backtrace();
		
		// Echo out the file and line 
       	echo "\n<br>Debug: ".$trace[$position]['file'].' :: '.$trace[$position]['line'];

		echo '<pre>';
		var_export($var);
		echo '</pre>';
	}
	
	/**
	 * Echo out a debug and exit.
	 * Created because I'm too lazy to type echo in front of Kohana's debug.
	 * @var mixed variable to pass into Kohana::debug
	 * @var string Comment to explain what the debug contains [optional]
	 */
	public static function ite($var, $comment=NULL, $position=0)
	{
		$position++;
		
		self::it($var, $comment, $position);
		
		exit;
	}
	
	/**
	 * Echo out framework constants
	 */
	public static function constants($position=0)
	{
		//Use debug backtrace to find the file and line where this debug was called
		$trace =debug_backtrace();
		
		// Echo out the file and line 
       	echo "\nDebug ActiveRules Constants: ".$trace[$position]['file'].' :: '.$trace[$position]['line']."<br />";
		
		echo 'APPPATH = '.APPPATH.'<br />';
		echo 'MODPATH = '.MODPATH.'</br />';
		echo 'SYSPATH = '.SYSPATH.'</br />';
		echo 'CACHEROOT = '.CACHEROOT.'</br />';
		echo 'LOGROOT = '.LOGROOT.'</br />';
		echo 'CONFIGPATH = '.CONFIGPATH.'</br />';
		echo 'SITEMOD = '.SITEMOD.'</br />';
		echo 'SITECONTENT = '.SITECONTENT.'</br />';
		echo 'ACTIVEMOD = '.ACTIVEMOD.'</br />';
		echo 'ACTIVERULES = '.ACTIVERULES.'</br />';
	}
	
	/**
	 * echo out site configs
	 */
	public static function configs($position=0)
	{
		//Use debug backtrace to find the file and line where this debug was called
		$trace =debug_backtrace();
		
		// Echo out the file and line 
       	echo "\n<br>Debug Site Configs: ".$trace[$position]['file'].' :: '.$trace[$position]['line'];
		
		echo '<pre>';
		var_export(Site::configs());
		echo '</pre>';
	}

} // End file