<?php
   
   //SandBox Info
   define("PAYGATE_PAYPAL_SANDBOX_ACTIVE",true); //metti a false per entrare in produzione
   define("PAYGATE_PAYPAL_API_SBN_CODE","PP-ECWizard");

   //SandBox Conf
   define("PAYGATE_PAYPAL_API_SANDBOX_USERNAME","andrea_1319484923_biz_api1.gmail.com");
   define("PAYGATE_PAYPAL_API_SANDBOX_PASSWORD","1319484953");
   define("PAYGATE_PAYPAL_API_SANDBOX_SIGNATURE","AjZ7vwvlUVUMdXCpf1f6.ETaZ0G-AJahT-n3I1WpZLBpW1HBmb36LcI.");
   define("PAYGATE_PAYPAL_API_SANDBOX_ENDPOINT","https://api-3t.sandbox.paypal.com/nvp");
   define("PAYGATE_PAYPAL_API_SANDBOX_PAY_URL","https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&token=");

   //Produzione
   define("PAYGATE_PAYPAL_API_USERNAME","andrea_1319484923_biz_api1.gmail.com");
   define("PAYGATE_PAYPAL_API_PASSWORD","1319484953");
   define("PAYGATE_PAYPAL_API_SIGNATURE","AjZ7vwvlUVUMdXCpf1f6.ETaZ0G-AJahT-n3I1WpZLBpW1HBmb36LcI.");
   define("PAYGATE_PAYPAL_API_ENDPOINT","https://api-3t.paypal.com/nvp");
   define("PAYGATE_PAYPAL_API_PAY_URL","https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=");

   define("PAYGATE_PAYPAL_ID","paypal");
   define("PAYGATE_PAYPAL_CURRENCY_CODE","EUR");
   define("PAYGATE_PAYPAL_PAYMENT_TYPE","Sale");
   define("PAYGATE_PAYPAL_DEFAULT_AMOUNT","0.00");
   define("PAYGATE_PAYPAL_SUCCESS_URL",HTTP_SITE.HTTP_ROOT."/?action=pay_response");
   define("PAYGATE_PAYPAL_CANCEL_URL",HTTP_SITE.HTTP_ROOT."/?action=pay_response");
   define("PAYGATE_PAYPAL_SECURE_MODE",true);
   define("PAYGATE_PAYPAL_KEY","Alasmdadasi0123412312m3QKJJQWEIQW99234M2M3KFMWLDF");
           
           
           
           