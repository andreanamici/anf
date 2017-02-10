<?php

namespace webProfiler\action;

class Action_logstailpanel extends Action_profilerToolbar
{     
      
   public function __construct() 
   {
      return $this->initMe("logstailpanel",self::ACTION_TYPE_ALL,false)
                  ->setTemplateEngine('templating.rain')
                  ->setTemplateFileExtension('tpl');
   }
   
   /**
    * Rimuovo la debugToolbar all'init di questo action
    */
   public function __doOnInit() 
   {
      $this->getApplicationHooks()->removeHookByName(\webProfiler\hooks\Hooks_profiler::getDefaultName());
   }

   public function doProcessMe(\Application_ActionRequestData $requestData)
   {      
      $logsTypes = array();

      if($this->getLogsManager())
      {
         $logsTypes     = $this->getLogsManager()->getAllLogsType();
      }
      
      foreach($logsTypes as $key => $type)
      {
         $lines   = 50;
         $reverse = 1;
         
         switch($type)
         {
            case 'actionresponse':     
                                       $lines   = 10000;
                                       $reverse = 0;
                                break;
         }      
         
         $logsTypes[$key] = array(
                  'type'     => $type,
                  'lines'    => $lines,
                  'reverse'  => $reverse
         );
         
      }
            
      return $this->setResponse(Array(
          'refreshTime' => 15000,
          'title'       => $this->getTitle($requestData),
          'logsTypes'   => $logsTypes
      ));      
   }
   
   /**
    * Visualizza il file di log
    * @return Array
    */
   public function doView(\Application_ActionRequestData $requestData = null)
   {
      
      $logType       = $requestData->getVal('logType',false);
      $reverse       = $requestData->getVal('reverse',false);
      $linesNumber   = $requestData->getVal('lines',100);
      $clear         = $requestData->getVal('clear',false);
      
      $logContent = 'Cannot Read file: ';
      
      try
      {
         $this->getLogsManager()->setType($logType);
         
         if($clear)
         {
            $this->getLogsManager()->clearLog();
         }
         
         $logContent = $logType ? $this->getLogsManager()->read($reverse,$linesNumber) : '';
         
      }
      catch (\Exception $e)
      {
         $logContent.= $e->getMessage();
      }
      
      return $this->setTemplateList('logstailpanel_view')
                  ->setResponse(Array(
                     'title'      => $this->getTitle($requestData),
                     'logContent' => $logContent,
             ));  
   }
   
   private function getTitle(\Application_ActionRequestData $requestData)
   {       
      return getConfigValue("PORTAL_TITLE_EXT",$requestData->getServer()->getVal('HOST_NAME')) . " | Logs Tail Panel";
   }
}