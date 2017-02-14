<?php

require_once 'application.php';

if(!function_exists('flash'))
{
   /**
    * Restituisce il dato flash indicato presenti in sessione
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


if(!function_exists('flash_has'))
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

if(!function_exists('flash_has_message'))
{
    /**
     * Indica se è presente almeno uno tra i flash message disponibili, o di un determinato tipo indicato
     * 
     * @param String $type  tipologia di messaggio, default uno tra quelli disponibili dal sessionManager
     * 
     * @return Mixed
     */
    function flash_has_message($type = null, $default = false)
    {
        return getApplicationSessionManager()->hasFlashMessage($type,$default);
    } 
}

if(!function_exists('flash_get_message'))
{
    /**
     * Restituisce la tipologia di messaggio indicata
     * 
     * @param String $type  tipologia di messaggio
     * 
     * @return array  lista dei messaggi, dove la key è il nome, il value è il messaggio
     */
    function flash_get_message($type = null, $default = false)
    {
        return getApplicationSessionManager()->getFlashMessage($type,$default);
    } 
}

if(!function_exists('flash_get_all_messages'))
{
    /**
     * Restituisce tutti i messaggi flash presenti
     * 
     * @return array  lista dei messaggi, dove la key è il nome, il value è il messaggio
     */
    function flash_get_all_messages()
    {
        $allFlashMessagesTypes = getApplicationSessionManager()->getAllFlashMessagesTypes();
        $flashMessages         = array();
        
        if($allFlashMessagesTypes)
        {
            foreach($allFlashMessagesTypes as $type)
            {
                if(flash_has_message($type))
                {
                    $flashMessages[$type] = flash_get_message($type);
                }
            }
        }
        
        return $flashMessages;
    }
}