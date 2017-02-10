<?php


/**
 * Interfaccia per il gestore dei servizi dell'applicazione
 */
interface Interface_ApplicationServices
{
    const SERVICE_STRING_PATTERN = '/^@([A-z\-\_]+)([\.A-z0-9]+){0,}/';
    
    /**
     * Indica se di default i servizi sono univoci, quindi una volta registrati non sono sovrascrivibili
     * @var Boolean
     */
    const DEFAULT_SERVICE_UNIQUE     = true;
        
    /**
     * Indica se di default i servizi sono lazy, attivati solamente se ricercati
     * @var Boolean
     */
    const DEFAULT_SERVICE_LAZY       = true;
    
    /**
     * Nome del file di configurazione dei servizi
     * @var String
     */
    const CONFIGS_SERVICES_FILE_NAME = 'application-services';
        
    /**
     * Nome del metodo da invocare sugli oggetti per ottenere il relativo singleton
     * @var String
     */
    const SINGLETON_METHOD_NAME      = 'getInstance';
}