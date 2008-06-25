<?php
/**
 * @version		$Id: $
 * @package		Joomla
 * @subpackage	Messages
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.controller' );

/**
 * Messages Controller
 *
 * @package		Joomla
 * @subpackage	Messages
 * @since 1.5
 */
class MessagesController extends JController
{
	function __construct($config = array())
	{
		parent::__construct($config);

		// Register Extra tasks
		$this->registerTask( 'add',		'display' );
		$this->registerTask( 'reply',	'display' );
		$this->registerTask( 'view',	'display' );
		$this->registerTask( 'config',	'display' );
	}

	function display( )
	{
		switch($this->getTask())
		{
			case 'view'		:
			{
				JRequest::setVar( 'hidemainmenu', 1 );
				JRequest::setVar( 'view', 'message');
				JRequest::setVar( 'edit', false );
			} break;
			case 'add'		:
			{
				JRequest::setVar( 'hidemainmenu', 1 );
				JRequest::setVar( 'view', 'message');
				JRequest::setVar( 'edit', true );
				JRequest::setVar( 'reply', false );
			} break;
			case 'reply'	:
			{
				JRequest::setVar( 'hidemainmenu', 1 );
				JRequest::setVar( 'view', 'message');
				JRequest::setVar( 'edit', true );
				JRequest::setVar( 'reply', true );
			} break;
			case 'config'	:
			{
				JRequest::setVar( 'hidemainmenu', 1 );
				JRequest::setVar( 'view', 'config');
			} break;
		}

		parent::display();
	}

	function saveconfig( )
	{
		global $mainframe;
	
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );
	
		$vars = JRequest::getVar( 'vars', array(), 'post', 'array' );

		$model = $this->getModel('config');

		if ($model->store($vars)) {
			$msg = JText::_( 'Settings Saved' );
		} else {
			$msg = JText::_( 'Error Saving Settings' );
		}

		$this->setRedirect( 'index.php?option=com_messages', $msg );
	}
	
	function save( )
	{
		global $mainframe;
	
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$post	= JRequest::get('post');
		$cid	= JRequest::getVar( 'cid', array(0), 'post', 'array' );
		$post['id'] = (int) $cid[0];

		$model = $this->getModel('message');

		if ($model->send($post)) {
			$msg = JText::_( 'Message Sent' );
		} else {
			$msg = JText::_( 'Error Sending Message' );
		}

		$this->setRedirect('index.php?option=com_messages', $msg);
	}
	
	function remove( )
	{
		global $mainframe;
	
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );
	
		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to delete' ) );
		}

		$model = $this->getModel('message');
		if(!$model->delete($cid)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_messages' );
	}
}