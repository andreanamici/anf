<?php

/**
 * Classe astratta magic methods invocati sugli ActionObject / Controllers
 */
abstract class Abstract_ActionMagicMethods
{
   /**
    * Questo metodo viene invocato quanto l'actionObject è stato inizializzato dall'ActionController, prima di invocare eventuali hooks di preaction
    * 
    * @return boolean
    */
   public function __doOnInit()
   {
      return true;
   }
   
       
   /**
    * Manipola la request che questo action object riverà per ogni metodo, utile per inserire / modificare / eliminare eventuali elementi all'interno dell'oggetto Application_ActionRequestData
    * 
    * @param \Application_ActionRequestData $actionRequestData
    * 
    * @return \Application_ActionRequestData
    */
   public function __doManipulateActionRequestData(\Application_ActionRequestData $actionRequestData)
   {
      return $actionRequestData;
   }
   
   /**
    * Questo metodo è richiamto automaticamente dal controller prima di elaborare ogni richiesta di questo ActionObject
    * 
    * @param \Application_ActionRequestData $actionRequestData
    * 
    * @return boolean
    */
   public  function __doOnPreProcess(\Application_ActionRequestData $actionRequestData)
   {
       return true;
   }

    /**
     * Questo metodo è richiamto automaticamente dal controller dopo l'elaborazione di ogni response fornita da questo ActionObject
     * 
     * Gestisce response in formato array
     * 
     * <b>Questo metodo deve restituire una response array</b>
     * 
     * @param Array $responseAdapted   Response Adattata
     * 
     * @return Arrray $responseAdapted
     */
   public  function __doOnPostProcess(array $responseAdapted)
   {
       return $responseAdapted;
   }
   
    /**
     * Reppresenta questo actionObject in formato string
     * @return String
     */
    public function __toString() 
    {
       return get_called_class();
    }
}