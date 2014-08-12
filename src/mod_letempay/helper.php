<?php
/**
 * @package Let'em pay for Joomla! 3.1
 * @author Ing. Peter Vavro
 * @copyright (C) 2013 - Ing. Peter Vavro
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * More info at http://joomla.vavro.me
**/


defined('_JEXEC') or die;

JTable::addIncludePath(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_letempay' . DS . 'tables');
JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_letempay' . DS . 'models', 'LetemPayModel');

abstract class modLetemPayHelper {

    /**
     * 
     * 
     * @param type $category_id
     * @return type
     */
    static function getFileList($category_id) {

        // Get the dbo
        $db = JFactory::getDbo();

        // Get an instance of the generic articles model
        $model = JModelLegacy::getInstance('Files', 'LetemPayModel', array('ignore_request' => true));

        $model->setState('filter.category_id', array($category_id));
        $model->setState('filter.published', 1);

        $files = $model->getItems();

        return $files;
    }


    /**
     * Get config paramaeters for Simple Pain Download component 
     * 
     * @return array
     */
    static function getPaypalParams($componentParams) {

        $params = array();
        $variables_prefix = 'Sandbox';

        $params['API_Environment'] = $componentParams->get('API_Environment');

        switch($componentParams->get('API_Environment')){
            case 'live':

                $variables_prefix = 'Live';

                if($componentParams->get('Live_API_Endpoint', FALSE)){

                    $params['API_Endpoint'] = $componentParams->get('Live_API_Endpoint');
                }

                break;
        }

        // PayPal Params
        $params['API_UserName'] = $componentParams->get($variables_prefix.'_API_UserName');
        $params['API_Password'] = $componentParams->get($variables_prefix.'_API_Password');
        $params['API_Signature'] = $componentParams->get($variables_prefix.'_API_Signature');

        return $params;
    }


    /**
     * Get record from transaction table
     * 
     * @param type $conditions
     * @return boolean
     */
    static function getPayPalLog($conditions = array()) {

        // Initialize a transaction table
        $table_transaction = &JTable::getInstance('Transaction', 'LetemPayTable');

        if ($table_transaction->load($conditions)) {
            return $table_transaction;
        }

        return false;
    }


    /**
     * Inserts record to the transaction table
     * 
     * @param type $table_transaction_row
     * @param type $table_transaction
     * @return boolean
     */
    static function savePayPalLog($table_transaction_row = array(), &$table_transaction = FALSE) {

        // >>> More at https://gist.github.com/3211464

        if (!$table_transaction) {

            JTable::addIncludePath(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_letempay' . DS . 'tables');

            // Initialize a new transaction
            $table_transaction = &JTable::getInstance('Transaction', 'LetemPayTable');
        }

        // Add category id
        if (isset($table_transaction_row['category_id'])) {

            $table_transaction_row['catid'] = $table_transaction_row['category_id'];
            unset($table_transaction_row['category_id']);
        }

        // Now store the transaction
        if (!$table_transaction->save($table_transaction_row)) {

            JError::raiseNotice(500, $table_transaction->getError());
            return false;
        }

        return true;
    }


    /**
     * Get record from transaction table
     * 
     * @param type $conditions
     * @return boolean
     */
    static function sendEmail($email_to , $subject, $message_body) {

        // http://docs.joomla.org/Sending_email_from_extensions
        $mailer = JFactory::getMailer();
        $config = JFactory::getConfig();

        $mailer->setSender(array($config->get( 'mailfrom' ), $config->get( 'fromname' )));
        $mailer->addRecipient($email_to);
        $mailer->setSubject($subject);
        $mailer->setBody($message_body);
        $mailer->isHTML(true);

        $send = $mailer->Send();

        if ( $send !== true ) {

            JFactory::getApplication()->enqueueMessage(JText::_('COM_LETEMPAY_ERROR_SENDING_EMAIL') . $send->message, 'error');
            return FALSE;
        }

        return TRUE;
    }
}
