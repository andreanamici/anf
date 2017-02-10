<?php

/**
 * Classe che gestisce i dati delle Rotte processate
 * 
 * Questa classe estende gli ArrayObject, con la possibilità di accedere alle proprietà come se fosse un array associativo
 * 
 */
class Application_RoutingData extends ArrayObject
{  
   
   use Trait_Singleton;
   
   /**
    * Classe che gestisce i dati delle Rotte processate
    * @param Array $array
    */
   public function __construct($array = Array()) 
   {      
      $this->initMe($array);
   }
   
   /**
    * Inizializza l'oggetto
    * 
    * @param Array $array Array dei dati da inizializzare
    * 
    * @return \Application_RoutingData
    */
   public function initMe($array)
   {
      if(isset($array["action"])){
         $this->setAction($array["action"]);
      }
      
      if(isset($array["method"])){
         $this->setMethod($array["method"]);
      }else{
         $this->setMethod(null);
      }
      
      if(isset($array["controllertype"])){
         $this->setControllerType($array["controllertype"]);
      }
      
      if(isset($array["params"])){
         $this->setParams($array["params"]);
      }else{
         $this->setParams(array()); 
      }
      
      if(isset($array["defaults"])){
         $this->setDefaults($array["defaults"]);
      }else{
         $this->setDefaults(array()); 
      }
      
      return $this;
   }
   
   
   /**
    * Imposta il nome della rotta
    * @param String $name Nome rotta
    * @return \Application_RoutingData
    */
   public function setRouteName($name)
   {
      $this->offsetSet('name',$name);
      return $this;
   }
   
   /**
    * Imposta il nome dell'ActionObject da invocare, es: 'login', puà essere anche una function da invocare, verrà processata  dall'ActionController
    * 
    * @param Mixed $action Nome action / Callable function
    * 
    * @return \Application_RoutingData
    */
   public function setAction($action)
   {
      $this->offsetSet('action',$action);
      return $this;
   }
   
   /**
    * Imposta la method processata per la rotta
    * @param String $method Nome method
    * @return \Application_RoutingData
    */
   public function setMethod($method)
   {
      $this->offsetSet('method',$method);
      return $this;
   }
   
   
   /**
    * Imposta il package elaborato dalla rotta
    * @param String $package
    * @return \Application_RoutingData
    */
   public function setPackage($package)
   {                 
      $this->offsetSet('package',$package);
      return $this;
   }
   
   /**
    * Imposta il controller da utilizzare, es: html,ajax etc..
    * @param String $controller
    * @return \Application_RoutingData
    */
   public function setControllerType($controller)
   {                 
      $this->offsetSet('controllerType',$controller);
      return $this;
   }
   
   
   /**
    * Imposta il valore params
    * 
    * @param Mixed ArrayObject|Array $params parametri per questa azione
    * 
    * @return ArrayObject
    */
   public function setParams($params)
   {
      if(!($params instanceof \ArrayObject)){
         $params = new parent($params,ArrayObject::ARRAY_AS_PROPS);
      }

      $this->offsetSet('params', $params);
      return $this;
   }
   
   /**
    * Imposta i parametri di defaults delle rotte elaborate
    * 
    * @param array $defaults Parametri di default
    * 
    * @return \Application_RoutingData
    */
   public function setDefaults($defaults)
   {
      if(!($defaults instanceof \ArrayObject)){
         $defaults = new parent($defaults,ArrayObject::ARRAY_AS_PROPS);
      }

      $this->offsetSet('defaults', $defaults);
      return $this;
   }
   
   /**
    * Restiusce il nome della rotta
    * @return String
    */
   public function getRouteName($default = false)
   {
      return $this->offsetExists("name") ? $this->offsetGet("name") : $default;
   }
   
   /**
    * Restiusce il valore action
    * @return String
    */
   public function getAction($default = false)
   {
      return $this->offsetExists("action") ? $this->offsetGet("action") : $default;
   }
   
   /**
    * Restiusce il valore controller
    * @return String
    */
   public function getControllerType($default = false)
   {
      return $this->offsetExists("controllerType") ? $this->offsetGet("controllerType") : $default;
   }
   
   /**
    * Restiusce il valore method
    * @return String
    */
   public function getMethod($default = false)
   {
      return $this->offsetExists("method") ? $this->offsetGet("method") : $default;
   }
   
   /**
    * Restiusce il valore package
    * @return String
    */
   public function getPackage($default = false)
   {
      return $this->offsetExists("package") ? $this->offsetGet("package") : $default;
   }
   
   
   /**
    * Restiusce il valore params
    * @return ArrayObject
    */
   public function getParams()
   {
      return $this->offsetExists("params")    ? $this->offsetGet("params") : new ArrayObject(Array());
   }
   
   /**
    * Restiusce il valore params di default della rotta
    * @return ArrayObject
    */
   public function getDefaults()
   {
       return $this->offsetExists('defaults') ? $this->offsetGet("defaults") : new ArrayObject(Array());
   }
   
   /**
    * Restituisce la callable per la callable utile all'Application Controller
    * 
    * @return array|string
    */
   public function getActionCallable()
   {
       if($this->getMethod())
       {
           return array($this->getAction(),$this->getMethod());
       }
       
       return $this->getAction();
   }
   
   public function __toString()
   {
      return $this->getRouteName()." , action: ".$this->getAction().", method: ".$this->getMethod('N/D').", routeParams: ".print_r($this->getParams(),true);
   }
   
}
