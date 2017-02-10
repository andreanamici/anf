<?php

/**
 * Trait utile per l'accesso al gestore delle traduzioni
 */
trait Trait_ApplicationLanguages
{
   /**
    * Restituisce il gestore delle traduzioni
    * 
    * @return Application_Languages
    */
   public static function getApplicationLanguages()
   {
      return \ApplicationKernel::getInstance()->getApplicationLanguages();
   }
   
   /**
    * Traduce un messaggio
    * 
    * @return String
    * 
    * @see Application_Languages::translate()
    */
   public static function _t()
   {
       return call_user_func_array(array(self::getApplicationLanguages(),'translate'),  func_get_args());
   }
}