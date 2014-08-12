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
 * Files list controller class.
 *
 * @package     LetemPay
 * @subpackage  com_letempay
 * @since       2.5
 */
class LetemPayControllerFiles extends JControllerAdmin {

    /**
     * @var        string    The prefix to use with controller messages.
     * @since   1.6
     */
    protected $text_prefix = 'COM_LETEMPAY_FILE';
    
    /**
     * Proxy for getModel.
     *
     * @param   string	$name	The name of the model.
     * @param   string	$prefix	The prefix for the PHP class name.
     *
     * @return  JModel
     * @since   1.6
     */
    public function getModel($name = 'File', $prefix = 'LetemPayModel', $config = array('ignore_request' => true)) {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }

}
