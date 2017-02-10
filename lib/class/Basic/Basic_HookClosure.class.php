<?php

/**
 * Hook generico usato per gestire le function anonime registrate in runtime.
 * 
 * Ogni function sarà poi manipolata da questo oggetto che a sua volta sarà gestito dall'Application_Hooks
 * 
 */
class Basic_HookClosure extends Abstract_Hooks
{
   
   /**
    * Function anonima
    * @var Closure
    */
   private $_closure;
   
   /**
    * Imposta la closure invocata al processamento dell'hook
    * 
    * @param Closure $closure funtion anonima
    * 
    * @return \Basic_HookClosure
    */
   public function setHookClosure(Closure $closure)
   {
      $this->_closure = $closure;
      $this->_closure = $this->_closure->bindTo($this,$this);
      
      return $this;
   } 
  
   /**
    * Ogni Hook Closure invocherà sempre questo metodo
    * 
    * @param \Application_HooksData $hookData
    * 
    * @return Application_HooksData
    */
   public function doProcessMe(\Application_HooksData $hookData) 
   { 
      $closure = $this->_closure; 
      return $closure($hookData,self::HOOK_DEFAULT_METHOD,$this);
   }
   
   /**
    * Questo metodo permette all'hook generico, inizializzato tramite function anonima Closure,
    * di poter anche avere a disposizione piu registrazioni simultanee a diversi hookType, 
    * sfruttando il secondo parametro che indica quale metodo  si vuole invocare
    * 
    * @param String $methodName  Nome del metodo
    * @param Array  $arguments   Argomenti
    * 
    * @return Mixed
    */
   public function __call($methodName, $arguments) 
   {
      $closure  = $this->_closure;

      $hookData = !empty($arguments) && $arguments[0] ? $arguments[0] : false;
      
      if(!($hookData instanceof Application_HooksData))
      {          
         return self::throwNewException(980237489273498234, 'L\'argomento 1 passato all\'hook '.$this->getHookName().' per il metodo '.$methodName.'() deve essere un instanza di Application_HooksData ');
      }
      
      if(strlen($methodName) == 0)
      {
         return self::throwNewException(92928384666261637, 'L\'argomento 2 passato all\'hook '.$this->getHookName().' per il metodo '.$methodName.'() deve essere il nome del metodo invocato ');
      }
      
      return $closure($hookData,$methodName,$this);
   }
}