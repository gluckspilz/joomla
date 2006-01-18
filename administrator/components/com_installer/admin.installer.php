<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Installer
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.installer.installer');

require_once ($mainframe->getPath('admin_html'));

$extension 	= mosGetParam($_REQUEST, 'extension', '');
$client 	= mosGetParam($_REQUEST, 'client', '');

// ensure user has access to this function
if (!$acl->acl_check('com_installer', 'installer', 'users', $my->usertype)) {
	mosRedirect('index2.php', JText :: _('ALERTNOTAUTH'));
}

/*
 * We need to load the appropriate tmpl file
 */
if ($extension != '') {
	$path = dirname(__FILE__).DS.'tmpl'.DS.$extension.'.php';
	
	if (file_exists($path)) {
		require_once($path);
	}
}


class JInstallerController {

	/**
	 * @param string The class name for the installer
	 */
	function uploadpackage()
	{
		global $mainframe;

		/*
		 * Get a database connector
		 */
		$db =& $mainframe->getDBO();

		/*
		 * Get the uploaded file information
		 */
		$userfile = mosGetParam($_FILES, 'userfile', null);
		$baseDir = JPath :: clean(JPATH_SITE.DS.'media');

		/*
		 * Make sure that file uploads are enabled in php
		 */
		if (!(bool) ini_get('file_uploads')) {
			JError::raiseError('SOME_ERROR_CODE', JText :: _('WARNINSTALLFILE'));
			JInstallerScreens :: showInstallForm();
			return false;
		}

		/*
		 * Make sure that zlib is loaded so that the package can be unpacked
		 */
		if (!extension_loaded('zlib')) {
			JError::raiseError('SOME_ERROR_CODE', JText :: _('WARNINSTALLZLIB'));
			JInstallerScreens :: showInstallForm();
			return false;
		}

		/*
		 * If there is no uploaded file, we have a problem...
		 */
		if (!$userfile) {
			JError::raiseError('SOME_ERROR_CODE', JText :: _('No file selected'));
			JInstallerScreens :: showInstallForm();
			return false;
		}

		/*
		 * Move uploaded file
		 */
		$uploaded = JFile :: upload($userfile['tmp_name'], $baseDir.$userfile['name']);

		/*
		 * Unpack the downloaded package file
		 */
		$package = JInstallerHelper :: unpack($userfile['name']);

		/*
		 * Was the package unpacked?
		 */
		if (!$package) {
			JInstallerScreens :: showInstallForm();
			return false;
		}

		/*
		 * Get an installer instance
		 */
		$installer =& JInstaller :: getInstance($db, $package['type']);

		/*
		 * Install the package
		 */
		if (!$installer->install($package['dir'])) {
			/*
			 * There was an error in installing the package
			 */
			$msg = sprintf(JText :: _('INSTALLEXT'), $package['type'], JText::_('Error'));
			JInstallerScreens::showInstallMessage($msg, $installer->i_description, $installer->i_message);
			// Cleanup the install files
			JInstallerHelper :: cleanupInstall($baseDir.$userfile['name'], $package['extractdir']);
		} else {
			/*
			 * Package installed sucessfully
			 */
			$msg = sprintf(JText :: _('INSTALLEXT'), $package['type'], JText::_('Success'));
			JInstallerScreens::showInstallMessage($msg, $installer->i_description, $installer->i_message);
			// Cleanup the install files
			JInstallerHelper :: cleanupInstall($baseDir.$userfile['name'], $package['extractdir']);
		}
	}

	/**
	 * Install an extension from a directory
	 * 
	 * @static
	 * @return boolean True on success
	 * @since 1.0
	 */
	function installFromDirectory()
	{
		global $mainframe;
		
		/*
		 * Get a database connector
		 */
		$db =& $mainframe->getDBO();
		
		/*
		 * Get the path to the package to install
		 */
		$p_dir = mosGetParam($_REQUEST, 'userfile', '');

		/*
		 * Did you give us a valid directory?
		 */
		if (!is_dir($p_dir)) {
			josRedirect("index2.php?option=com_installer", JText :: _('Please enter a package directory'));
		}

		/*
		 * Detect the package type
		 */
		$type = JInstallerHelper :: detectType($p_dir);

		/*
		 * Did you give us a valid directory?
		 */
		if (!$type) {
			josRedirect("index2.php?option=com_installer", JText :: _('Path does not have a valid package'));
		}

		/*
		 * Get an installer instance
		 */
		$installer =& JInstaller :: getInstance($db, $type);

		/*
		 * Install the package
		 */
		if (!$installer->install($p_dir)) {
			/*
			 * There was an error in installing the package
			 */
			$msg = sprintf(JText :: _('INSTALLEXT'), $package['type'], JText::_('Error'));
			JInstallerScreens::showInstallMessage($msg, $installer->i_description, $installer->i_message);
		} else {
			/*
			 * Package installed sucessfully
			 */
			$msg = sprintf(JText :: _('INSTALLEXT'), $package['type'], JText::_('Success'));
			JInstallerScreens::showInstallMessage($msg, $installer->i_description, $installer->i_message);
		}
	}

