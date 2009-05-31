<?php
/**
 * Document Description
 * 
 * Document Long Description 
 * 
 * PHP4/5
 *  
 * Created on Oct 30, 2008
 * 
 * @package Joomla.Framework
 * @subpackage Database
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 * @version SVN: $Id$
 */

// No direct access
defined('JPATH_BASE') or die();

abstract class JDataLoad extends JObject {
	
	abstract public function load();
	
	public static function &getInstance($options = array())
	{
		static $instances;

		if (!isset( $instances )) {
			$instances = array();
		}

		$signature = serialize( $options );

		if (empty($instances[$signature]))
		{
			$driver		= array_key_exists('driver', $options) 		? $options['driver']	: 'sql';
			$filename	= array_key_exists('filename', $options)	? $options['filename']	: null;

			$driver = preg_replace('/[^A-Z0-9_\.-]/i', '', $driver);
			$path	= dirname(__FILE__).DS.'loader'.DS.$driver.'.php';

			if (file_exists($path)) {
				require_once $path;
			} else {
				JError::setErrorHandling(E_ERROR, 'die'); //force error type to die
				$error = JError::raiseError( 500, JTEXT::_('Unable to load Data Load Driver:') .$driver);
				return $error;
			}
			$adapter	= 'JDataLoader'.$driver;
			$instance	= new $adapter($options);

			if ( $error = $instance->getError() )
			{
				JError::setErrorHandling(E_ERROR, 'ignore'); //force error type to die
				$error = JError::raiseError( 500, JTEXT::_('Unable to instantiate data load driver:') .$error);
				return $error;
			}


			$instances[$signature] = & $instance;
		}

		return $instances[$signature];
	}
}
 