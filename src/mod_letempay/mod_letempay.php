<?php
/**
 * @package Let'em pay for Joomla! 3.1
 * @author Ing. Peter Vavro
 * @copyright (C) 2013 - Ing. Peter Vavro
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * More info at http://joomla.vavro.me
**/


// no direct access
defined('_JEXEC') or die;

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

$user = JFactory::getUser();
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

$component_params = JComponentHelper::getParams('com_letempay');

// Include the syndicate functions only once
require_once dirname(__FILE__).'/helper.php';

    // Get Category

        $categories = JCategories::getInstance('LetemPay');
        $category_id = $params->get('parent', 0);

        if($category_id == 0){

            $category_root = $categories->get('root');
            $categoryChildren = $category_root->getChildren();

            // Get first category
            $category = $categoryChildren[0];

        } else {

            $category = $categories->get($category_id);
        }

// Product is free / Product price = 0
if(!$params->get('product_price', 100)) {

    $alreadyPurchased = TRUE;
    require JModuleHelper::getLayoutPath('mod_letempay', $params->get('layout', 'default'));

} else if ($user->id) {

    // Get component params
    $params_paypal = modLetemPayHelper::getPaypalParams($component_params);

    // Include the syndicate functions only once
    require_once JPATH_BASE.DS.'components'.DS.'com_letempay'.DS.'helpers'.DS.'paypal'.DS.'paypalfunctions.php';
    $PayPalHelper = new PayPalHelper($params_paypal);

    // Check last sucessful purchase

        require_once JPATH_BASE.DS.'components'.DS.'com_letempay'.DS.'helpers'.DS.'letempay.php';
        $alreadyPurchased = LetemPayHelper::checkIfPaid($user->id, $category_id);

    if(!$alreadyPurchased) {

        $jinput = JFactory::getApplication()->input;
        $paypal_task = $jinput->get('paypal_task', FALSE);

        $product_price = $params->get('product_price', FALSE);
        $product_price_currency = $params->get('price_currency', FALSE);

        $currencies = array();
        $currencies['USD'] = array('symbol'=> '$', 'position' => 'left');
        $currencies['EUR'] = array('symbol'=> '€');
        $currencies['AUD'] = array('symbol'=> '$', 'position' => 'left');
        $currencies['GBP'] = array('symbol'=> '£');
        $currencies['CAD'] = array('symbol'=> '$', 'position' => 'left');
        $currencies['CHF'] = array('symbol'=> 'CHF');
        $currencies['SEK'] = array('symbol'=> 'kr');

        if(isset($currencies[$product_price_currency])) {

            if($currencies[$product_price_currency]['position'] == 'left'){

                $product_price_view = $currencies[$product_price_currency]['symbol'].$product_price;

            } else {

                $product_price_view = $product_price.$currencies[$product_price_currency]['symbol'];
            }
        }

        $productName = $params->get('product_name', FALSE);

        switch($paypal_task) {
            case 'checkout':

                $paymentAmount = $product_price; // -D- $params->get('product_price', FALSE);

                if (!$productName) {

                    JFactory::getApplication()->enqueueMessage(JText::_('MOD_LETEMPAY_ERROR_NO_PRODUCT_NAME'), 'error');

                } else if(!$paymentAmount) {

                    JFactory::getApplication()->enqueueMessage(JText::_('MOD_LETEMPAY_ERROR_NO_PRODUCT_PRICE'), 'error');

                } else {

                    $request_SetExpressCheckoutDG = array();

                    // -D- $PayPalHelper->checkout($productName,$productPrice);

                    // ==================================
                    // PayPal Express Checkout Module
                    // ==================================

                    //'------------------------------------
                    //' The paymentAmount is the total value of the purchase.
                    //' TODO: Enter the total Payment Amount within the quotes.
                    //' example : $paymentAmount = "15.00";
                    //'------------------------------------

                    //'------------------------------------
                    //' The currencyCodeType is set to the selections made on the Integration Assistant 
                    //'------------------------------------
                    $request_SetExpressCheckoutDG['currencyCodeType'] = $product_price_currency; // -D- "USD";
                    $request_SetExpressCheckoutDG['paymentType'] = "Sale";

                        //'------------------------------------
                        //' The returnURL is the location where buyers return to when a payment has been succesfully authorized.
                        //' This is set to the value entered on the Integration Assistant 
                        //'------------------------------------
                        $orderconfirm_uri = JFactory::getURI();
                        $orderconfirm_uri->setVar('paypal_task','orderconfirm');
                        $request_SetExpressCheckoutDG['returnURL'] = urlencode($orderconfirm_uri->toString());

                        //'------------------------------------
                        //' The cancelURL is the location buyers are sent to when they hit the
                        //' cancel button during authorization of payment during the PayPal flow
                        //' This is set to the value entered on the Integration Assistant
                        //'------------------------------------
                        $cancel_uri = JFactory::getURI();
                        $cancel_uri->setVar('paypal_task','cancel');
                        $request_SetExpressCheckoutDG['cancelURL'] = urlencode($cancel_uri->toString());

                    //'------------------------------------
                    //' Calls the SetExpressCheckout API call
                    //' The CallSetExpressCheckout function is defined in the file PayPalFunctions.php, it is included at the top of this file.
                    //'-------------------------------------------------

                    //::ITEMS::

                        // to add anothe item, uncomment the lines below and comment the line above 
                        // $items[] = array('name' => 'Item Name1', 'amt' => $itemAmount1, 'qty' => 1);
                        // $items[] = array('name' => 'Item Name2', 'amt' => $itemAmount2, 'qty' => 1);
                        // $paymentAmount = $itemAmount1 + $itemAmount2;
                        $request_SetExpressCheckoutDG['items'] = array();
                        $request_SetExpressCheckoutDG['items'][] = array('name' => $productName, 'amt' => $paymentAmount, 'qty' => 1);

                        // assign corresponding item amounts to "$itemAmount1" and "$itemAmount2"
                        // NOTE : sum of all the item amounts should be equal to payment  amount 

                    // Execute request to PayPal
                    $resArray = $PayPalHelper->SetExpressCheckoutDG($paymentAmount, 
                            $request_SetExpressCheckoutDG['currencyCodeType'], 
                            $request_SetExpressCheckoutDG['paymentType'], 
                            isset($request_SetExpressCheckoutDG['returnURL']) ? $request_SetExpressCheckoutDG['returnURL'] : NULL, 
                            isset($request_SetExpressCheckoutDG['cancelURL']) ? $request_SetExpressCheckoutDG['cancelURL'] : NULL, 
                            $request_SetExpressCheckoutDG['items']);

                    // Save transaction log
                    $table_transaction_row = array();
                    
                    // Processing of received result
                    $ack = strtoupper($resArray["ACK"]);

                    if (isset($resArray["TOKEN"]) && $resArray["TOKEN"]) {

                        $token = urldecode($resArray["TOKEN"]);
                        $table_transaction_row['token'] = $token;
                    }

                    $table_transaction_row['catid'] = $category_id;
                    $table_transaction_row['request_SetExpressCheckoutDG'] = $request_SetExpressCheckoutDG;
                    $table_transaction_row['result_SetExpressCheckoutDG'] = $resArray;

                    if(isset($params_paypal['API_Environment']) && $params_paypal['API_Environment']){
                        $table_transaction_row['API_Environment'] = $params_paypal['API_Environment'];
                    }

                    // SAVE LOG OF THE TRANSACTION
                    modLetemPayHelper::savePayPalLog($table_transaction_row);

                    if($ack == "SUCCESS" || $ack == "SUCCESSWITHWARNING") {

                        $_SESSION['TOKEN'] = $token;
                        $PayPalHelper->RedirectToPayPalDG($token);
                    }
                }

                break;

            case 'orderconfirm':

                // Get token
                $token = $jinput->get('token', FALSE);

                // Get transaction log
                if($token) {
                    
                    // Look for transaction error from "checkout"
                    $table_transaction = modLetemPayHelper::getPayPalLog(array('token'=>$token));

                    if ($table_transaction) {
                        $token = $table_transaction->token;
                    }

                    if (!$table_transaction->result_ConfirmPayment) {

                        /* =====================================
                         *	 PayPal Express Checkout Call
                         * =====================================
                         ' this step is required to get parameters to make DoExpressCheckout API call, 
                         ' this step is required only if you are not storing the SetExpressCheckout API call's request values in you database.
                         ' ------------------------------------
                         */
                        $res = $PayPalHelper->GetExpressCheckoutDetails($token);

                        if($res) {

                            $log_row = array();
                            $log_row['result_GetExpressCheckoutDetails'] = $res;

                            modLetemPayHelper::savePayPalLog($log_row, $table_transaction);

                            /* ------------------------------------
                             * The paymentAmount is the total value of the purchase. 
                             *------------------------------------
                             */
                            $finalPaymentAmount = $res["AMT"];

                            /*
                             '------------------------------------
                             ' Calls the DoExpressCheckoutPayment API call
                             ' The ConfirmPayment function is defined in the file PayPalFunctions.php, that is included at the top of this file.
                             '-------------------------------------------------
                             */

                            // Format the  parameters that were stored or received from GetExperessCheckout call.
                            $payerID            = $jinput->get('PayerID');
                            $currencyCodeType   = $res['CURRENCYCODE'];
                            $paymentType        = $table_transaction->paymentType; // Sale

                            $resArray = $PayPalHelper->ConfirmPayment ($token, $paymentType, $currencyCodeType, $payerID, $finalPaymentAmount);

                            // Save transaction log
                            $log_row = array();

                            $ack = strtoupper($resArray["ACK"]);

                            if( $ack == "SUCCESS" || $ack == "SUCCESSWITHWARNING" ) {

                                //!!! Payment successfull

                                $log_row['state'] = 1;

                                // Set success message language code
                                $result_message = 'MOD_LETEMPAY_PAYPAL_SUCCESS_MSG';

                                // Email notification about sale, if there is assigned email address

                                    $notification_email = $component_params->get('notification_email', FALSE);

                                    if($notification_email) {

                                        $subject = JText::sprintf('MOD_LETEMPAY_PAYMENT_EMAIL_SOLD_SUBJECT_LABEL', $productName);
                                        $message_body = JText::sprintf('MOD_LETEMPAY_PAYMENT_EMAIL_SOLD_BODY_LABEL', $productName, $user->email);

                                        $log_row['params']['admin_notification'] = modLetemPayHelper ::sendEmail($notification_email, $subject ,$message_body);
                                    }

                                // Notification for the user about successful payment

                                    $subject = JText::sprintf('MOD_LETEMPAY_PAYMENT_EMAIL_THANK_SUBJECT_LABEL', JFactory::getDocument()->getTitle());

                                    $message_body = array();
                                    $message_body[] = JText::_('MOD_LETEMPAY_PAYMENT_EMAIL_THANK_LABEL');

                                    $actual_uri = JFactory::getURI();
                                    $downloadURL = $actual_uri->toString(array('scheme', 'host', 'path'));

                                    $html_link_to = JHtml::_('link', $downloadURL, $downloadURL, array('target' => '_blank', 'rel' => "nofollow"));
                                    $message_body[] = JText::sprintf('MOD_LETEMPAY_PAYMENT_EMAIL_SOLD_SUBJECT_LABEL', $html_link_to);

                                    $log_row['params']['user_notification'] = modLetemPayHelper ::sendEmail($user->email, $subject, implode('',$message_body));

                            } else {

                                $result_message = 'MOD_LETEMPAY_PAYPAL_FAIL_MSG';
                            }

                            $log_row['result_ConfirmPayment'] = $resArray;

                            // Save transaction log
                            modLetemPayHelper::savePayPalLog($log_row, $table_transaction);
                        }
                    }
                }

                break;
            case 'cancel':

                $result_message = 'MOD_LETEMPAY_PAYPAL_CANCEL_MSG';

                break;
        }
    }

    if (isset($result_message) && $result_message) {

        // Add javascript to close Digital Goods frame. You may want to add more javascript code to
        // display some info message indicating status of purchase in the parent window
        require JModuleHelper::getLayoutPath('mod_letempay', 'default_paypal_result');

    } else {

        // Check if all the PayPal necessary parameters are already set
        if ($params_paypal['API_UserName'] && $params_paypal['API_Password'] && $params_paypal['API_Signature']) {

            require JModuleHelper::getLayoutPath('mod_letempay', $params->get('layout', 'default'));

        } else {

            JFactory::getApplication()->enqueueMessage(JText::_('MOD_LETEMPAY_ERROR_SET_PAYPAL_API_DATA'), 'error');
        }
    }

} else {

    require JModuleHelper::getLayoutPath('mod_letempay', 'default_dologgin');
}










