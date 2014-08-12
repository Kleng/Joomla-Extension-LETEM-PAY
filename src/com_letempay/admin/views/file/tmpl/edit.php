<?php
/**
 * @package Let'em pay for Joomla! 3.1
 * @author Ing. Peter Vavro
 * @copyright (C) 2013 - Ing. Peter Vavro
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * More info at http://joomla.vavro.me
**/


defined('_JEXEC') or die;

JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

// Create shortcut to parameters.
$params = $this->state->get('params');
$params = $params->toArray();

// This checks if the config options have ever been saved. If they haven't they will fall back to the original settings.
// $editoroptions = isset($params['show_publishing_options']);

$app = JFactory::getApplication();
$input = $app->input;
?>
<script type="text/javascript">
    Joomla.submitbutton = function(task)
    {
        if (task == 'file.cancel' || document.formvalidator.isValid(document.id('item-form')))
        {
            <?php // echo $this->form->getField('articletext')->save(); ?>
            Joomla.submitform(task, document.getElementById('item-form'));
        }
    };
</script>

<form action="<?php echo JRoute::_('index.php?option=com_letempay&layout=edit&id='.(int) $this->item->id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="item-form" class="form-validate">

    <?php echo JLayoutHelper::render('joomla.edit.item_title', $this); ?>

    <div class="row-fluid">

        <!-- Begin Content -->
        <div class="span10 form-horizontal">

            <h4><?php echo JText::_('COM_LETEMPAY_FILE_DETAILS');?></h4>
            <hr />
            <fieldset class="adminform">

                <div class="control-group">
                    <div class="control-label">
                            <?php echo $this->form->getLabel('file_title'); ?>
                    </div>
                    <div class="controls">
                            <?php echo $this->form->getInput('file_title'); ?>
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-label">
                            <?php echo $this->form->getLabel('file_upload'); ?>
                    </div>
                    <div class="controls">
                            <?php echo $this->form->getInput('file_upload'); ?>
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-label">
                            <?php echo $this->form->getLabel('file_name'); ?>
                    </div>
                    <div class="controls">
                            <?php echo $this->form->getInput('file_name'); ?>
                    </div>
                </div>
                
                <div class="control-group">
                    <div class="control-label">
                            <?php echo $this->form->getLabel('catid'); ?>
                    </div>
                    <div class="controls">
                            <?php echo $this->form->getInput('catid'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
                            <?php echo $this->form->getLabel('id'); ?>
                    </div>
                    <div class="controls">
                            <?php echo $this->form->getInput('id'); ?>
                    </div>
                </div>

            </fieldset>

            <input type="hidden" name="task" value="" />
            <input type="hidden" name="return" value="<?php echo $input->getCmd('return');?>" />
            <?php echo JHtml::_('form.token'); ?>

        </div>
        <!-- End Content -->

        <!-- Begin Sidebar -->
            <?php echo JLayoutHelper::render('joomla.edit.details', $this); ?>
        <!-- End Sidebar -->
    </div>
</form>