	/**
	 * Install an extension from a URL
	 * 
	 * @static
	 * @return boolean True on success
	 * @since 1.1
	 */
	function installFromUrl()
	{
		global $mainframe;
		
		/*
		 * Get a database connector
		 */
		$db =& $mainframe->getDBO();
		
		/*
		 * Get the URL of the package to install
		 */
		$url = mosGetParam($_REQUEST, 'userfile', '');

		/*
		 * Did you give us a URL?
		 */
		if (!$url) {
			josRedirect("index2.php?option=com_installer", JText :: _('Please enter a URL'));
		}

		/*
		 * Download the package at the URL given
		 */
		$p_file = JInstallerHelper :: downloadPackage($url);

		/*
		 * Was the package downloaded?
		 */
		if (!$p_file) {
			josRedirect("index2.php?option=com_installer", JText :: _('Invalid URL'));
		}

		/*
		 * Unpack the downloaded package file
		 */
		$package = JInstallerHelper :: unpack($p_file);

		/*
		 * Was the package unpacked?
		 */
		if (!$package) {
			JInstallerScreens :: showInstallForm();
			return false;
		}

		/*
		 * Get an installer instance
		 */
		$installer =& JInstaller :: getInstance($db, $package['type']);

		/*
		 * Install the package
		 */
		if (!$installer->install($package['dir'])) {
			/*
			 * There was an error in installing the package
			 */
			$msg = sprintf(JText :: _('INSTALLEXT'), $package['type'], JText::_('Error'));
			JInstallerScreens::showInstallMessage($msg, $installer->i_description, $installer->i_message);
			// Cleanup the install files
			JInstallerHelper :: cleanupInstall($p_file, $package['extractdir']);
		} else {
			/*
			 * Package installed sucessfully
			 */
			$msg = sprintf(JText :: _('INSTALLEXT'), $package['type'], JText::_('Success'));
			JInstallerScreens::showInstallMessage($msg, $installer->i_description, $installer->i_message);
			// Cleanup the install files
			JInstallerHelper :: cleanupInstall($p_file, $package['extractdir']);
		}
	}

	/**
	 * Enable an extension
	 * 
	 * @static
	 * @return boolean True on success
	 * @since 1.0
	 */
	function enableextension()
	{
		global $mainframe;
		
		/*
		 * Get a database connector
		 */
		$db =& $mainframe->getDBO();
		
		/*
		 * Initialize variables
		 */
		$eid = JRequest::getVar( 'eid', array(0), '', 'array' );
		$extension = JRequest::getVar( 'extension' );
		$result = false;

		/*
		 * Get the extension client
		 * 
		 * Defaults to 'site'
		 * Set 'admin' to 'administrator'
		 */
		$client = JRequest::getVar( 'client', 'site' );
		if ($client == '') {
			$client = 'site';
		}
		if ($client == 'admin') {
			$client = 'administrator';
		}
		
		
		/*
		 * Ensure eid is an array of extension ids
		 * TODO: If it isn't an array do we want to set an error and fail?  
		 */
		if (!is_array($eid)) {
			$eid = array ($eid);
		}

		/*
		 * Get a model object for the extension type
		 */
		$model = & JModel :: getInstance( $extension, $db );

		/*
		 * Disable the extension in the model and store it in the database
		 */
		foreach ($eid as $id) {
			$model->load($id);
			$model->enabled = '1';
			$model->store();
		}

		/*
		 * Display the extension management screen
		 */
		JInstallerExtensionTasks::showInstalled();		
	}

