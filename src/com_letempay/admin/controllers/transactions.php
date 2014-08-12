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
 * Transactions list controller class.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_content
 * @since       1.6
 */
class LetemPayControllerTransactions extends JControllerAdmin {

	/**
	 * @var        string    The prefix to use with controller messages.
	 * @since   1.6
	 */
	protected $text_prefix = 'COM_LETEMPAY_TRANSACTION';

    /**
     * Proxy for getModel.
     *
     * @param   string	$name	The name of the model.
     * @param   string	$prefix	The prefix for the PHP class name.
     *
     * @return  JModel
     * @since   1.6
     */
    public function getModel($name = 'Transaction', $prefix = 'LetemPayModel', $config = array('ignore_request' => true)) {

        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }
}
