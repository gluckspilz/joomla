<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_categories
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
?>

<script type="text/javascript">
<!--
	function submitbutton(task)
	{
		if (task == 'category.cancel' || document.formvalidator.isValid(document.id('item-form'))) {
			submitform(task);
		}
	}
// -->
</script>

<form action="<?php JRoute::_('index.php?option=com_menus'); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
	<div class="width-60 fltlft">
		<fieldset class="adminform">
			<legend><?php echo JText::_('CATEGORIES_FIELDSET_DETAILS');?></legend>

			<?php echo $this->form->getLabel('title'); ?>
			<?php echo $this->form->getInput('title'); ?>

			<?php echo $this->form->getLabel('alias'); ?>
			<?php echo $this->form->getInput('alias'); ?>

			<?php echo $this->form->getLabel('note'); ?>
			<?php echo $this->form->getInput('note'); ?>

			<?php echo $this->form->getLabel('extension'); ?>
			<?php echo $this->form->getInput('extension'); ?>

			<?php echo $this->form->getLabel('parent_id'); ?>
			<?php echo $this->form->getInput('parent_id'); ?>

			<?php echo $this->form->getLabel('published'); ?>
			<?php echo $this->form->getInput('published'); ?>

			<?php echo $this->form->getLabel('access'); ?>
			<?php echo $this->form->getInput('access'); ?>

			<?php echo $this->form->getLabel('language'); ?>
			<?php echo $this->form->getInput('language'); ?>

			<div class="clr"></div>
			<?php echo $this->form->getLabel('description'); ?>
			<div class="clr"></div>
			<?php echo $this->form->getInput('description'); ?>
		</fieldset>
	</div>

	<div class="width-40 fltrt">
	<?php
		if(in_array('params', $this->form->getGroups()))
		{
			echo JHTML::_('sliders.start');
			$groups = $this->form->getGroups('params');
			$fieldsets = $this->form->getFieldsets();
			array_unshift($groups, 'params');
			foreach($groups as $group) {
				echo JHTML::_('sliders.panel', JText::_($fieldsets[$group]['label']), $group);
				echo '<fieldset class="panelform">';
				foreach($this->form->getFields($group) as $field)
				{
					if ($field->hidden)
					{
						echo $field->input;
					} else {
						echo $field->label;
						echo $field->input;
					}
				}
				echo '</fieldset>';
			}
			echo JHTML::_('sliders.end');
		} ?>
		<fieldset class="adminform">
			<legend><?php echo JText::_('CATEGORIES_FIELDSET_METADATA'); ?></legend>
			<?php echo $this->loadTemplate('metadata'); ?>
		</fieldset>
		<fieldset>
			<legend><?php echo JText::_('CATEGORIES_FIELDSET_RULES');?></legend>
				<?php echo $this->form->getLabel('rules'); ?>
				<?php echo $this->form->getInput('rules'); ?>
		</fieldset>
	</div>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
<div class="clr"></div>
