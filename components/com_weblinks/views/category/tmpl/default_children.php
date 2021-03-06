<?php
/**
 * @version		$Id: default_children.php 12812 2009-09-22 03:58:25Z dextercowley $
 * @package		Joomla.Site
 * @subpackage	com_weblinks
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>
<?php if (empty($this->children)) : ?>
<p><?php  echo JText::_('JContent_No_Children'); ?></p>
<?php else : ?>
	<h3><?php  echo JText::_('JContent_Children'); ?></h3>
<?php
	// Initialise the starting level
	// starting level is the parent level coming in
	$curLevel = $this->item->level;
	$difLevel = 0;

	// Loop through each of the children
	foreach ($this->children as &$item) :
	// Create an <ul> for every level going deeper
	// and an </ul> for every level jumping back up
	// set current level to the new level
		$difLevel = $item->level - $curLevel;
		if ($difLevel < 0) :
			for ($i = 0, $n = -($difLevel); $i < $n; $i++) :
				echo "</ul>";
			endfor;
			$curLevel = $item->level;
		elseif ($difLevel > 0) :
			for ($i = 0, $n = $difLevel; $i < $n; $i++) : ?>
				<ul>
			<?php endfor;
			$curLevel = $item->level;
		endif;
?>

		<li>
			<a href="<?php echo JRoute::_(WeblinkRoute::category($item->slug)); ?>">
				<?php echo $this->escape($item->title); ?></a>
		</li>
		<?php endforeach; ?>

	</ul>
<?php endif; ?>