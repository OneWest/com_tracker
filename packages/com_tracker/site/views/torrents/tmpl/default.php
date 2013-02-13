<?php
/**
 * @version			2.5.0
 * @package			Joomla
 * @subpackage	com_tracker
 * @copyright		Copyright (C) 2007 - 2012 Hugo Carvalho (www.visigod.com). All rights reserved.
 * @license			GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.helper');
jimport( 'joomla.html.parameter' );

if ($this->user->guest && $this->params->get('allow_guest')) :
	$this->user->load($this->params->get('guest_user'));
endif;

$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));

?>

<form action="<?php echo JRoute::_('index.php?option=com_tracker&view=torrents'); ?>" method="post" name="adminForm">
	<fieldset id="filter-bar">
		<div class="filter-search fltlft">
			<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('Search'); ?>" />
			<button type="submit"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
			<button type="button" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
		</div>

		<div class="filter-category">
			<select name="filter_category_id" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_CATEGORY');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('category.options', 'com_tracker'), 'value', 'text', $this->state->get('filter.category_id'));?>
			</select>
		</div>

<!-- TODO: Dropdown for licenses -->

	</fieldset>

	<div class="clr"> </div>

	<table class="adminlist" style="width:100%;">
		<thead>
			<tr>
				<th width="1%" align='center'><?php echo JHtml::_('grid.sort',	'COM_TRACKER_TORRENT_NAME', 't.name', $listDirn, $listOrder); ?></th>
				<th width="1%" align='center' nowrap>&nbsp;<?php echo JHtml::_('grid.sort',	'JCATEGORY', 'c.title', $listDirn, $listOrder); ?>&nbsp;</th>
				<th width="1%" align='center' nowrap><?php echo JHtml::_('grid.sort',	'COM_TRACKER_TORRENT_SIZE', 't.size', $listDirn, $listOrder); ?></th>
				<th width="1%" align='center' nowrap><?php echo JHtml::_('grid.sort',	'COM_TRACKER_TORRENT_CREATED_TIME', 't.created_time', $listDirn, $listOrder); ?></th>
				<th width="1%" align='center' nowrap>&nbsp;<?php echo JHtml::_('grid.sort',	'COM_TRACKER_TORRENT_LEECHERS_SMALL', 't.leechers', $listDirn, $listOrder); ?>&nbsp;</th>
				<th width="1%" align='center' nowrap>&nbsp;<?php echo JHtml::_('grid.sort',	'COM_TRACKER_TORRENT_SEEDERS_SMALL', 't.seeders', $listDirn, $listOrder); ?>&nbsp;</th>
				<th width="1%" align='center' nowrap>&nbsp;<?php echo JHtml::_('grid.sort',	'COM_TRACKER_TORRENT_COMPLETED_SMALL', 't.completed', $listDirn, $listOrder); ?>&nbsp;</th>
				<th width="1%" align='center' nowrap>&nbsp;<?php echo JHtml::_('grid.sort',	'COM_TRACKER_TORRENT_UPLOADER', 'torrent_owner', $listDirn, $listOrder); ?>&nbsp;</th>
				<?php if (TrackerHelper::user_permissions('download_torrents', $this->user->id)) { ?>
					<th width="1%" class='align'>DL</th>
				<?php } ?>
			</tr>
		</thead>

		<tbody>
		<?php foreach ($this->items as $i => $item) :
			$ordering	= ($listOrder == 'a.ordering');
			$canCreate	= $this->user->authorise('core.create',		'com_tracker');
			$canEdit	= $this->user->authorise('core.edit',			'com_tracker');
			$canCheckin	= $this->user->authorise('core.manage',		'com_tracker');
			$canChange	= $this->user->authorise('core.edit.state',	'com_tracker');
			$category_params = new JParameter( $item->category_params );
			?>
			<tr class="row<?php echo $i % 2; ?>" style="width:90%;">
				<td width="1%">
					<a href="<?php echo JRoute::_("index.php?option=com_tracker&view=torrent&id=".(int)$item->fid); ?>">
					<?php echo $this->escape(str_replace('_', ' ', $item->name)); ?>
					</a>
				</td>
				<td width="1%" align="center">
					<?php if (is_file($_SERVER['DOCUMENT_ROOT'].JUri::root(true).DS.$category_params->get('image'))) { ?>
						<img id="image<?php echo $item->fid;?>" alt="<?php echo $item->torrent_category; ?>" src="<?php echo JUri::root(true).DS.$category_params->get('image'); ?>" width="36" />
					<?php }
						else echo '&nbsp;'.$item->torrent_category.'&nbsp;';
						?>
				</td>
				<td align="right" nowrap>&nbsp;<?php echo TrackerHelper::make_size($item->size);?>&nbsp;</td>
				<td align="right" nowrap>&nbsp;<?php echo date('Y.m.d', strtotime($item->created_time));?>&nbsp;</td>
				<td align="center" nowrap>&nbsp;<?php echo $item->leechers;?>&nbsp;</td>
				<td align="center" nowrap>&nbsp;<?php echo $item->seeders;?>&nbsp;</td>
				<td align="center" nowrap>&nbsp;<?php echo $item->completed;?>&nbsp;</td>

				<td align="right" nowrap>&nbsp;
				<?php 
				//echo $item->torrent_owner;
				if (($this->params->get('allow_upload_anonymous') == 0) || ($item->uploader_anonymous == 0) || ($item->uploader == $this->user->id)) echo $item->torrent_owner;
				else echo JText::_( 'COM_TRACKER_TORRENT_ANONYMOUS' );
				?>&nbsp;</td>
				<?php if (TrackerHelper::user_permissions('download_torrents', $this->user->id)) { ?>
					<td width="1%" align="center">
						<a href="<?php echo JRoute::_("index.php?option=com_tracker&task=torrent.download&id=".$item->fid); ?>">
							<img src="<?php echo JURI::base();?>components/com_tracker/assets/images/download.gif" alt="<?php echo JText::_( 'TORRENT_DOWNLOAD_TORRENT_LIST_ALT' ); ?>" border="0" />
						</a>
					</td>
				<?php } ?>
					
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<div class="pagination">
			<?php echo $this->pagination->getLimitBox(); ?>
			<?php echo $this->pagination->getPagesCounter(); ?>
			<?php echo $this->pagination->getPagesLinks(); ?>
	</div>

	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>