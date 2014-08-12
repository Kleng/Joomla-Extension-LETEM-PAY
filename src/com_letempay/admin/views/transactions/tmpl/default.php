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
$config         = JFactory::getConfig();
$user		= JFactory::getUser();
$userId		= $user->get('id');

$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));

$archived	= $this->state->get('filter.published') == 2 ? true : false;
$trashed	= $this->state->get('filter.published') == -2 ? true : false;
$saveOrder	= $listOrder == 'a.ordering';

if ($saveOrder)
{
    $saveOrderingUrl = 'index.php?option=com_letempay&task=transactions.saveOrderAjax&tmpl=component';
    JHtml::_('sortablelist.sortable', 'transactionList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

// $sortFields = $this->getSortFields();
$assoc = isset($app->item_associations) ? $app->item_associations : 0;

$time_offset = $config->get('offset');

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

<form action="<?php echo JRoute::_('index.php?option=com_letempay&view=transactions'); ?>" method="post" name="adminForm" id="adminForm">

    <?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
    <?php else : ?>
	<div id="j-main-container">
    <?php endif;?>

        <table class="table table-striped" id="transactionList">
                <thead>
                    <tr>
                        <th width="1%" class="hidden-phone">
                            <?php echo JHtml::_('grid.checkall'); ?>
                        </th>
                        <th width="8%" style="min-width:55px" class="nowrap center">
                            <?php echo JHtml::_('grid.sort', 'JCATEGORY', 'a.catid', $listDirn, $listOrder); ?>
                        </th>
                        <th width="26%">
                            <?php echo JText::_('COM_LETEMPAY_PAYPAL_PRODUCT_TITLE_LABEL'); ?>
                        </th>
                        <th width="18%" class="nowrap">
                            <?php echo JText::_('COM_LETEMPAY_PAYPAL_TRANSACTION_STATUS_LABEL'); ?>
                        </th>
                        <th>
                            <?php echo JText::_('COM_LETEMPAY_PAYPAL_TRANSACTION_PROBLEM_DETAIL_LABEL'); ?>
                        </th>
                        <th width="6%" class="nowrap center hidden-phone">
                                <?php echo JHtml::_('grid.sort', 'JDATE', 'a.created', $listDirn, $listOrder); ?>
                        </th>
                        <th width="6%" class="nowrap center hidden-phone">
                                <?php echo JHtml::_('grid.sort', 'JAUTHOR', 'a.author_name', $listDirn, $listOrder); ?>
                        </th>
                        <th width="1%" style="min-width:55px" class="nowrap center">
                            <?php echo JHtml::_('grid.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
                        </th>
                        <th width="2%" class="nowrap center hidden-phone">
                            <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                        </th>
                    </tr>
                </thead>
                <tbody>

                <?php foreach ($this->items as $i => $item) : ?>

                    <?php 
                        $item->max_ordering = 0; //??
                        $ordering   = ($listOrder == 'a.ordering');
                        $canCreate  = $user->authorise('core.create',     'com_letempay.category.'.$item->catid);
                        $canEdit    = $user->authorise('core.edit',       'com_letempay.transaction.'.$item->id);
                        $canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
                        $canEditOwn = $user->authorise('core.edit.own',   'com_letempay.transaction.'.$item->id) && $item->created_by == $userId;
                        $canChange  = $user->authorise('core.edit.state', 'com_letempay.transaction.'.$item->id) && $canCheckin;
                    ?>

                    <tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->catid; ?>">

                        <td class="center hidden-phone">
                             <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                        </td>

                        <td class="center hidden-phone">
                            <?php echo $this->escape($item->category_title); ?>
                        </td>

                        <td class="nowrap">
                            <div class="pull-left">

                                <?php if ($item->checked_out) : ?>
                                    <?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'transactions.', $canCheckin); ?>
                                <?php endif; ?>

                                <div class="items">

                                    <?php $products = array(); ?>

                                    <?php foreach ($item->request_SetExpressCheckoutDG->items as $product) : ?>

                                        <?php $products[] = '<span class="quantity">'.$product->qty.'</span>x'.
                                                            '<span class="product_name">'.$product->name.'</span>&nbsp;'.
                                                            '<span class="price">'.$product->amt.'&nbsp;'.$item->request_SetExpressCheckoutDG->currencyCodeType.'</span>'; ?>

                                    <?php endforeach; ?>

                                    <?php echo implode('<br/>', $products); ?>
                                </div>

                                <?php if ($canEdit || $canEditOwn) : ?>
                                        <a href="<?php echo JRoute::_('index.php?option=com_letempay&task=transaction.edit&id=' . $item->id); ?>" title="<?php echo JText::_('JACTION_EDIT'); ?>">
                                        <?php echo $this->escape($item->transaction_title); ?></a>

                                <?php else : ?>
                                        <span title="<?php echo JText::sprintf('JFIELD_ALIAS_LABEL', $this->escape($item->alias)); ?>"><?php echo $this->escape($item->title); ?></span>
                                <?php endif; ?>
                            </div>
                        </td>

                        <?php if ((strcasecmp($item->result_SetExpressCheckoutDG->ACK,"SUCCESS") != 0) && (strcasecmp($item->result_SetExpressCheckoutDG->ACK,"SUCCESSWITHWARNING") != 0)) : ?>
                            <td class="nowrap small text-error">
                                <?php echo JText::_('COM_LETEMPAY_PAYPAL_ERROR_SETEXPRESSCHECKOUT_FAILED_MESSAGE'); ?>
                            </td>
                        <?php elseif ((strcasecmp($item->result_GetExpressCheckoutDetails->ACK,"SUCCESS") != 0) && (strcasecmp($item->result_GetExpressCheckoutDetails->ACK,"SUCCESSWITHWARNING") != 0)) : ?>
                            <td class="nowrap small text-error">
                                <?php echo JText::_('COM_LETEMPAY_PAYPAL_ERROR_GETEXPRESSCHECKOUT_FAILED_MESSAGE'); ?>
                            </td>
                        <?php else : ?>
                            <td class="nowrap small text-success">
                                <b><?php echo $item->result_ConfirmPayment->ACK; ?></b>
                            </td>
                        <?php endif; ?>

                        <td class="nowrap small has-context text-error">
                            <?php
                                if ((($item->result_SetExpressCheckoutDG->ACK != "SUCCESS") && ($item->result_SetExpressCheckoutDG->ACK != "SUCCESSWITHWARNING")) || 
                                    (($item->result_GetExpressCheckoutDetails->ACK != "SUCCESS") && ($item->result_GetExpressCheckoutDetails->ACK != "SUCCESSWITHWARNING"))) {

                                    $result_errors = array();

                                    if(isset($item->result_SetExpressCheckoutDG->L_SHORTMESSAGE0) && $item->result_SetExpressCheckoutDG->L_SHORTMESSAGE0){

                                        $result_errors[]= '<b>'.JText::_('COM_LETEMPAY_PAYPAL_ERROR_SHORT_LABEL').'</b>:'.urldecode($item->result_SetExpressCheckoutDG->L_SHORTMESSAGE0);
                                    }

                                    if(isset($item->result_SetExpressCheckoutDG->L_LONGMESSAGE0) && $item->result_SetExpressCheckoutDG->L_LONGMESSAGE0){

                                        $result_errors[]= '<b>'.JText::_('COM_LETEMPAY_PAYPAL_ERROR_DETAIL_LABEL').'</b>:'.urldecode($item->result_SetExpressCheckoutDG->L_LONGMESSAGE0);
                                    }

                                    if(isset($item->result_SetExpressCheckoutDG->L_ERRORCODE0) && $item->result_SetExpressCheckoutDG->L_ERRORCODE0){

                                        $result_errors[]= '<b>'.JText::_('COM_LETEMPAY_PAYPAL_ERROR_CODE_LABEL').'</b>:'.urldecode($item->result_SetExpressCheckoutDG->L_ERRORCODE0);
                                    }

                                    if(isset($item->result_SetExpressCheckoutDG->L_SEVERITYCODE0) && $item->result_SetExpressCheckoutDG->L_SEVERITYCODE0){

                                        $result_errors[]= '<b>'.JText::_('COM_LETEMPAY_PAYPAL_ERROR_SEVERITY_CODE_LABEL').'</b>:'.urldecode($item->result_SetExpressCheckoutDG->L_SEVERITYCODE0);
                                    }

                                    print_r(implode('<br/>',$result_errors));
                                }
                            ?>
                        </td>
                        <td class="nowrap center small hidden-phone">
                            <?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC2'),$time_offset); ?>
                        </td>
                        <td class="center small">
                                <?php echo $item->author_name; ?>
                        </td>

                        <td class="center">
                            <div class="btn-group">
                                <?php echo JHtml::_('jgrid.published', $item->state, $i, 'transactions.', $canChange, 'cb'); ?>
                            </div>
                        </td>

                        <td class="center small hidden-phone">
                                <?php echo (int)$item->id; ?>
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
