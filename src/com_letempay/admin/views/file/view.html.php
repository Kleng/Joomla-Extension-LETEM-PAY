<?php
/**
 * @package Let'em pay for Joomla! 3.1
 * @author Ing. Peter Vavro
 * @copyright (C) 2013 - Ing. Peter Vavro
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * More info at http://joomla.vavro.me
**/


defined('_JEXEC') or die;

/**
 * View to edit an file.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_letempay
 * @since       1.6
 */
class LetemPayViewFile extends JViewLegacy {

    protected $form;
    protected $item;
    protected $state;

    /**
     * Display the view
     */
    public function display($tpl = null) {
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->state = $this->get('State');
        $this->canDo = LetemPayHelper::getActions($this->state->get('filter.category_id'));

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));
            return false;
        }

        if ($this->getLayout() == 'modal') {
            $this->form->setFieldAttribute('language', 'readonly', 'true');
            $this->form->setFieldAttribute('catid', 'readonly', 'true');
        }

        $this->addToolbar();
        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @since   1.6
     */
    protected function addToolbar() {
        JFactory::getApplication()->input->set('hidemainmenu', true);

        $user = JFactory::getUser();
        $userId = $user->get('id');
        $isNew = ($this->item->id == 0);
        $checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $userId);
        $canDo = LetemPayHelper::getActions($this->state->get('filter.category_id'), $this->item->id);
        JToolbarHelper::title(JText::_('COM_LETEMPAY_PAGE_' . ($checkedOut ? 'VIEW_FILE' : ($isNew ? 'ADD_FILE' : 'EDIT_FILE'))), 'file-add.png');

        // Built the actions for new and existing records.
        // For new records, check the create permission.
        if ($isNew && (count($user->getAuthorisedCategories('com_letempay', 'core.create')) > 0)) {
            JToolbarHelper::apply('file.apply');
            JToolbarHelper::save('file.save');
            JToolbarHelper::cancel('file.cancel');
        } else {
            // Can't save the record if it's checked out.
            if (!$checkedOut) {
                // Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
                if ($canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $userId)) {
                    JToolbarHelper::apply('file.apply');
                    JToolbarHelper::save('file.save');
                }
            }

            JToolbarHelper::cancel('file.cancel', 'JTOOLBAR_CLOSE');
        }
    }

}
