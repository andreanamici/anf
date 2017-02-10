<?php

class Utility_Cript
{
    
    private static $_mcrypt_rand = MCRYPT_RAND;
    private static $_mcrypt_key  = MCRIPT_SECRET_KEY;
    private static $_str_append  = "&enc=1";
    
    
    /**
     * Cripta una Stringa utilizzando mcrypy_module cast-256+base64
     * @param String $string Stringa da criptare
     * @return String Stringa criptata
     */
    public function encryptString($string)
    {
        $key = self::$_mcrypt_key;
        $td = mcrypt_module_open('cast-256', '', 'ecb', '');
        $iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td),self::$_mcrypt_rand);
        mcrypt_generic_init($td, $key, $iv);
        //$string.=self::$_str_append;
        $encrypted_data = mcrypt_generic($td, $string);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        $encoded_64=$this->strToHex(base64_encode($encrypted_data));
        return $encoded_64;
    }

    /**
     * Decripta una stringa codificata con il metodo della medesima classe
     * @see encrypt_string
     * @param  String $string Stringa criptata
     * @return String Stringa decriptata
     */
    public function decryptString($string)
    {
        if(strlen($string)==0) return "";
        $decoded_64=base64_decode($this->hexToStr($string));
        $key = self::$_mcrypt_key;
        $td = mcrypt_module_open('cast-256', '', 'ecb', '');
        $iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td),self::$_mcrypt_rand);
        mcrypt_generic_init($td, $key, $iv);
        $decrypted_data = mdecrypt_generic($td, $decoded_64);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        return trim($decrypted_data);
    }

    
    private function strToHex($string)
    {
        $hex='';
        for ($i=0; $i < strlen($string); $i++){
            $hex .= dechex(ord($string[$i]));
        }
        return $hex;
    }

    private function hexToStr($hex)
    {
        $string='';
        for ($i=0; $i < strlen($hex)-1; $i+=2){
            $string .= chr(hexdec($hex[$i].$hex[$i+1]));
        }
        return $string;
    }
}

