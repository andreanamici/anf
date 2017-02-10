<?php

/**
 * Trait per la gestione dei file di configurazione
 */
trait Trait_ApplicationConfigs
{   
   /**
    * Restituisce il gestore delle configurazioni
    * 
    * @return Application_Configs
    */
   protected static final function getApplicationConfigs()
   {
       return \ApplicationKernel::getInstance()->getApplicationConfigs();
   }
   
   /**
    * Restituisce il valore della configurazione
    * 
    * @param String $configName Nome configurazione 
    * @param Mixed  $default    [OPZIONALE] Valore di default restituito, default FALSE
    * 
    * @return Mixed
    */
   protected static final function getConfigValue($configName,$default = false)
   {
       return \ApplicationKernel::getInstance()->getApplicationConfigs()->getConfigsValue($configName, $default);
   }
}