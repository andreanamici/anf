<?php

final class Payment_PayPal extends Payment_PayPalAPI
{
   
    use Trait_DAO;
   
    private static $_paypal_sql_table_name = "pie_paypal_express_checkout";
    
    private static $_paygate_id      = PAYGATE_PAYPAL_ID;
    private static $_currency_code   = PAYGATE_PAYPAL_CURRENCY_CODE;
    private static $_pay_type        = PAYGATE_PAYPAL_PAYMENT_TYPE;
    private static $_pay_success_url = PAYGATE_PAYPAL_SUCCESS_URL;
    private static $_pay_cancel_url  = PAYGATE_PAYPAL_CANCEL_URL;
    private static $_pay_key_enc     = PAYGATE_PAYPAL_KEY;
    private static $_pay_secure_mode = PAYGATE_PAYPAL_SECURE_MODE;
        
    private $_paygate    = null;
    private $_bene_id    = null;
    private $_price      = null;
    private $_datetime   = null;
    private $_users_info = null;
    private $_pay_obj    = null;
    private $_token      = null;
    private $_payer_id   = null;
    private $_setParameter = null;
    private $_paypal_info_arr = null;


    private function setBeneId($val)
    {
        return $this->_bene_id = $val;
    }

    private function setBenePrice($val)
    {
       return $this->_price = $val;//$this->_formatPrice($val);
    }

    private function setPayerId($val)
    {
        return $this->_payer_id = $val;
    }

    private function _formatPrice($price)
    {
        return Utility_CommonFunction::Numeric_Format($price,"",".",3,0);
    }

    private function setUsersInfo($usr_id,$usr_email)
    {
        $this->_users_info = Array();
        $this->_users_info["usr_id"] = $usr_id;
        
        if(Utility_CommonFunction::String_isValidEmail($usr_email)){
            return $this->_users_info["usr_email"] = $usr_email;
        }
        
        return self::throwNewException(83748623453874,"Email Utile per il pagamento non Valida!");
    }

    public function  __construct($token=null,$payer_id=null)
    {
        parent::__construct();
        
        $this->initDAO();
        
        $this->_token     = $token;
        $this->_payer_id  = $payer_id;
        
        return true;
    }

    public function  __destruct()
    {
        parent::__destruct();
        unset($this);
        return true;
    }

    public function setPaymentInfo($bene_id,$bene_price,$usr_email,$usr_id=null)
    {
       $this->setBeneId($bene_id);
       $this->setBenePrice($bene_price);
       $this->setUsersInfo($usr_id,$usr_email);
       $this->_setParameter = true;
       return $this->_setParameter;
    }

    public function authorizePayment($payLang='it')
    {
        if(!$this->_setParameter) return false;
 
        $key        = md5(self::$_pay_key_enc.$this->_users_info["usr_id"]); //checksum string
        $secureMode = (self::$_pay_secure_mode==1) ? "&key=".$key       : "";

        $divers     = base64_encode("paygate_id=".self::$_paygate_id."&bene_id=".$this->_bene_id."&usr_email=".$this->_users_info["usr_email"]."&usr_id=".$this->_users_info["usr_email"]."&lang=".$payLang.$secureMode);

        $returnURL = "http://".self::$_pay_success_url."&divers=".$divers;
        $cancelURL = "http://".self::$_pay_cancel_url."&divers=".$divers;

        $resArray = $this->CallShortcutExpressCheckout($this->_price, self::$_currency_code, self::$_pay_type, $returnURL,$cancelURL,$payLang);
        $ack = strtoupper($resArray["ACK"]);

        if ($ack == "SUCCESS")
        {
           $this->_token = urldecode($resArray["TOKEN"]);
           if(!$this->storeTransaction())
               return self::throwNewException("12381023810238012381","Impossibile Archiviare la transazione!");
           $this->_session_obj->addIndex("token",$this->_token);
           return $this->RedirectToPayPal($this->_token);
        }
        else
        {
           var_dump($resArray);
           $alert = "Non è stato possibile iniziare la transazione<br />";
           //Display a user friendly Error on the page using any of the following error information returned by PayPal
           $ErrorCode = urldecode ( $resArray ["L_ERRORCODE0"] );
           $ErrorShortMsg = urldecode ( $resArray ["L_SHORTMESSAGE0"] );
           $ErrorLongMsg = urldecode ( $resArray ["L_LONGMESSAGE0"] );
           $ErrorSeverityCode = urldecode ( $resArray ["L_SEVERITYCODE0"] );
           echo "<br />";
           echo "SetExpressCheckout API call failed. " . "<br />";
           echo "Detailed Error Message: " . $ErrorLongMsg . "<br />";
           echo "Short Error Message: " . $ErrorShortMsg . "<br />";
           echo "Error Code: " . $ErrorCode . "<br />";
           echo "Error Severity Code: " . $ErrorSeverityCode . "<br />";
       }


    }

