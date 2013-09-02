<?php
namespace ActiveRules\Core;

/**
 * Debug library
 * 
 * Provides safe debug facilities to ActiveRules
 * 
 * @package ActiveRules
 * @package	Core
 * @author Brian Winkers
 * @copyright (c) 2005-2013 Brian Winkers
 * 
 */

class Debug {	
    
	/**
	 * Echo out a debug.
	 *
	 * @var mixed variable 
	 * @var string Comment to explain what the debug contains [optional]
	 */
	static public function it($var, $comment=NULL, $position=0)
	{
		// Echo comment if one was provided
		if($comment)
		{
			echo "\n<br>".$comment;
		}
		
		//Use debug backtrace to find the file and line where this debug was called
		$trace = debug_backtrace();
		
		// Echo out the file and line 
       	echo "\n<br>Debug: ".$trace[$position]['file'].' :: '.$trace[$position]['line'];

		echo '<pre>';
		var_export($var);
		echo '</pre>';
	}

}