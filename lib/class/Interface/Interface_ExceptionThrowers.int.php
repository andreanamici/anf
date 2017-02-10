<?php
/**
 * Interfaccia per i lanciatori di eccezioni!
 */
Interface Interface_ExceptionThrowers extends Interface_HttpStatus
{    
   
    /**
     * Tipologia di eccezione delle eccezioni stesse "exception"
     * @var String
     */
    const TYPE_EXCEPTION        = 'exception';
    
    /**
     * Tipologia di eccezione degli errori php
     * 
     * @var String
     */
    const TYPE_ERROR            = 'error';
    
    /**
     * Tipologia di eccezione degli errori http
     * 
     * @var String
     */
    const TYPE_HTTP_ERROR       = 'http_error';
    
    
    /**
     * Tipologia di errori generica
     * 
     * @var String
     */
    const TYPE_UNKNOW           = 'generic';
    
    
    /**
     * Nome classe eccezione lanciata di default
     * 
     * @ver String
     */
    const DEFAULT_EXCEPTION_CLASS_NAME  = 'Exception_PortalErrorException';
    
    
    public static function throwNewException($errorCode,$errorMess);
}
