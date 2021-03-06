<?php
/**
 * @package Let'em pay for Joomla! 3.1
 * @author Ing. Peter Vavro
 * @copyright (C) 2013 - Ing. Peter Vavro
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * More info at http://joomla.vavro.me
**/


defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('dropdown.init');
JHtml::_('formbehavior.chosen', 'select');

$app		= JFactory::getApplication();
$user		= JFactory::getUser();
$userId		= $user->get('id');

$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));

$archived	= $this->state->get('filter.published') == 2 ? true : false;
$trashed	= $this->state->get('filter.published') == -2 ? true : false;
$saveOrder	= $listOrder == 'a.ordering';

if ($saveOrder)
{
    $saveOrderingUrl = 'index.php?option=com_letempay&task=files.saveOrderAjax&tmpl=component';
    JHtml::_('sortablelist.sortable', 'fileList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

// $sortFields = $this->getSortFields();
$assoc = isset($app->item_associations) ? $app->item_associations : 0;

?>
<script type="text/javascript">
	Joomla.orderTable = function()
	{
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;
		if (order != '<?php echo $listOrder; ?>')
		{
			dirn = 'asc';
		}
		else
		{
			dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn, '');
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_letempay&view=files'); ?>" method="post" name="adminForm" id="adminForm">
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
		<table class="table table-striped" id="fileList">
			<thead>
                            <tr>
                                <th width="1%" class="nowrap center hidden-phone">
                                    <?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
                                </th>
                                <th width="1%" class="hidden-phone">
                                    <?php echo JHtml::_('grid.checkall'); ?>
                                </th>
                                <th width="1%" style="min-width:55px" class="nowrap center">
                                    <?php echo JHtml::_('grid.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
                                </th>
                                <th>
                                    <?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.file_title', $listDirn, $listOrder); ?>
                                </th>
                                <th width="10%" class="nowrap center hidden-phone">
                                    <?php echo JHtml::_('grid.sort', 'COM_LETEMPAY_FILES_TABLE_HEAD_FILE_NAME', 'a.file_name', $listDirn, $listOrder); ?>
                                </th>
                                <th width="6%" class="nowrap hidden-phone">
                                        <?php echo JHtml::_('grid.sort', 'JDATE', 'a.created', $listDirn, $listOrder); ?>
                                </th>
                                <th width="2%">
                                    <?php echo JHtml::_('grid.sort', 'JGLOBAL_HITS', 'a.hits', $listDirn, $listOrder); ?>
                                </th>
                                <th width="2%" class="nowrap hidden-phone">
                                    <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                                </th>
                            </tr>
			</thead>
			<tbody>
			<?php foreach ($this->items as $i => $item) :
				$item->max_ordering = 0; //??
				$ordering   = ($listOrder == 'a.ordering');
				$canCreate  = $user->authorise('core.create',     'com_letempay.category.'.$item->catid);
				$canEdit    = $user->authorise('core.edit',       'com_letempay.file.'.$item->id);
				$canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
				$canEditOwn = $user->authorise('core.edit.own',   'com_letempay.file.'.$item->id) && $item->created_by == $userId;
				$canChange  = $user->authorise('core.edit.state', 'com_letempay.file.'.$item->id) && $canCheckin;
				?>

                            <?php // print_r($item); ?>

				<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->catid; ?>">
					<td class="order nowrap center hidden-phone">
                                            <?php
                                            $iconClass = '';
                                            if (!$canChange)
                                            {
                                                    $iconClass = ' inactive';
                                            }
                                            elseif (!$saveOrder)
                                            {
                                                    $iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::tooltipText('JORDERINGDISABLED');
                                            }
                                            ?>
                                            <span class="sortable-handler<?php echo $iconClass ?>">
                                                    <i class="icon-menu"></i>
                                            </span>
                                            <?php if ($canChange && $saveOrder) : ?>
                                                    <input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order " />
                                            <?php endif; ?>
					</td>
                                        
					<td class="center hidden-phone">
                                             <?php echo JHtml::_('grid.id', $i, $item->id); ?>
					</td>

					<td class="center">
                                            <div class="btn-group">
                                                <?php echo JHtml::_('jgrid.published', $item->state, $i, 'files.', $canChange, 'cb'); ?>
                                            </div>
					</td>
	
					<td class="nowrap has-context">
						<div class="pull-left">
							<?php if ($item->checked_out) : ?>
								<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'files.', $canCheckin); ?>
							<?php endif; ?>
							<?php if ($canEdit || $canEditOwn) : ?>
								<a href="<?php echo JRoute::_('index.php?option=com_letempay&task=file.edit&id=' . $item->id); ?>" title="<?php echo JText::_('JACTION_EDIT'); ?>">
									<?php echo $this->escape($item->file_title); ?></a>
							<?php else : ?>
								<span title="<?php echo JText::sprintf('JFIELD_ALIAS_LABEL', $this->escape($item->alias)); ?>"><?php echo $this->escape($item->title); ?></span>
							<?php endif; ?>
							<div class="small">
								<?php echo JText::_('JCATEGORY') . ": " . $this->escape($item->category_title); ?>
							</div>
						</div>
					</td>
                                        <td class="center">
                                            <?php echo JHtml::_('link', JRoute::_(JURI::root().'index.php?option=com_letempay&view=getfile&format=raw&id='.(int) $item->id), $this->escape($item->file_name), array('target' => '_blank')); ?>
					</td>    
					<td class="nowrap small hidden-phone">
						<?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC4')); ?>
					</td>
					<td class="center">
						<?php echo (int) $item->hits; ?>
					</td>
					<td class="center hidden-phone">
						<?php echo (int) $item->id; ?>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
            
		<?php echo $this->pagination->getListFooter(); ?>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
