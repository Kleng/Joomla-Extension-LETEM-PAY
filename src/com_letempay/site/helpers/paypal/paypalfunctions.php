<?php
/**
 * @package Let'em pay for Joomla! 3.1
 * @author PayPal + Ing. Peter Vavro
 * @copyright (C) 2013 - PayPal + Ing. Peter Vavro
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * More info at http://joomla.vavro.me
**/

defined('_JEXEC') or die;

/* Derivate from paypalfunctions.php */

class PayPalHelper {

    private $PROXY_HOST;
    private $PROXY_PORT;
    private $USE_PROXY;
    private $API_UserName;
    private $API_Password;
    private $API_Signature;
    private $sBNCode;
    private $API_Endpoint;
    private $PAYPAL_URL;
    private $PAYPAL_DG_URL;
    private $version;

    /**
     * Constructor.
     *
     * @param	array	$config	An optional associative array of configuration settings.
     *
     * @return	ASimpleListControllerColumns
     * @see		JController
     * @since	1.6
     */
    public function __construct($config = array()) {


        /********************************************
          PayPal API Module

          Defines all the global variables and the wrapper functions
         ******************************************* */
        $this->PROXY_HOST = '127.0.0.1';
        $this->PROXY_PORT = '808';
        $this->USE_PROXY = false;


        //' TODO:
        //'------------------------------------
        //' PayPal API Credentials
        //' Replace <API_USERNAME> with your API Username
        //' Replace <API_PASSWORD> with your API Password
        //' Replace <API_SIGNATURE> with your Signature
        //'------------------------------------
        $this->API_UserName = $config['API_UserName']; // "<API_USERNAME>";
        $this->API_Password = $config['API_Password']; // "<API_PASSWORD>";
        $this->API_Signature = $config['API_Signature']; // "<API_SIGNATURE>";
        // BN Code 	is only applicable for partners
        // $this->sBNCode = "PP-ECWizard";

        /* 	
          ' Define the PayPal Redirect URLs.
          ' 	This is the URL that the buyer is first sent to do authorize payment with their paypal account
          ' 	change the URL depending if you are testing on the sandbox or the live PayPal site
          '
          ' For the sandbox, the URL is       https://www.sandbox.paypal.com/webscr&cmd=_express-checkout&token=
          ' For the live site, the URL is        https://www.paypal.com/webscr&cmd=_express-checkout&token=
         */

        switch ($config['API_Environment']) {
            case 'live':

                $this->API_Endpoint = "https://api-3t.paypal.com/nvp";

                if ($config['API_Endpoint']) {

                    $this->API_Endpoint = $config['API_Endpoint'];
                }

                $this->PAYPAL_URL = "https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=";
                $this->PAYPAL_DG_URL = "https://www.paypal.com/incontext?token=";

                break;
            case 'sandbox':

                $this->API_Endpoint = "https://api-3t.sandbox.paypal.com/nvp";
                $this->PAYPAL_URL = "https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&token=";
                $this->PAYPAL_DG_URL = "https://www.sandbox.paypal.com/incontext?token=";

                break;
        }

        $this->version = "84";
    }

    /* An express checkout transaction starts with a token, that
      identifies to PayPal your transaction
      In this example, when the script sees a token, the script
      knows that the buyer has already authorized payment through
      paypal.  If no token was found, the action is to send the buyer
      to PayPal to first authorize payment
     */

    /*
      '-------------------------------------------------------------------------------------------------------------------------------------------
      ' Purpose: 	Prepares the parameters for the SetExpressCheckout API Call for a Digital Goods payment.
      ' Inputs:
      '		paymentAmount:  	Total value of the shopping cart
      '		currencyCodeType: 	Currency code value the PayPal API
      '		paymentType: 		paymentType has to be one of the following values: Sale or Order or Authorization
      '		returnURL:			the page where buyers return to after they are done with the payment review on PayPal
      '		cancelURL:			the page where buyers return to when they cancel the payment review on PayPal
      '--------------------------------------------------------------------------------------------------------------------------------------------
     */

