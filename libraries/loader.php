<?php
/**
* @version $Id$
* @package		Joomla.Framework
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

if(!defined('DS')) {
	define( 'DS', DIRECTORY_SEPARATOR );
}

/**
 * @package		Joomla.Framework
 */
class JLoader
{
	 /**
	 * Loads a class from specified directories.
	 *
	 * @param string $name	The class name to look for ( dot notation ).
	 * @param string $base	Search this directory for the class.
	 * @param string $key	String used as a prefix to denote the full path of the file ( dot notation ).
	 * @return void
	 * @since 1.5
	 */
	function import( $filePath, $base = null, $key = null )
	{
		static $paths;

		if (!isset($paths)) {
			$paths = array();
		}
		
		$keyPath = $key ? $key . $filePath : $filePath;
		
		if (!isset($paths[$keyPath]))
		{
			if ( ! $base ) {
				$base =  dirname( __FILE__ );
			}
			
			$parts = explode( '.', $filePath );

			$classname = array_pop( $parts );
			switch($classname)
			{
				case 'helper' :
					$classname = ucfirst(array_pop( $parts )).ucfirst($classname);
					break;
				
				default :
					$classname = ucfirst($classname);
					break;
			}
			
			//If we are loading a joomla class prepend the classname with a capital J
			if($parts[0] == 'joomla') {
				$classname = 'J'.$classname;
			}
			
			$path  = str_replace( '.', DS, $filePath );
			$trs   = JLoader::register($classname, $base.DS.$path.'.php');
			//$trs   = include($base.DS.$path.'.php');

			$paths[$keyPath] = 1;
		}
	}

    /**
     * Add a class to autoload
     *
     * @param	string $classname	The class name
     * @param	string $file		Full path to the file that holds the class
     * @return	array|boolean  		List of classnames => files, or True, false
     * @since 	1.5
     */
    function register ($class = null, $file = null)
    {
    	static $classes;

        if(!isset($classes)) {
            $classes    = array();
        }

        if($class && is_file($file))
		{
			$class = strtolower($class); //force to lower case
			$classes[$class] = $file;

			// In php4 we load the class immediately
            if((version_compare( phpversion(), '5.0' ) < 0)) {
                JLoader::load($class);
            }
        }
        return $classes;
    }


    /**
     * Load the file for a class
     *
     * @access  public
     * @param   string  $class  The class that will be loaded
     * @return  boolean True on success
     * @since   1.5
     */
    function load( $class )
    {
		$class = strtolower($class); //force to lower case
			
		if (class_exists($class)) {
      		return;
    	}

		$classes = JLoader::register();
        if(array_key_exists( strtolower($class), $classes)) {
            include($classes[$class]);
            return true;
        }
        return false;
    }
}


/**
 * When calling a class that hasn't been defined, __autoload will attempt to
 * include the correct file for that class. 
 * 
 * This function get's called by PHP. Never call this function yourself.
 *
 * @param 	string 	$class
 * @access 	public
 * @return  boolean
 * @since   1.5
 */
function __autoload($class)
{
	if(JLoader::load($class)) {
		return true;
	}

	return false;
}


/**
 * Intelligent file importer
 *
 * @access public
 * @param string $path A dot syntax path
 * @since 1.5
 */
function jimport( $path ) {
	return JLoader::import($path, null, 'libraries.' );
}