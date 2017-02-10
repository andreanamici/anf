<?php

 /**
  * Controller ActionObject Ajax/JSON
  * 
  * @return boolean
  *
  */
class Controllers_ActionControllerAJAX extends Controllers_ActionController
{    
    /**
     * Controller ActionObject Ajax/JSON
     * 
     * @return boolean
     */
    public function  __construct()
    {
        parent:: __construct();
        return true;
    }

    public function  __destruct() 
    {
        parent::__destruct();
        unset($this);
    }
    
    /**
     * Processa l'oggetto Action instanziato
     * 
     * Processa l'oggetto Action instanziato e ne restituisce una response utile fruibile dal Kernel
     * 
     * @return Application_ControllerResponseData
     */
    public function doActionProcess()
    {  
       $headers                 = Application_ControllerResponseData::$_HEADERS_DEFAULT;
       $headers['Content-type'] = 'text/javascript';
       
       try
       {
            $ActionObject     = $this->_action_object;
            
            $actionResponse   = $this->doActionObjectProcess()->getActionResponse();
            
            if($actionResponse instanceof \Application_ControllerResponseData)
            {
                return $actionResponse;
            }
         
            $actionType       = $actionResponse->getActionType();
            $actionResponse   = $actionResponse->getResponse()->getArrayCopy();
             
            if($actionResponse!==false)
            {
               switch($actionType)
               {
                  case $ActionObject::ACTION_TYPE_ALL:                              
                  case $ActionObject::ACTION_TYPE_AJAX:
                  case $ActionObject::ACTION_TYPE_JSON:                  
                                                                     $headers['Content-type'] = 'application/json';
                                                                     $content =  json_encode($actionResponse);
                                                                     break;

                  case $ActionObject::ACTION_TYPE_ALL:
                  case $ActionObject::ACTION_TYPE_JSONP:
                                                                     $callbackJavascript      = self::getRequestParameter("callback")!==false ? self::getRequestParameter("callback") : '__callback';
                                                                     $content                 =  $callbackJavascript."(".json_encode($actionResponse).")";

                                                                     break;

                  default:                         
                                                                     return self::throwNewException(92837489161724572892974," Tipologia di Action invalida per questo Controler: ".__CLASS__.", response: ".$actionType);
                                                   break;
               }
            }
            else
            {
               return $ActionObject->throwNewException(3994992001948857379,"Errore, Response Invalida!");   
            }
       }
       catch(\Exception $e)
       {
            $headers['Content-type'] = 'application/json';
            $content                 =  $this->_manageActionObjectException($e);
       }
       
       $controllerResponse    =  $this->generateControllerResponse($content,$headers);       
       
       return $controllerResponse;
    }
    
    /**
     * Rielaboro la gestione delle eccezioni lanciate dagli actionObject, chiudendo qui il flow e non delegando al Kernel la gestione
     * 
     * @param Exception  $exception  Eccezione
     * 
     * @return Array   Response Adattata
     */
    public function _manageActionObjectException(Exception $e)
    {
         $response = array_merge($this->_action_object->getResponse(true),array(
                           "response"      => false,
                           "exception"     => Array(
                                 "message" => $e->getMessage(),
                                 "code"    => $e->getCode())
                     ));
         
         if($e instanceof Exception_RedirectException)
         {     
            $response["response"] = true;
            $response["redirect"] = $e->getUrl();

            unset($response["exception"]);
         }

         $jsonResponse = json_encode($response);
         
         return $jsonResponse;
    }
}