    function SetExpressCheckoutDG($paymentAmount, $currencyCodeType, $paymentType, $returnURL, $cancelURL, $items) {

        //------------------------------------------------------------------------------------------------------------------------------------
        // Construct the parameter string that describes the SetExpressCheckout API call in the shortcut implementation
        $nvpstr = "&PAYMENTREQUEST_0_AMT=" . $paymentAmount;
        $nvpstr .= "&PAYMENTREQUEST_0_PAYMENTACTION=" . $paymentType;

        if ($returnURL != NULL) {
            $nvpstr .= "&RETURNURL=" . $returnURL;
        }

        if ($cancelURL != NULL) {
            $nvpstr .= "&CANCELURL=" . $cancelURL;
        }

        $nvpstr .= "&PAYMENTREQUEST_0_CURRENCYCODE=" . $currencyCodeType;
        $nvpstr .= "&REQCONFIRMSHIPPING=0";
        $nvpstr .= "&NOSHIPPING=1";

        foreach ($items as $index => $item) {

            $nvpstr .= "&L_PAYMENTREQUEST_0_NAME" . $index . "=" . urlencode($item["name"]);
            $nvpstr .= "&L_PAYMENTREQUEST_0_AMT" . $index . "=" . urlencode($item["amt"]);
            $nvpstr .= "&L_PAYMENTREQUEST_0_QTY" . $index . "=" . urlencode($item["qty"]);
            $nvpstr .= "&L_PAYMENTREQUEST_0_ITEMCATEGORY" . $index . "=Digital";
        }

        //'--------------------------------------------------------------------------------------------------------------- 
        //' Make the API call to PayPal
        //' If the API call succeded, then redirect the buyer to PayPal to begin to authorize payment.  
        //' If an error occured, show the resulting errors
        //'---------------------------------------------------------------------------------------------------------------

        $resArray = $this->hash_call("SetExpressCheckout", $nvpstr);
        return $resArray;
    }

    /*
      '-------------------------------------------------------------------------------------------
      ' Purpose: 	Prepares the parameters for the GetExpressCheckoutDetails API Call.
      '
      ' Inputs:
      '		None
      ' Returns:
      '		The NVP Collection object of the GetExpressCheckoutDetails Call Response.
      '-------------------------------------------------------------------------------------------
     */

    function GetExpressCheckoutDetails($token) {
        //'--------------------------------------------------------------
        //' At this point, the buyer has completed authorizing the payment
        //' at PayPal.  The function will call PayPal to obtain the details
        //' of the authorization, incuding any shipping information of the
        //' buyer.  Remember, the authorization is not a completed transaction
        //' at this state - the buyer still needs an additional step to finalize
        //' the transaction
        //'--------------------------------------------------------------
        //'---------------------------------------------------------------------------
        //' Build a second API request to PayPal, using the token as the
        //'  ID to get the details on the payment authorization
        //'---------------------------------------------------------------------------
        $nvpstr = "&TOKEN=" . $token;

        //'---------------------------------------------------------------------------
        //' Make the API call and store the results in an array.  
        //'	If the call was a success, show the authorization details, and provide an action to complete the payment.  
        //'	If failed, show the error
        //'---------------------------------------------------------------------------
        $resArray = $this->hash_call("GetExpressCheckoutDetails", $nvpstr);

        $ack = strtoupper($resArray["ACK"]);

        if ($ack == "SUCCESS" || $ack == "SUCCESSWITHWARNING") {
            return $resArray;
        }
        else
            return false;
    }

    /*
      '-------------------------------------------------------------------------------------------------------------------------------------------
      ' Purpose: 	Prepares the parameters for the GetExpressCheckoutDetails API Call.
      '
      ' Inputs:
      '		sBNCode:	The BN code used by PayPal to track the transactions from a given shopping cart.
      ' Returns:
      '		The NVP Collection object of the GetExpressCheckoutDetails Call Response.
      '--------------------------------------------------------------------------------------------------------------------------------------------
     */

    function ConfirmPayment($token, $paymentType, $currencyCodeType, $payerID, $FinalPaymentAmt) {

        /* Gather the information to make the final call to finalize the PayPal payment.  The variable nvpstr holds the name value pairs */
        $token = urlencode($token);
        $paymentType = urlencode($paymentType);
        $currencyCodeType = urlencode($currencyCodeType);
        $payerID = urlencode($payerID);
        $serverName = urlencode($_SERVER['SERVER_NAME']);

        $nvpstr = '&TOKEN=' . $token . '&PAYERID=' . $payerID . '&PAYMENTREQUEST_0_PAYMENTACTION=' . $paymentType . '&PAYMENTREQUEST_0_AMT=' . $FinalPaymentAmt;
        $nvpstr .= '&PAYMENTREQUEST_0_CURRENCYCODE=' . $currencyCodeType . '&IPADDRESS=' . $serverName;

        /* Make the call to PayPal to finalize payment
          If an error occured, show the resulting errors
         */
        $resArray = $this->hash_call("DoExpressCheckoutPayment", $nvpstr);

        /* Display the API response back to the browser.
          If the response from PayPal was a success, display the response parameters'
          If the response was an error, display the errors received using APIError.php.
         */

        return $resArray;
    }

