<?php

/**
 * Interfaccia base di ogni controller
 */
interface Interface_Controllers extends Interface_HttpStatus,Interface_HooksType
{    
   
    /**
     * Method di default del portale, in pratica il metodo da invocare per l'action processato
     * 
     * @return String
     */
    const      DEFAULT_SUBACTION             = '';
    
    
    /**
     * Azione di default del portale
     * @var String
     */
    const      DEFAULT_ACTION                = ACTION_CNT_ACTION_DEFAULT;
    

    /**
     * Tipologia di template di default 
     * @var String
     */
    const      DEFAULT_PACKAGE         = APPLICATION_PACKAGE_DEFAULT;
    
    /**
     * Espressione regolare per matchare le string action callable
     * @var String
     */
    const      ACTION_CALLABLE_STRING_REGEXP  = '/^[A-z\_]+\:\:[A-z\_]+$/';
    
    
    /**
     * Metodo di default invocato sugli actionObject elaborati
     * @var String
     */
    const ACTION_OBJET_DEFAULT_METHOD_NAME = 'doProcessMe';
    
}
