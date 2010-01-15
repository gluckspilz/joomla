<?php
/**
 * @version		$Id: default.php 14001 2010-01-04 22:42:12Z eddieajau $
 * @package		Joomla.Administrator
 * @subpackage	mod_unread
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

// Set the inbox class.
$inboxClass = $unread ? 'unread-messages' : 'no-unread-messages';
?>
<?php if (!empty($inboxLink)) : ?>
	<span class="<?php echo $inboxClass;?>"><a href="<?php echo $inboxLink;?>"><?php echo JText::sprtinf('mod_unread_Messages', $unread);?></a></span>
<?php else : ?>
	<span class="<?php echo $inboxClass;?>"><?php echo JText::sprintf('mod_unread_Messages', $unread);?></span>
<?php endif; ?>