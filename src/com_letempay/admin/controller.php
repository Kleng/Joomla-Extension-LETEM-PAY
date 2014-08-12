<?php
/**
 * @package Let'em pay for Joomla! 3.1
 * @author Ing. Peter Vavro
 * @copyright (C) 2013 - Ing. Peter Vavro
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * More info at http://joomla.vavro.me
**/


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Component Controller
 *
 * @package     LetemPay
 * @subpackage  com_letempay
 * @since       1.5
 */
class LetemPayController extends JControllerLegacy {

    /**
     * @var		string	The default view.
     * @since   1.6
     */
    protected $default_view = 'transactions';

    /**
     * Method to display a view.
     *
     * @param   boolean			If true, the view output will be cached
     * @param   array  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
     *
     * @return  JController		This object to support chaining.
     *
     * @since   1.5
     */
    public function display($cachable = false, $urlparams = false) {

        $view   = $this->input->get('view', 'transactions');
        $layout = $this->input->get('layout', 'transactions');
        $id     = $this->input->getInt('id');

        // Check for edit form.
        if ($view == 'transaction' && $layout == 'edit' && !$this->checkEditId('com_letempay.edit.transaction', $id))
        {
                // Somehow the person just went to the form - we don't allow that.
                $this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
                $this->setMessage($this->getError(), 'error');
                $this->setRedirect(JRoute::_('index.php?option=com_letempay&view=transactions', false));

                return false;
        }

        // Require helper file
        JLoader::register('LetemPayHelper', JPATH_COMPONENT . DS . 'helpers' . DS . 'letempay.php');

        // Load the submenu.
        // -???- LetemPayHelper::addSubmenu(JRequest::getCmd('view', 'files'));

        // call parent behavior
        parent::display($cachable);

        return $this;
    }

}
