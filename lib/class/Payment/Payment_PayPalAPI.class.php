<?php

/********************************************
PayPal API Module Class
 ********************************************/

class Payment_PayPalAPI extends Exception_ExceptionHandlers
{

    protected $_version = "2.3";

    protected $_use_proxy = false;

    protected $_proxy_host = '';

    protected $_proxy_port = '';

    protected $_sandbox_flag = PAYGATE_PAYPAL_SANDBOX_ACTIVE;

    //'------------------------------------
    //' PayPal API Credentials
    //'------------------------------------
    protected $_api_username;

    protected $_api_password;

    protected $_api_signature;

    // BN Code 	is only applicable for partners
    protected $_sbn_code = PAYGATE_PAYPAL_API_SBN_CODE;

    protected $_api_endpoint = "";

    protected $_paypal_url = "";

    function __construct () 
    {
        parent::__construct();
        /*
         ' Define the PayPal Redirect URLs.
         ' 	This is the URL that the buyer is first sent to do authorize payment with their paypal account
         ' 	change the URL depending if you are testing on the sandbox or the live PayPal site
         '
         ' For the sandbox, the URL is       https://www.sandbox.paypal.com/webscr&cmd=_express-checkout&token=
         ' For the live site, the URL is        https://www.paypal.com/webscr&cmd=_express-checkout&token=
         */
        if ($this->_sandbox_flag) 
        {
            $this->_api_endpoint  = PAYGATE_PAYPAL_API_SANDBOX_ENDPOINT;
            $this->_paypal_url    = PAYGATE_PAYPAL_API_SANDBOX_PAY_URL;
            $this->_api_username  = PAYGATE_PAYPAL_API_SANDBOX_USERNAME;
            $this->_api_password  = PAYGATE_PAYPAL_API_SANDBOX_PASSWORD;
            $this->_api_signature = PAYGATE_PAYPAL_API_SANDBOX_SIGNATURE;
        } 
        else 
        {
            $this->_api_endpoint  = PAYGATE_PAYPAL_API_ENDPOINT;
            $this->_paypal_url    = PAYGATE_PAYPAL_API_PAY_URL;
            $this->_api_username  = PAYGATE_PAYPAL_API_USERNAME;
            $this->_api_password  = PAYGATE_PAYPAL_API_PASSWORD;
            $this->_api_signature = PAYGATE_PAYPAL_API_SIGNATURE;
        }
        if (session_id() == "") session_start();
    }