    public function executePayment($payLang='it')
    {
        if(!$this->_setParameter) return false;

        $this->_paypal_info_arr = $this->GetShippingDetails($this->_token);
        
        if(strtoupper($this->_paypal_info_arr["ACK"])=="SUCCESS")
        {
           $resArray = $this->ConfirmPayment($this->_price, $this->_token,self::$_pay_type,self::$_currency_code,$this->_payer_id);
           if(strtoupper($resArray["ACK"])=="SUCCESS")
               $this->_paypal_info_arr["trs_status"] = "OK";
           else
               $this->_paypal_info_arr["trs_status"] = "KO";

           if($this->storeTransaction('update'))
           {
              return $this->_paypal_info_arr["trs_status"]=="OK";
           }
           
           return self::throwNewException(8834623587419834,"Impossibile storare la Transazione!");
        }
        return false;
    }

    public function cancelPayment()
    {
        $this->_paypal_info_arr["trs_status"] = "KO";
        if($this->storeTransaction('cancel'))
            return true;
        return false;
    }

    private function storeTransaction($action='insert')
    {
        
        switch($action)
        {
            case 'insert':
                             $arrInfo      = Array("id_prodotto"=>$this->_bene_id,"token"=>$this->_token,"trs_fund"=>$this->_price,"timestamp"=>date("Y-m-d H:i:s"));
                             $sqlStore = $this->_sqlBuilder->BuildSqlInsert(self::$_paypal_sql_table_name,$arrInfo);
                          break;
           case 'update':
                             $arrInfo     = Array("timestamp"             => date("Y-m-d H:i:s"),
                                                  "trs_status"            => $this->_paypal_info_arr["trs_status"],
                                                  "correlaction_payer_id" => $this->_paypal_info_arr["CORRELATIONID"],
                                                  "email"                 => $this->_paypal_info_arr["EMAIL"],
                                                  "payer_id"              => $this->_paypal_info_arr["PAYERID"],
                                                  "payer_status"          => $this->_paypal_info_arr["PAYERSTATUS"],
                                                  "payer_first_name"      => $this->_paypal_info_arr["FIRSTNAME"],
                                                  "payer_last_name"       => $this->_paypal_info_arr["LASTNAME"],
                                                  "payer_country_code"    => $this->_paypal_info_arr["COUNTRYCODE"],
                                                  "ship_to_name"          => $this->_paypal_info_arr["SHIPTONAME"],
                                                  "ship_to_street"        => $this->_paypal_info_arr["SHIPTOSTREET"],
                                                  "ship_to_city"          => $this->_paypal_info_arr["SHIPTOCITY"],
                                                  "ship_to_state"         => $this->_paypal_info_arr["SHIPTOSTATE"],
                                                  "ship_to_zip"           => $this->_paypal_info_arr["SHIPTOZIP"],
                                                  "ship_to_country_code"  => $this->_paypal_info_arr["SHIPTOCOUNTRYCODE"],
                                                  "ship_to_country_name"  => $this->_paypal_info_arr["SHIPTOCOUNTRYNAME"],
                                                  "address_status"        => $this->_paypal_info_arr["ADDRESSSTATUS"]);
                             $sqlStore = $this->_sqlBuilder->BuildSqlUpdate(self::$_paypal_sql_table_name,$arrInfo,array("token"=>"='".$this->_token."'","id_prodotto"=>"=".$this->_bene_id));
                          break;
            case 'cancel':
                            $sqlStore = $this->_sqlBuilder->BuildSqlUpdate(self::$_paypal_sql_table_name,array("trs_status"=>$this->_paypal_info_arr["trs_status"]),array("token"=>"='".$this->_token."'"));
                          break;
        }
        
        $res = $this->_db->exeQuery($sqlStore);
        return $res;
    }

}

