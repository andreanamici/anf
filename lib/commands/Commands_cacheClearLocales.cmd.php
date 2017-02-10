<?php

/**
 * Questo command si occupa di pulire la cache relativa ai file di locales
 */
class Commands_cacheClearLocales extends Commands_cacheClear
{
    
   public function getName()
   {
      return 'cache:clear:locales';
   }
   
   public function doProcessMe()
   {
      
      $env = $this->getApplicationKernel()->getEnvironment();
      
      $this->getApplicationKernel()->setEnvironment($env);
      
      $this->getApplicationConfigs()->initMe();
      
      $response = $this->__cacheClear();
      
      $cacheClearResponse = 'Pulizia cache dei file di locales per environment "'.$this->getApplicationKernel()->getEnvironment().'" ';
      $cacheClearResponse.= $response["response"] ? 'success!' : 'failed, '.$response["exception"]["message"]." (Exception Code: ".$response["exception"]["code"].")";
      
      return $this->setResponse($cacheClearResponse);
   }
   
   /**
    * Pulisce la cache
    * @return boolean
    */
   private function __cacheClear()
   {
      
      $response = false;
      
      try
      {
         
         /**
          * Pulisce la cache dei file di configurazione dell'environment in uso
          */
          if($this->getApplicationLanguages()->flushCachedLanguageCatalogoues())
          {
                $response = array(
                        "response"   => true,
                        "exception"  => false
                );
          }
          else
          {
                throw new \Exception('Impossibile pulire la cache!',9328295534);
          }
       }
       catch (Exception $e)
       {
          $response = array(
                "response"   => false,
                "exception"  => array(
                    "message" => $e->getMessage(),
                    "code"    => $e->getCode()
          ));
       }
      
       return $response;
   }  
}