    /**
      '-------------------------------------------------------------------------------------------------------------------------------------------
     * hash_call: Function to perform the API call to PayPal using API signature
     * @methodName is name of API  method.
     * @nvpStr is nvp string.
     * returns an associtive array containing the response from the server.
      '-------------------------------------------------------------------------------------------------------------------------------------------
     */
    function hash_call($methodName, $nvpStr) {
        //setting the curl parameters.
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->API_Endpoint);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);

        //turning off the server and peer verification(TrustManager Concept).
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);

        //if USE_PROXY constant set to TRUE in Constants.php, then only proxy will be enabled.
        //Set proxy name to PROXY_HOST and port number to PROXY_PORT in constants.php 
        if ($this->USE_PROXY)
            curl_setopt($ch, CURLOPT_PROXY, $this->PROXY_HOST . ":" . $this->PROXY_PORT);

        //NVPRequest for submitting to server
        $nvpreq = "METHOD=" . urlencode($methodName) . "&VERSION=" . urlencode($this->version) . "&PWD=" . urlencode($this->API_Password) . "&USER=" . urlencode($this->API_UserName) . "&SIGNATURE=" . urlencode($this->API_Signature) . $nvpStr . "&BUTTONSOURCE=" . urlencode($this->sBNCode);

        //setting the nvpreq as POST FIELD to curl
        curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);
        //getting response from server
        $response = curl_exec($ch);

        //convrting NVPResponse to an Associative Array
        $nvpResArray = $this->deformatNVP($response);
        $nvpReqArray = $this->deformatNVP($nvpreq);
        $_SESSION['nvpReqArray'] = $nvpReqArray;

        if (curl_errno($ch)) {

            // moving to display page to display curl errors
            $_SESSION['curl_error_no'] = curl_errno($ch);
            $_SESSION['curl_error_msg'] = curl_error($ch);

            // Execute the Error handling module to display errors. 
        } else {

            //closing the curl
            curl_close($ch);
        }

        return $nvpResArray;
    }

    /* '----------------------------------------------------------------------------------
      Purpose: Redirects to PayPal.com site.
      Inputs:  NVP string.
      Returns:
      ----------------------------------------------------------------------------------
     */

    function RedirectToPayPal($token) {

        // Redirect to paypal.com here
        header("Location: " . $this->PAYPAL_URL . $token);
        exit;
    }

    function RedirectToPayPalDG($token) {

        $redirect_url = $this->PAYPAL_DG_URL . $token;

        // Redirect to paypal.com here
        header("Location: " . $redirect_url);
        exit;
    }

    /* '----------------------------------------------------------------------------------
     * This function will take NVPString and convert it to an Associative Array and it will decode the response.
     * It is usefull to search for a particular key and displaying arrays.
     * @nvpstr is NVPString.
     * @nvpArray is Associative Array.
      ----------------------------------------------------------------------------------
     */

    function deformatNVP($nvpstr) {
        $intial = 0;
        $nvpArray = array();

        while (strlen($nvpstr)) {
            //postion of Key
            $keypos = strpos($nvpstr, '=');
            //position of value
            $valuepos = strpos($nvpstr, '&') ? strpos($nvpstr, '&') : strlen($nvpstr);

            /* getting the Key and Value values and storing in a Associative Array */
            $keyval = substr($nvpstr, $intial, $keypos);
            $valval = substr($nvpstr, $keypos + 1, $valuepos - $keypos - 1);
            //decoding the respose
            $nvpArray[urldecode($keyval)] = urldecode($valval);
            $nvpstr = substr($nvpstr, $valuepos + 1, strlen($nvpstr));
        }
        return $nvpArray;
    }

    static function getResultOfTransaction($result_ConfirmPayment) {

        $transactionResult = array();

        $ack = strtoupper($result_ConfirmPayment["ACK"]);

        if ($ack == "SUCCESS" || $ack == "SUCCESSWITHWARNING") {

            /*
             * TODO: Proceed with desired action after the payment (ex: start download, start streaming, Add coins to the game.. etc)
              '********************************************************************************************************************
              '
              ' THE PARTNER SHOULD SAVE THE KEY TRANSACTION RELATED INFORMATION LIKE "transactionId & orderTime"
              '  IN THEIR OWN  DATABASE AND THE REST OF THE INFORMATION CAN BE USED TO UNDERSTAND THE STATUS OF THE PAYMENT
              '********************************************************************************************************************
             */

            // Unique transaction ID of the payment.
            $transactionResult['transactionId'] = $result_ConfirmPayment["PAYMENTINFO_0_TRANSACTIONID"];

            // The type of transaction Possible values: l  cart l  express-checkout
            $transactionResult['transactionType'] = $result_ConfirmPayment["PAYMENTINFO_0_TRANSACTIONTYPE"];

            // Indicates whether the payment is instant or delayed. Possible values: l  none l  echeck l  instant
            $transactionResult['paymentType'] = $result_ConfirmPayment["PAYMENTINFO_0_PAYMENTTYPE"];

            // Time/date stamp of payment
            $transactionResult['orderTime'] = $result_ConfirmPayment["PAYMENTINFO_0_ORDERTIME"];

            // The final amount charged, including any  taxes from your Merchant Profile.
            $transactionResult['amt'] = $result_ConfirmPayment["PAYMENTINFO_0_AMT"];

            // A three-character currency code for one of the currencies listed in PayPay-Supported Transactional Currencies. Default: USD.
            $transactionResult['currencyCode'] = $result_ConfirmPayment["PAYMENTINFO_0_CURRENCYCODE"];

            // PayPal fee amount charged for the transaction
            $transactionResult['feeAmt'] = $result_ConfirmPayment["PAYMENTINFO_0_FEEAMT"];

            // Amount deposited in your PayPal account after a currency conversion.
            //	$transactionResult['settleAmt'] = $result_ConfirmPayment["PAYMENTINFO_0_SETTLEAMT"];
            // Tax charged on the transaction.
            $transactionResult['taxAmt'] = $result_ConfirmPayment["PAYMENTINFO_0_TAXAMT"];

            // Exchange rate if a currency conversion occurred. 
            // Relevant only if your are billing in their non-primary currency. 
            // If the customer chooses to pay with a currency other than the non-primary currency, the conversion occurs in the customer's account.
            //$transactionResult['exchangeRate'] = $result_ConfirmPayment["PAYMENTINFO_0_EXCHANGERATE"];


            /*
              ' Status of the payment:
              'Completed: The payment has been completed, and the funds have been added successfully to your account balance.
              'Pending: The payment is pending. See the PendingReason element for more information.
             */
            $transactionResult['paymentStatus'] = $result_ConfirmPayment["PAYMENTINFO_0_PAYMENTSTATUS"];

            /*
              'The reason the payment is pending:
              '  none: No pending reason
              '  address: The payment is pending because your customer did not include a confirmed shipping address and your Payment Receiving Preferences is set such that you want to manually accept or deny each of these payments. To change your preference, go to the Preferences section of your Profile.
              '  echeck: The payment is pending because it was made by an eCheck that has not yet cleared.
              '  intl: The payment is pending because you hold a non-U.S. account and do not have a withdrawal mechanism. You must manually accept or deny this payment from your Account Overview.
              '  multi-currency: You do not have a balance in the currency sent, and you do not have your Payment Receiving Preferences set to automatically convert and accept this payment. You must manually accept or deny this payment.
              '  verify: The payment is pending because you are not yet verified. You must verify your account before you can accept this payment.
              '  other: The payment is pending for a reason other than those listed above. For more information, contact PayPal customer service.
             */
            $transactionResult['pendingReason'] = $result_ConfirmPayment["PAYMENTINFO_0_PENDINGREASON"];

            /*
              'The reason for a reversal if TransactionType is reversal:
              '  none: No reason code
              '  chargeback: A reversal has occurred on this transaction due to a chargeback by your customer.
              '  guarantee: A reversal has occurred on this transaction due to your customer triggering a money-back guarantee.
              '  buyer-complaint: A reversal has occurred on this transaction due to a complaint about the transaction from your customer.
              '  refund: A reversal has occurred on this transaction because you have given the customer a refund.
              '  other: A reversal has occurred on this transaction due to a reason not listed above.
             */
            $transactionResult['reasonCode'] = $result_ConfirmPayment["PAYMENTINFO_0_REASONCODE"];

            return $transactionResult;
        }
    }

}

?>