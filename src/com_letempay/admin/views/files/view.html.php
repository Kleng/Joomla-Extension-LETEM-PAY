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
 * View class for a list of files.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_letempay
 * @since       1.6
 */
class LetemPayViewFiles extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;

	/**
	 * Display the view
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
            if ($this->getLayout() !== 'modal')
            {
                LetemPayHelper::addSubmenu('files');
            }

            $this->items = $this->get('Items');
            $this->pagination = $this->get('Pagination');
            $this->state = $this->get('State');

            // Check for errors.
            if (count($errors = $this->get('Errors')))
            {
                JError::raiseError(500, implode("\n", $errors));
                return false;
            }


            // We don't need toolbar in the modal window.
            if ($this->getLayout() !== 'modal')
            {
                $this->addToolbar();
                $this->sidebar = JHtmlSidebar::render();
            }

            parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{

		$canDo = LetemPayHelper::getActions();
		$user  = JFactory::getUser();

		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');

                JToolBarHelper::title(JText::_('COM_LETEMPAY').':&nbsp;'.
                        JText::_('COM_LETEMPAY_MANAGER_FILES'), 'file.png');

		if ($canDo->get('core.create') || (count($user->getAuthorisedCategories('com_letempay', 'core.create'))) > 0 )
		{
			JToolbarHelper::addNew('file.add');
		}

		if (($canDo->get('core.edit')) || ($canDo->get('core.edit.own')))
		{
			JToolbarHelper::editList('file.edit');
		}

		if ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::publish('files.publish', 'JTOOLBAR_PUBLISH', true);
			JToolbarHelper::unpublish('files.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			JToolbarHelper::checkin('files.checkin');
		}

		if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete'))
		{
                    JToolbarHelper::deleteList('', 'files.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($canDo->get('core.edit.state'))
		{
                    JToolbarHelper::trash('files.trash');
		}

		if ($canDo->get('core.admin'))
		{
                    JToolbarHelper::preferences('com_letempay');
		}

		JToolbarHelper::help('JHELP_LETEMPAY_FILE_MANAGER');
		JHtmlSidebar::setAction('index.php?option=com_letempay&view=files');
                
		JHtmlSidebar::addFilter(
			JText::_('JOPTION_SELECT_PUBLISHED'),
			'filter_published',
			JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true)
		);

		JHtmlSidebar::addFilter(
			JText::_('JOPTION_SELECT_CATEGORY'),
			'filter_category_id',
			JHtml::_('select.options', JHtml::_('category.options', 'com_letempay'), 'value', 'text', $this->state->get('filter.category_id'))
		);
	}
}
