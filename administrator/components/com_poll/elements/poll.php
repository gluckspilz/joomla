<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class JElement_Poll extends JElement
{
   /**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'Poll';

	function fetchElement($name, $value, &$node, $control_name)
	{
		$db =& JFactory::getDBO();

		$query = "SELECT a.id, a.title"
		. "\n FROM #__polls AS a"
		. "\n WHERE a.published = 1"
		. "\n ORDER BY a.title"
		;
		$db->setQuery( $query );
		$options = $db->loadObjectList();

		array_unshift($options, JHTMLSelect::option('0', '- '.JText::_('Select Poll').' -', 'id', 'title'));

		return JHTMLSelect::genericList($options, ''.$control_name.'['.$name.']', 'class="inputbox"', 'id', 'title', $value, $control_name.$name );
	}
}
?>
