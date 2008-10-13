<?php /** $Id$ */ defined('_JEXEC') or die('Restricted access');

	JHTML::addIncludePath(JPATH_COMPONENT.DS.'helpers'.DS.'html');
	JHTML::_('behavior.tooltip');
?>
<style type="text/css">
/* @TODO Mode to stylesheet */
.scroll {
	overflow: auto;
}
</style>
<div>
	<?php echo JText::_('ACL Rules View Desc'); ?>
</div>

<form action="<?php echo JRoute::_( 'index.php?option=com_acl&view=rules' );?>" method="post" name="adminForm">
	<fieldset class="filter clearfix">
		<div class="left">
			<label for="search"><?php echo JText::_( 'Search' ); ?>:</label>
			<input type="text" name="search" id="search" value="<?php echo $this->state->get('list.search'); ?>" size="60" title="<?php echo JText::_( 'Search in note' ); ?>" />
			<button type="submit"><?php echo JText::_( 'Search Go' ); ?></button>
			<button type="button" onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'Search Clear' ); ?></button>
		</div>
	</fieldset>
	<table class="adminlist">
		<thead>
			<tr>
				<th width="20">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->items );?>)" />
				</th>
				<th class="left">
					<?php echo JHTML::_( 'grid.sort', 'ACL Col Note', 'a.note', $this->state->orderDirn, $this->state->orderCol ); ?>
				</th>
				<th nowrap="nowrap" align="center">
					<?php echo JText::_( 'ACL Col User Groups' ); ?>
				</th>
				<th nowrap="nowrap" align="center">
					<?php echo JText::_( 'ACL Col Permissions' ); ?>
				</th>
				<th width="5%">
					<?php echo JHTML::_( 'grid.sort', 'ACL Col Allowed', 'a.allow', $this->state->orderDirn, $this->state->orderCol ); ?>
				</th>
				<th nowrap="nowrap" width="5%">
					<?php echo JHTML::_( 'grid.sort', 'ACL Col Enabled', 'a.enabled', $this->state->orderDirn, $this->state->orderCol ); ?>
				</th>
				<th nowrap="nowrap" width="1%" align="center">
					<?php echo JText::_( 'Col ID' ); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="15">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php
			$i = 0;
			foreach ($this->items as $item) : ?>
			<tr class="row<?php echo $i % 2; ?>">
				<td style="text-align:center">
					<?php echo JHTML::_( 'grid.id', $i++, $item->id ); ?>
				</td>
				<td>
					<a href="<?php echo JRoute::_( 'index.php?option=com_acl&task=acl.edit&id='.$item->id );?>">
						<?php echo $item->note; ?></a>
				</td>
				<td align="left" valign="top">
					<div class="scroll" style="height: 75px;">
				<?php
					if (isset( $item->aros )) :
						foreach ($item->aros as $section => $aros) : ?>
							<strong><?php echo $section;?></strong>
							<?php if (count( $aros )) : ?>
								<ol>
									<?php foreach ($aros as $name) : ?>
									<li>
										<?php echo $name; ?>
									</li>
									<?php endforeach; ?>
								</ol>
							<?php endif;
						endforeach;
					endif;

					if (isset( $item->aroGroups ) && count( $item->aroGroups )) : ?>
						<ol>
							<?php foreach ($item->aroGroups as $name) : ?>
							<li>
								<?php echo $name; ?>
							</li>
							<?php endforeach; ?>
						</ol>
					<?php
					endif;
				?>
					</div>
				</td>
				<td align="left" valign="top">
				<?php if (isset($item->acos) AND !empty($item->acos)) : ?>
					<div class="scroll" style="height: 75px;">
						<ol>
					<?php foreach ($item->acos as $section => $acos) : ?>
							<?php if (count( $acos )) : ?>
								<?php foreach ($acos as $name) : ?>
								<li>
									<strong><?php echo $section; ?></strong>:
									<?php echo $name; ?>
								</li>
								<?php endforeach; ?>
							<?php endif;
						endforeach; ?>
						</ol>
					</div>
				<?php endif; ?>
				</td>
				<td align="center">
					<?php echo JHTML::_( 'acl.allowed', $item->allow, $item->id ); ?>
				</td>
				<td align="center">
					<?php echo JHTML::_( 'acl.enabled', $item->enabled, $item->id ); ?>
				</td>
				<td align="center">
					<?php echo $item->id; ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->state->get('orderCol'); ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->state->get('orderDirn'); ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
