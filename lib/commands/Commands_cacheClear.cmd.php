<?php

/**
 * Questo command si occupa di pulire la cache dell'applicazione
 */
class Commands_cacheClear extends Abstract_Commands
{
   
   public function getName()
   {
      return 'cache:clear';
   }
   
   public function doProcessMe()
   {
      
      $options  = $this->getOptions();
      $response = $this->__cacheClear();
      
      $cacheClearResponse = 'Pulizia cache per environment "'.$this->getApplicationKernel()->getEnvironment().'"';
      $cacheClearResponse.= $response['response'] ? 'success! ' : 'failed';
      $cacheClearResponse.= $response['message'];
      
      return $this->setResponse($cacheClearResponse);
   }
   
   /**
    * Pulisce la cache
    * @return boolean
    */
   private function __cacheClear()
   {
      $response = array('response' => false,'message' => null);
      
      try
      {
         /**
          * Clear cache Data
          */
         $cacheManager = $this->getApplicationKernel()->get('@cache');
         
         try
         {
            if($cacheManager->isActive())
            {
                   $cacheEngineFile = $cacheManager->changeCacheEngine('File');

                   if($cacheEngineFile->check())
                   {
                       $cacheEngineFile->deleteByPrefix(CACHE_KEYPREFIX);
                   }

                   $cacheEngineMemcached = $cacheManager->changeCacheEngine('Memcached');

                   if($cacheEngineMemcached->check())
                   {
                       $cacheEngineMemcached->deleteByPrefix(CACHE_KEYPREFIX);
                   }

                   $cacheEngineApc = $cacheManager->changeCacheEngine('Apc');

                   if($cacheEngineApc->check())
                   {
                       $cacheEngineApc->deleteByPrefix(CACHE_KEYPREFIX);
                   }
            }
         }
         catch(\Exception $e){
             //non fare nulla
         }
         
         /**
          * Templates Engine
          */
         $cacheClear = $this->getApplicationKernel()->get('@templating')->flushCache();

         /**
          * Pulisce la cache dei file di configurazione dell'environment in uso
          */
         $cacheClear = $this->getApplicationKernel()->get('@config')->flushCache();
                  
         $response['response'] = true;
         
         /**
          * Propago questo hook in tutta l'applicazione cosi che si possano agganciare future funzionalità in altri package
          */
         $this->getApplicationKernel()->get('@hooks')->processAll(Application_Hooks::HOOK_TYPE_CACHE_CLEAR,true)->getResponseData()->getData();                  
       }
       catch (\Exception $e)
       {
          $response = array(
              "response"   => false,
              "message"    => "error: {$e->getMessage()} code: {$e->getCode()}"
          );
       }
      
       return $response;
   }  
}