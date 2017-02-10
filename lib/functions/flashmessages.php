<?php

require_once 'application.php';

if(!function_exists('flash_clear'))
{
   /**
    * Pulisce i dati flash specificati in session
    * 
    * @param String $index chiave dati flash
    * 
    * @return Boolean
    */
   function flash_clear($data)
   { 
      return getApplicationSessionManager()->getFlashData($data,false)!==false;
   }
}


if(!function_exists('flash'))
{
   /**
    * Stampa i dati flash specificati in session
    * 
    * @param String $index chiave dati flash
    * 
    * @return Void
    */
   function flash($data,$default = false)
   {
      echo getApplicationSessionManager()->getFlashData($data,$default);
   }
}


if(!function_exists('flash_isset'))
{
   /**
    * Verifica l'esistenza  dei dati flash specificati in session
    * 
    * @param String $index chiave dati flash
    * 
    * @return Boolean
    */
   function flash_isset($data,$default = false)
   {
      if(getApplicationSessionManager()->getFlashData($data,$default,false)!==false)
      {
         return true;
      }

      return false;
   }
}


if(!function_exists('flash_get'))
{
   /**
    * Restituisce i dati flash specificati in session
    * <b> Verranno cancellati appena restituiti </b>
    * 
    * @param String $index chiave dati flash
    * 
    * @return Mixed
    */
   function flash_get($data,$default = false)
   {
      return getApplicationSessionManager()->getFlashData($data,$default);
   }
}


if(!function_exists('flash_set'))
{

   /**
    * [FUNCTION ALIAS]
    * 
    * Restituisce i dati flash specificati in session
    * 
    * @param String $index chiave dati flash
    * @param Mixed  $value Valore associato alla chiave
    * 
    * @return Boolean
    * 
    * @see Application_SessionManager::addFlashData
    */
   function flash_set($data,$value)
   {
      return getApplicationSessionManager()->addFlashData($data,$value);
   }
}