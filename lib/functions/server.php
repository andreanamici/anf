<?php

if( !function_exists('server_request_headers') ) 
{
        /**
         * Restituisce le informazioni headers della chiamata HTTP
         * 
         * @param array $server [OPZIONALE] $_SERVER superglobal
         * 
         * @return array
         */
        function server_request_headers($server = null) 
        {
                $arh = array();
                $rx_http = '/\AHTTP_/';
                $server  = is_null($server) ? $_SERVER : $server;
                foreach($server as $key => $val) {
                        if( preg_match($rx_http, $key) ) {
                                $arh_key = preg_replace($rx_http, '', $key);
                                $rx_matches = array();
                                // do some nasty string manipulations to restore the original letter case
                                // this should work in most cases
                                $rx_matches = explode('_', strtolower($arh_key));
                                if( count($rx_matches) > 0 and strlen($arh_key) > 2 ) {
                                        foreach($rx_matches as $ak_key => $ak_val) $rx_matches[$ak_key] = ucfirst($ak_val);
                                        $arh_key = implode('-', $rx_matches);
                                }
                                $arh[$arh_key] = $val;
                        }
                }
                if(isset($server['CONTENT_TYPE']))   $arh['Content-Type']     = $server['CONTENT_TYPE'];
                if(isset($server['CONTENT_LENGTH'])) $arh['Content-Length']   = $server['CONTENT_LENGTH'];
                return( $arh );
        }
}