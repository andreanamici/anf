<?php

/**
 * Trait per la gestione dei services
 */
trait Trait_ApplicationServices
{
    
    /**
     * Restituisce il gestore dei servizi registrati al Kernel
     * 
     * @return Application_Services
     */
    protected static function getApplicationServices()
    {
        return \ApplicationKernel::getInstance()->getApplicationServices();
    }
    
    
    /**
     * Restituisce il servizio tramite il suo nome di registrazione
     * 
     * @param String $serviceName Service
     * 
     * @return Mixed
     * 
     * @throws Exception_PortalErrorException
     */
    protected static function getService($serviceName,array $parameters = array())
    {
        return self::getApplicationServices()->getService($serviceName,$parameters);
    }
    
    /**
     * Verifica che un servizio sia esistente tra quelli registrati
     * 
     * @param String $serviceName Nome del service
     * 
     * @return Boolean
     */
    protected static function hasService($serviceName)
    {
        return self::getApplicationServices()->hasService($serviceName);
    }
}