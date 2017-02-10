<?php

namespace webProfiler\hooks;

use webProfiler\webProfiler;
use webProfiler\action\Action_profilerToolbar;
use webProfiler\action\Action_logstailpanel;

/**
 * Questo hook aggancia una toolbar di analisi in tutte le viste del progetto se questo è in debug
 */
class Hooks_profiler extends \Abstract_Hooks
{
    
   /**
    * Usage memory iniziale in bytes
    * 
    * @var Int
    */
   private $memoryUsageStart = 0;
   
   /**
    * ActionObject Invocati
    * @var Array
    */
   private $actionObjects;
   
   public static function getSubscriberConfiguration()
   {
       return array(
           
           /**
            * Eseguo con priorità massima
            */
           self::HOOK_TYPE_KERNEL_LOAD      => array(
                                                    array('onKernelLoad'  => self::HOOK_PRIORITY_MAX)
                                               ),
           /**
            * Eseguo con priorità massima
            */
           self::HOOK_TYPE_PRE_ACTION       => array(
                                                    array('onPreAction' => self::HOOK_PRIORITY_MAX)
                                               ),
           /**
            * Aggiungo dei dati al postAction
            */
           self::HOOK_TYPE_POST_ACTION      => array(
                                                    array('onPostAction')
                                               ),
           /**
            * Inietto la toolbar prima della response
            */
           self::HOOK_TYPE_PRE_RESPONSE     => array(
                                                    array('onPreResponse')
                                               )
       );
   } 
    
   
   /**
    * Catturo l'uso della memoria in ingresso
    * 
    * @param \Application_HooksData $hookData
    */
   public function onKernelLoad(\Application_HooksData $hookData)
   {
      $this->memoryUsageStart = memory_get_usage();
   }    
   
   /**
    * Se l'actionObject da processare è quello di debug blocco tutto il flow di preAction, evitando che possa essere cambia l'action elaborata
    * 
    * @param Application_HooksData $hookData
    */
   public function onPreAction(\Application_HooksData $hookData)
   {
       $appRoutingData = $hookData->getApplicationRouteData();
       $actionObject   = $hookData->getActionObject();

       if($actionObject instanceof Action_profilerToolbar)
       {
          $hookData->setPropagationStop(true);
          return $hookData;
       }

       $this->actionObjects[$hookData->getController()->isMainAction() ? 'mainactions' : 'subactions'][] = array(
            'actionobject' =>  $actionObject,
            'action'       =>  $actionObject->getAction(),
            'method'       =>  $actionObject->getMethodName()
       );
   }
   
   /**
    * Imposta l'utilizzo della memoria all'actionObject
    * 
    * @param \Application_HooksData $hookData
    */
   public function onPostAction(\Application_HooksData $hookData)
   {
       $actionObject = $hookData->getActionObject();
       
       $package = $actionObject->getPackageInstance();
       
       if(($package instanceof webProfiler) && ($actionObject instanceof Action_profilerToolbar))
       {
           $actionResponse = $hookData->getData()->getResponse()->getArrayCopy();
                      
           $actionResponse["profiler"]["memory_usage"] = formatBytes(memory_get_usage() - $this->memoryUsageStart,2);
           $actionResponse["profiler"]["action_objects"] = array();
           
           if($this->actionObjects)
           {
                $actionResponse["profiler"]["action_objects"] = $this->actionObjects;
           }
                      
           $hookData->setData($hookData->getData()->setResponse($actionResponse));
           $actionObject->setResponse($actionResponse);
       }
   }
   
   /**
    * Elaboro e processo l'ActionObject "Action_profilerToolbar" iniettando la response HTML in quella già elaborata
    * 
    * @param Application_HooksData $hookData
    */
   public function onPreResponse(\Application_HooksData $hookData) 
   {
        $actionController   = $hookData->getController();           /*@var $actionController Controllers_ActionController*/
        $controllerResponse = $hookData->getData();                 /*@var $actionController Application_ControllerResponseData*/
        $currentContent     = $controllerResponse->getContent();
        
        $actionRequestData  = $actionController->getActionRequestData();
                
        if(!$actionRequestData->isXmlHttpRequest() && $controllerResponse->headerMatch('Content-type','/text\/html/'))
        {  
           $hookData->getKernel()->get('templating')->clearTemplateList();
           
           $profilerControllerResponse = $actionController->forwardActionControllerResponse('webProfiler\action\Action_profilerToolbar');
            
           if($profilerControllerResponse)
           {   
              $currentHeaders           = $controllerResponse->getHeaders();
              
              $profilerToolbarContent   = $profilerControllerResponse->getContent();
              
              $currentContent = str_replace(array("</body>","</html>"),"",$currentContent);
              $newContent     = $currentContent.$profilerToolbarContent."</body></html>";

              $controllerResponse->setContent($newContent)
                                 ->setHeaders($currentHeaders->getArrayCopy());

              /**
               * Innietto l'HTML della response
               */
              $hookData->setData($controllerResponse);
           }
        }
   }
}