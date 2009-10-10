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

if ($showmode == 0 || $showmode == 2) :
    if ($count['guest'] != 0 || $count['user'] != 0) :
        echo JText::_('We have') . '&nbsp;';
		if ($count['guest'] == 1) :
		    echo JText::sprintf('guest', '1');
		else :
		    if ($count['guest'] > 1) :
			    echo JText::sprintf('guests', $count['guest']);
			endif;
		endif;

		if ($count['guest'] != 0 && $count['user'] != 0) :
		    echo '&nbsp;' . JText::_('and') . '&nbsp;';
	    endif;

		if ($count['user'] == 1) :
		    echo JText::sprintf('member', '1');
		else :
		    if ($count['user'] > 1) :
			    echo JText::sprintf('users', $count['user']);
			endif;
		endif;
		echo '&nbsp;' . JText::_('online');
    endif;
endif;

if (($showmode > 0) && count($names)) : ?>
    <ul>
<?php foreach($names as $name) : ?>
	    <li><strong><?php echo $name->username; ?></strong></li>
<?php endforeach;  ?>
	</ul>
<?php endif;