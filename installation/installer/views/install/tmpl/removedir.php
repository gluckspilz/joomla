<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Installation
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Set our step information to render in the template
JRequest::setVar('step', 'remove');

?>

<div>
	<table class="final-table">
	<tr>
		<td class="error" align="center">
			<?php echo JText::_('removeInstallation') ?>
		</td>
	</tr>
	<tr>
		<td class="error" align="center">
			<a href="../"><?php echo JText::_('installationRemoved') ?></a>
		</td>
	</tr>
	</table>
</div>