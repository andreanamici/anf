<?php


/**
 * Trait utile per gestire gli hooks
 */
trait Trait_ApplicationHooks
{
   /**
    * Restituisce l'Application_Hooks gestore degli hooks
    * 
    * @return Application_Hooks
    */
   protected static function getApplicationHooks()
   {
      return  \AppKernel::getApplicationHooks();
   }
   
   
   /**
    * Processa la tipologia di hook specificata
    * 
    * @param String   $hookType      Tipologia di hook
    * @param Mixed    $params        Mixed data opzionale da passare all'hookManager
    * 
    * @return Application_HooksResponseData
    */
   protected function processHooks($hookType,$hookParams = null)
   {
      return \AppKernel::processHooks($hookType, $hookParams);
   }
   
   
   /* Hooks metodi  **********************************************************************

    /**
     * Indica se gli hooks sono abilitati
     * 
     * @see Application_Hooks
     * 
     * @return Boolean
     */
    protected function getHooksEnable()
    {
       return self::getApplicationHooks()->isEnable();
    }
    
    /**
     * Abilita / Disabilita gli hooks
     * 
     * @param Boolean $status status di abilitazione
     * 
     * @see Application_Hooks
     * 
     * @return Trait_ApplicationHooks
     */
    protected function setHooksEnable($status)
    {
       self::getApplicationHooks()->setEnable($status);
       return $this;
    }

    
    /**
     * [ALIAS]
     * 
     * Disabilita un particolare tipo di hook
     * 
     * @param String $hookType Tipologia di hook
     * 
     * @see Application_Hooks
     * 
     * @return Trait_ApplicationHooks
     */
    protected function setHookTypeDisabled($hookType)
    {
       self::getApplicationHooks()->setHookTypeDisabled($hookType);
       return $this;
    }
    
    /**
     * [ALIAS]
     * 
     * Abilita un particolare tipo di hook
     * 
     * @param String $hookType Tipologia di hook
     * 
     * @see Application_Hooks
     * 
     * @return Trait_ApplicationHooks
     */
    protected function setHookTypeEnabled($hookType)
    {
       self::getApplicationHooks()->setHookTypeEnabled($hookType);
       return $this;
    }
    
    
    /**
     * [ALIAS]
     * 
     * Indica se la tipologia di hook è attiva
     * 
     * @param String $hookType Tipologia di hook
     * 
     * @see Application_Hooks
     * 
     * @return Trait_ApplicationHooks
     */
    protected function isHookTypeDisabled($hookType)
    {
       return self::getApplicationHooks()->isHookTypeDisabled($hookType);
    }
    
    
   /**
    * Registra un hook od una callback per essere eseguiti appena l'evento indicato dal Kernel corrisponde
    * 
    * @param  Mixed    $hook           Callback,  Hook o String (nome della classe hook) da agganciare
    * @param  Mixed    $hookType       Tipologia  hook o array contenente le tipologie con associati i metodi da richiamare (opzionali, richiamto di default il metodo dell'hook principale)
    * @param  Mixed    $hookPriority   [OPZIONALE] Priorità o array con le priorità per ogni tipologia di hook, default = 0 appende agli hook già registrati
    * 
    * @return Boolean
    */
    protected function registerHook($hook,$hookType, $hookPriority = 0)
    {
       return self::getApplicationHooks()->registerHook($hook,$hookType,$hookPriority); 
    }
}
