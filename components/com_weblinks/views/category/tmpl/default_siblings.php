<?php
/**
 * @version		$Id: default_siblings.php 12416 2009-07-03 08:49:14Z eddieajau $
 * @package		Joomla.Site
 * @subpackage	com_weblinks
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>
<?php if (empty($this->siblings)) : ?>
	no siblings
<?php else : ?>
	<h5>Siblings</h5>
	<ol>
		<?php foreach ($this->siblings as &$item) : ?>
		<li>
			<?php if ($item->id != $this->item->id) : ?>
			<a href="<?php echo JRoute::_(WeblinksRoute::category($item->slug)); ?>">
				<?php echo $this->escape($item->title); ?></a>
			<?php else : ?>
				<?php echo $this->escape($item->title); ?>
			<?php endif; ?>
		</li>
		<?php endforeach; ?>
	</ol>

<?php endif; ?>
