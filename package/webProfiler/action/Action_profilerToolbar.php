<?php

namespace webProfiler\action;

class Action_profilerToolbar extends \Abstract_ActionObject 
{  
   const SESSION_STATUS_KEY   = 'profiler.status';
   
   const SESSION_STATUS_SHOW  = 'show';
   
   const SESSION_STATUS_HIDE  = 'hidden';   
   
   
   public function __doOnInit()
   {
       $this->initMe("profiler_toolbar",self::ACTION_TYPE_ALL,false)
            ->setTemplateEngine('templating.rain')
            ->setTemplateFileExtension('tpl');
   }

   
   public function doProcessMe(\Application_ActionRequestData $requestData)
   {      
      $currentRoute = $this->getApplicationRoutingCurrentRouteData()->getRouteName();
      $routeParams  = $this->getApplicationRoutingCurrentRouteData()->getParams()->getArrayCopy();
      
      $prodUrl      = preg_replace('/app(.*?)\.php/','app.php',$this->generateUrl($currentRoute,$routeParams));
      
      return $this->setResponse(Array('profiler' => Array(
                  'status'            => $this->getSessionManager()->getIndex(self::SESSION_STATUS_KEY),
                  'logstailpanelurl'  => $this->generateUrl('_profiler_logstailpanel',array(),true),
                  'phpinfourl'        => $this->generateUrl('_profiler_phpinfo',array(),true),
                  'query_number'      => $this->getDatabaseManager()->getQueryExecutedNumber(),
                  'cachekeys_fetched' => $this->getCacheManager()->getKeysFetchedNumber(),
                  'cachekeys_stored'  => $this->getCacheManager()->getKeysStoredNumber(),
                  'locale'            => $this->getApplicationLanguages()->getPortalLocale(),
                  'lang'              => $this->getApplicationLanguages()->getPortalLanguage(),
                  'locale_fallback'   => $this->getApplicationLanguages()->getFallbackLocale(),
                  'lang_fallback'     => $this->getApplicationLanguages()->getFallbackLanguage(),
                  'processtime'       => abs((microtime() - $this->getApplicationKernel()->getKernelStartTime())),
                  'prodUrl'           => $prodUrl,
                  'path_hide'         => $this->generateUrl('_profiler_hide'),
                  'path_show'         => $this->generateUrl('_profiler_show'),
                  'commands'          => $this->getApplicationKernel()->getApplicationCommands()->getAllCommands(),
                  'route_name'        => $this->getApplicationRouting()->getApplicationRoutingData()->getRouteName(),
      )));         
   }
   
   /**
    * Imposta la profiler su show per la sessione attuale
    * 
    * @return Array
    */
   public function doSetVisible(\Application_ActionRequestData $requestData = null)
   {
      $session = $this->getSessionManager();
      
      $session->addIndex(self::SESSION_STATUS_KEY,self::SESSION_STATUS_SHOW);
      
      return $this->setResponse(Array(
                     'response' => $session->exists(self::SESSION_STATUS_KEY)
             ),false);  
   }
   
  
   
   public function doCommand(\Application_ActionRequestData $requestData = null)
   {
      $post    = $requestData->getPost();
      
      $command = $post["command"];
      
      $commandData = $this->getApplicationKernel()->getApplicationCommands()->getParseCommand($command);

      $response   = $this->getApplicationKernel()->getApplicationCommands()->executeCommand($commandData->getCommandName(),$commandData->getParams(),$commandData->getOptions());
      
      return $this->setResponse($response,false);
   }
   
   
   /**
    * Imposta la profiler su hide per la sessione attuale
    * 
    * @return Array
    */
   public function doSetHide(\Application_ActionRequestData $requestData = null)
   {
      $session = $this->getSessionManager();
      
      $session->addIndex(self::SESSION_STATUS_KEY,self::SESSION_STATUS_HIDE);
            
      return $this->setResponse(Array(
                  'response' => $session->exists(self::SESSION_STATUS_KEY)
             ),false);  
   }   

   /**
    * Mostra la versione di php e chiude il kernel
    * 
    * @return void
    */
   public function doPhpinfo()
   {
      phpinfo();
      return $this->getApplicationKernel()->closeKernel();
   }
   
      
   public function isLoggable()
   {
      return false;
   }
   
   
}