    public function  __destruct() {
        parent::__destruct();
        unset($this);
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
	' Purpose: 	Prepares the parameters for the SetExpressCheckout API Call.
	' Inputs:
	'		paymentAmount:  	Total value of the shopping cart
	'		currencyCodeType: 	Currency code value the PayPal API
	'		paymentType: 		paymentType has to be one of the following values: Sale or Order or Authorization
	'		returnURL:			the page where buyers return to after they are done with the payment review on PayPal
	'		cancelURL:			the page where buyers return to when they cancel the payment review on PayPal
	'--------------------------------------------------------------------------------------------------------------------------------------------
	*/
    public function CallShortcutExpressCheckout ($paymentAmount, $currencyCodeType, $paymentType, $returnURL, $cancelURL, $lang="it_IT") {

        //------------------------------------------------------------------------------------------------------------------------------------
        // Construct the parameter string that describes the SetExpressCheckout API call in the shortcut implementation
        $nvpstr = "&Amt=" . $paymentAmount;
        $nvpstr = $nvpstr . "&PAYMENTACTION=" . $paymentType;
        $nvpstr = $nvpstr . "&ReturnUrl=" . $returnURL;
        $nvpstr = $nvpstr . "&CANCELURL=" . $cancelURL;
        $nvpstr = $nvpstr . "&CURRENCYCODE=" . $currencyCodeType;
        $nvpstr = $nvpstr . '&SOLUTIONTYPE=Sole&LANDINGPAGE=Billing';
        $nvpstr = $nvpstr . "&LOCALECODE=".$lang;
        //$_SESSION["currencyCodeType"] = $currencyCodeType;
        //$_SESSION["PaymentType"] = $paymentType;
        //'---------------------------------------------------------------------------------------------------------------
        //' Make the API call to PayPal
        //' If the API call succeded, then redirect the buyer to PayPal to begin to authorize payment.
        //' If an error occured, show the resulting errors
        //'---------------------------------------------------------------------------------------------------------------

        $resArray = $this->_hash_call("SetExpressCheckout", $nvpstr);
        $ack = strtoupper($resArray["ACK"]);
        if ($ack == "SUCCESS") {
            $token = urldecode($resArray["TOKEN"]);
            $_SESSION['TOKEN'] = $token;
        }
        return $resArray;
    }

    /*
	'-------------------------------------------------------------------------------------------------------------------------------------------
	' Purpose: 	Prepares the parameters for the SetExpressCheckout API Call.
	' Inputs:
	'		paymentAmount:  	Total value of the shopping cart
	'		currencyCodeType: 	Currency code value the PayPal API
	'		paymentType: 		paymentType has to be one of the following values: Sale or Order or Authorization
	'		returnURL:			the page where buyers return to after they are done with the payment review on PayPal
	'		cancelURL:			the page where buyers return to when they cancel the payment review on PayPal
	'		shipToName:		the Ship to name entered on the merchant's site
	'		shipToStreet:		the Ship to Street entered on the merchant's site
	'		shipToCity:			the Ship to City entered on the merchant's site
	'		shipToState:		the Ship to State entered on the merchant's site
	'		shipToCountryCode:	the Code for Ship to Country entered on the merchant's site
	'		shipToZip:			the Ship to ZipCode entered on the merchant's site
	'		shipToStreet2:		the Ship to Street2 entered on the merchant's site
	'		phoneNum:			the phoneNum  entered on the merchant's site
	'--------------------------------------------------------------------------------------------------------------------------------------------
	*/
    public function CallMarkExpressCheckout ($paymentAmount, $currencyCodeType, $paymentType, $returnURL, $cancelURL, $shipToName, $shipToStreet, $shipToCity, $shipToState, $shipToCountryCode, $shipToZip, $shipToStreet2, $phoneNum) {

        //------------------------------------------------------------------------------------------------------------------------------------
        // Construct the parameter string that describes the SetExpressCheckout API call in the shortcut implementation
        $nvpstr = "&Amt=" . $paymentAmount;
        $nvpstr = $nvpstr . "&PAYMENTACTION=" . $paymentType;
        $nvpstr = $nvpstr . "&ReturnUrl=" . $returnURL;
        $nvpstr = $nvpstr . "&CANCELURL=" . $cancelURL;
        $nvpstr = $nvpstr . "&CURRENCYCODE=" . $currencyCodeType;
        $nvpstr = $nvpstr . "&ADDROVERRIDE=1";
        $nvpstr = $nvpstr . "&SHIPTONAME=" . $shipToName;
        $nvpstr = $nvpstr . "&SHIPTOSTREET=" . $shipToStreet;
        $nvpstr = $nvpstr . "&SHIPTOSTREET2=" . $shipToStreet2;
        $nvpstr = $nvpstr . "&SHIPTOCITY=" . $shipToCity;
        $nvpstr = $nvpstr . "&SHIPTOSTATE=" . $shipToState;
        $nvpstr = $nvpstr . "&SHIPTOCOUNTRYCODE=" . $shipToCountryCode;
        $nvpstr = $nvpstr . "&SHIPTOZIP=" . $shipToZip;
        $nvpstr = $nvpstr . "&PHONENUM=" . $phoneNum;
        $_SESSION["currencyCodeType"] = $currencyCodeType;
        $_SESSION["PaymentType"] = $paymentType;
        //'---------------------------------------------------------------------------------------------------------------
        //' Make the API call to PayPal
        //' If the API call succeded, then redirect the buyer to PayPal to begin to authorize payment.
        //' If an error occured, show the resulting errors
        //'---------------------------------------------------------------------------------------------------------------
        $resArray = $this->_hash_call("SetExpressCheckout", $nvpstr);
        $ack = strtoupper($resArray["ACK"]);
        if ($ack == "SUCCESS") {
            $token = urldecode($resArray["TOKEN"]);
            $_SESSION['TOKEN'] = $token;
        }
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
    public function GetShippingDetails ($token) {

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
        //'	If the call was a success, show the authorization details, and provide
        //' 	an action to complete the payment.
        //'	If failed, show the error
        //'---------------------------------------------------------------------------
        $resArray = $this->_hash_call("GetExpressCheckoutDetails", $nvpstr);
        $ack = strtoupper($resArray["ACK"]);
        if ($ack == "SUCCESS") {
            $_SESSION['payer_id'] = $resArray['PAYERID'];
        }
        return $resArray;
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
    public function ConfirmPayment ($FinalPaymentAmt, $token, $paymentType, $currencyCodeType, $payerID) {

        /* Gather the information to make the final call to
		   finalize the PayPal-3t payment.  The variable nvpstr
		   holds the name value pairs
		   */
        $serverName = urlencode($_SERVER['SERVER_NAME']);
        $nvpstr = '&TOKEN=' . urlencode($token) . '&PAYERID=' . urlencode($payerID) . '&PAYMENTACTION=' . urlencode($paymentType) . '&AMT=' . urlencode($FinalPaymentAmt);
        $nvpstr .= '&CURRENCYCODE=' . urlencode($currencyCodeType) . '&IPADDRESS=' . $serverName;
        /* Make the call to PayPal to finalize payment
		    If an error occured, show the resulting errors
		    */
        $resArray = $this->_hash_call("DoExpressCheckoutPayment", $nvpstr);
        /* Display the API response back to the browser.
		   If the response from PayPal was a success, display the response parameters'
		   If the response was an error, display the errors received using APIError.php.
		   */
        $ack = strtoupper($resArray["ACK"]);
        return $resArray;
    }

    /*'----------------------------------------------------------------------------------
	 Purpose: Redirects to PayPal.com site.
	 Inputs:  NVP string.
	 Returns:
	----------------------------------------------------------------------------------
	*/
    public function RedirectToPayPal ($token) {

        // Redirect to paypal.com here
        redirect($this->_paypal_url . $token, 0);
    }

    /**
	  '-------------------------------------------------------------------------------------------------------------------------------------------
     * hash_call: Function to perform the API call to PayPal using API signature
     * @methodName is name of API  method.
     * @nvpStr is nvp string.
     * returns an associtive array containing the response from the server.
	  '-------------------------------------------------------------------------------------------------------------------------------------------
     */
    protected function _hash_call ($methodName, $nvpStr) {

        //setting the curl parameters.
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->_api_endpoint);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        //turning off the server and peer verification(TrustManager Concept).
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        //if USE_PROXY constant set to TRUE in Constants.php, then only proxy will be enabled.
        //Set proxy name to PROXY_HOST and port number to PROXY_PORT in constants.php
        if ($this->_use_proxy) curl_setopt($ch, CURLOPT_PROXY, $this->_proxy_host . ":" . $this->_proxy_port);
        //NVPRequest for submitting to server
        $nvpreq = "METHOD=" . urlencode($methodName) . "&VERSION=" . urlencode($this->_version) . "&PWD=" . urlencode($this->_api_password) . "&USER=" . urlencode($this->_api_username) . "&SIGNATURE=" . urlencode($this->_api_signature) . $nvpStr . "&BUTTONSOURCE=" . urlencode($this->_sbn_code);
        //setting the nvpreq as POST FIELD to curl
        curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);
        //getting response from server
        $response = curl_exec($ch);
        //convrting NVPResponse to an Associative Array
        $nvpResArray = $this->_deformat_nvp($response);
        $nvpReqArray = $this->_deformat_nvp($nvpreq);
        $_SESSION['nvpReqArray'] = $nvpReqArray;
        if (curl_errno($ch)) {
            // moving to display page to display curl errors
            $_SESSION['curl_error_no'] = curl_errno($ch);
            $_SESSION['curl_error_msg'] = curl_error($ch);
            //Execute the Error handling module to display errors.
        } else {
            //closing the curl
            curl_close($ch);
        }
        return $nvpResArray;
    }

    /*'----------------------------------------------------------------------------------
	 * This function will take NVPString and convert it to an Associative Array and it will decode the response.
	  * It is usefull to search for a particular key and displaying arrays.
	  * @nvpstr is NVPString.
	  * @nvpArray is Associative Array.
	   ----------------------------------------------------------------------------------
	  */
    protected function _deformat_NVP ($nvpstr) {

        $intial = 0;
        $nvpArray = array();
        while (strlen($nvpstr)) {
            //postion of Key
            $keypos = strpos($nvpstr, '=');
            //position of value
            $valuepos = strpos($nvpstr, '&') ? strpos($nvpstr, '&') : strlen($nvpstr);
            /*getting the Key and Value values and storing in a Associative Array*/
            $keyval = substr($nvpstr, $intial, $keypos);
            $valval = substr($nvpstr, $keypos + 1, $valuepos - $keypos - 1);
            //decoding the respose
            $nvpArray[urldecode($keyval)] = urldecode($valval);
            $nvpstr = substr($nvpstr, $valuepos + 1, strlen($nvpstr));
        }
        return $nvpArray;
    }
}