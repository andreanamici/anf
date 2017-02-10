<?php

/**
 * Classe che gestisce i dati elaborati dall'actionObject quando viene processato correttamente
 * 
 * Questa classe estende gli ArrayObject, con la possibilità di accedere alle proprietà come se fosse un array associativo
 * 
 */
class Application_ActionResponse extends ArrayObject 
{

   /**
    * Response da gestire dal kernel
    * 
    * @var ArrayObject
    */
   protected $_response = null;
   
   
   /**
    * Tipologia di action processata
    * 
    * @var String
    */
   protected $_actionType = null;
   
   
   /**
    * Classe che gestisce i dati passati dall'actionObject quando viene processato correttamente
    * 
    * @param Array $array Array degli attributi
    * 
    * @return Boolean
    */
   public function __construct($actionType,array $response) 
   {      
      $this->setActionType($actionType)
           ->setResponse($response);
      
      return true;
   }

   /**
    * Imposta la response attualmente elaborata dall'ActionObject
    * 
    * @param Array $response Array response dell'actionObject
    * 
    * @return Application_ActionResponse
    */
   public function setResponse(array $response) 
   {
      $this->_response = new ArrayObject($response);
      return $this;
   }
   
   
   /**
    * Restituisce la response attualmente elaborata dall'ActionObject
    * 
    * @return ArrayObject
    */
   public function getResponse() 
   {
      return $this->_response;
   }
   
   
   /**
    * Imposta l'actionType processata
    * 
    * @param String $actionType ActionType es: 'ext', 'ajax', 'html', 'all'
    * 
    * @return Application_ActionResponse
    */
   public function setActionType($actionType) 
   {
      $this->_actionType = $actionType;
      return $this;
   }
   
   
   /**
    * Restituisce l'actionType processata
    * 
    * @return Mixed
    */
   public function getActionType() 
   {
      return $this->_actionType;
   }
   
   
   /**
    * Restituisce il formato string di questo oggetto
    * @return String
    */
   public function __toString(){
      return print_r($this->getResponse()->getArrayCopy(),true);
   }
   
}
