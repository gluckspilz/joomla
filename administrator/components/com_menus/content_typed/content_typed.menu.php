<?php
/**
* @version $Id: content_typed.menu.php 185 2005-09-13 15:00:03Z stingrey $
* @package Joomla
* @subpackage Menus
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

mosAdminMenus::menuItem( $type );

$scid 	= mosGetParam( $_POST, 'scid', '' );

switch ($task) {
	case 'content_typed':
		// this is the new item, ie, the same name as the menu `type`
		content_typed_menu::edit( 0, $menutype, $option );
		break;

	case 'edit':
		content_typed_menu::edit( $cid[0], $menutype, $option );
		break;

	case 'save':
	case 'apply':
		saveMenu( $option, $task );
		break;

	case 'redirect':
		content_typed_menu::redirect( $scid );
		break;
}
?>