<?php

/**
 * Forza gli headers di connessione per evitare caching 
 */
class Hooks_headers extends Abstract_Hooks
{  
   public function __construct() 
   {
      $this->initMe(self::HOOK_TYPE_PRE_RESPONSE,self::getDefaultName());
   }
   
   public function doProcessMe(\Application_HooksData $hookData) 
   {     
      $data    = $hookData->getData();/*@var $data Application_ControllerResponseData*/
      $charset = $hookData->getKernel()->get('templating')->getCharset();
      
      $data->replaceHeader('Cache-Control','no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
      $hookData->setData($data); 
   }
}