	/**
	 * Disable an extension
	 * 
	 * @static
	 * @return boolean True on success
	 * @since 1.1
	 */
	function disableextension()
	{
		global $mainframe;
		
		/*
		 * Get a database connector
		 */
		$db =& $mainframe->getDBO();
		
		/*
		 * Initialize variables
		 */
		$eid = JRequest::getVar( 'eid', array(0), '', 'array' );
		$extension = JRequest::getVar( 'extension' );
		$result = false;

		/*
		 * Get the extension client
		 * 
		 * Defaults to 'site'
		 * Set 'admin' to 'administrator'
		 */
		$client = JRequest::getVar( 'client', 'site' );
		if ($client == '') {
			$client = 'site';
		}
		if ($client == 'admin') {
			$client = 'administrator';
		}
		
		
		/*
		 * Ensure eid is an array of extension ids
		 * TODO: If it isn't an array do we want to set an error and fail?  
		 */
		if (!is_array($eid)) {
			$eid = array ($eid);
		}

		/*
		 * Get a model object for the extension type
		 */
		$model = & JModel :: getInstance( $extension, $db );

		/*
		 * Disable the extension in the model and store it in the database
		 */
		foreach ($eid as $id) {
			
			$model->load($id);
			$model->enabled = '0';
			$model->store();
		}

		/*
		 * Display the extension management screen
		 */
		JInstallerExtensionTasks::showInstalled();		
	}

	/**
	 * Remove (uninstall) an extension
	 * 
	 * @static
	 * @return boolean True on success
	 * @since 1.0
	 */
	function removeextension()
	{
		global $mainframe;
		
		/*
		 * Get a database connector
		 */
		$db =& $mainframe->getDBO();
		
		/*
		 * Initialize variables
		 */
		$eid = mosGetParam($_REQUEST, 'eid', array (0));
		$extension = mosGetParam($_REQUEST, 'extension', '');
		$result = false;

		/*
		 * Get the extension client
		 * 
		 * Defaults to 'site'
		 * Set 'admin' to 'administrator'
		 */
		$client = mosGetParam($_REQUEST, 'client', 'site');
		if ($client == '') {
			$client = 'site';
		}
		if ($client == 'admin') {
			$client = 'administrator';
		}
		
		
		/*
		 * Ensure eid is an array of extension ids
		 * TODO: If it isn't an array do we want to set an error and fail?  
		 */
		if (!is_array($eid)) {
			$eid = array ($eid);
		}

		/*
		 * Get an installer object for the extension type
		 */
		$installer = & JInstaller :: getInstance($db, $extension);

		/*
		 * Uninstall the chosen extensions
		 */
		foreach ($eid as $id) {
			$result = $installer->uninstall($id, $client);
			
			// Build an array of extensions that failed to uninstall
			if ($result === false) {
				$failed[] = $id;
			}
		}

		if (count($failed)) {
			/*
			 * There was an error in uninstalling the package
			 */
			$msg = sprintf(JText :: _('UNINSTALLEXT'), $extension, JText::_('Error'));
			JInstallerScreens::showInstallMessage($msg, $installer->i_description, $installer->i_message);
		} else {
			/*
			 * Package uninstalled sucessfully
			 */
			$msg = sprintf(JText :: _('UNINSTALLEXT'), $extension, JText::_('Success'));
			JInstallerScreens::showInstallMessage($msg, $installer->i_description, $installer->i_message);
		}
	}


	/**
	 * Unified intaller
	 */
	function installer()
	{

		/*
		 * Initialize variables
		 */
		$extension = mosGetParam($_REQUEST, 'extension', '');
		
		if ($extension != '') {
			JInstallerExtensionTasks::showInstalled();
		} else {
			JInstallerScreens :: showInstallForm();
		}
	}
}

// Get the task from the request array
$task = strtolower(mosGetParam($_REQUEST, 'task', 'installer'));

switch ($task) {

	case 'uploadpackage':
		JInstallerController :: uploadpackage();
		break;
	case 'installfromdirectory':
		JInstallerController :: installFromDirectory();
		break;
	case 'installfromurl':
		JInstallerController :: installFromUrl();
		break;
	case 'enable':
		JInstallerController :: enableextension();
		break;
	case 'disable':
		JInstallerController :: disableextension();
		break;
	case 'remove':
	case 'removeextension':
		JInstallerController :: removeextension();
		break;
	default:
		JInstallerController :: installer();
		break;
}	
?>