<?php

/**
 * Interfaccia per gli hooks
 */
interface Interface_Hooks extends Interface_HooksType,Interface_HttpStatus
{   
    /**
     * Processa questo hook
     * 
     * @return Application_HooksData
     */
    public function doProcessMe(Application_HooksData $hookData);
    
    /**
     * Verifica che l'hook sia eseguibile
     * 
     * @param Application_HookData $hookData Dati per questo hook
     * 
     * @return Boolean
     */
    public function isEnable(Application_HooksData $hookData);

    
    /**
     * Indica se l'hook è registrabile dall'HookManager
     * 
     * @return Boolean
     */
    public function isRegistrable();
    
    
    /**
     * Restituisce la configurazione di registrazione dell'hooks
     * 
     * @return Array
     */
    public static function getSubscriberConfiguration();
}

