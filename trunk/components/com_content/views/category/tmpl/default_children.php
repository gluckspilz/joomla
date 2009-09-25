<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>
<?php if (empty($this->children)) : ?>
	<!-- no children  -->
<?php else : ?>
	<h5>Sub Categories</h5>
<?php
	// Initialize the starting level
	// starting level is the parent level coming in 	
	$curLevel = $this->item->level;
	$difLevel = 0;
	
	// Loop through each of the children
	foreach ($this->children as &$item) : 
	// Create an <ol> for every level going deeper
	// and an </ol> for every level jumping back up
	// set current level to the new level
		$difLevel = $item->level - $curLevel;
		if ($difLevel < 0) :
			for ($i = 0, $n = -($difLevel); $i < $n; $i++) :
				echo "</ol>";
			endfor;
			$curLevel = $item->level;
		elseif ($difLevel > 0) :
			for ($i = 0, $n = $difLevel; $i < $n; $i++) : ?>
				<ol>
			<?php endfor;
			$curLevel = $item->level;
		endif;
?>
		
		<li>
			<a href="<?php echo JRoute::_(ContentRoute::category($item->slug)); ?>">
				<?php echo $this->escape($item->title); ?></a>
		</li>
		<?php endforeach; ?>

	</ol>
<?php endif; ?>