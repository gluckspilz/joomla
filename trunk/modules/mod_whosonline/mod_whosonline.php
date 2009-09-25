<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	mod_whosonline
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Include the whosonline functions only once
require_once dirname(__FILE__).DS.'helper.php';

$showmode = $params->get('showmode', 0);

if ($showmode == 0 || $showmode == 2) {
    $count 	= modWhosonlineHelper::getOnlineCount();
}

if ($showmode > 0) {
    $names 	= modWhosonlineHelper::getOnlineUserNames();
}

require JModuleHelper::getLayoutPath('mod_whosonline');