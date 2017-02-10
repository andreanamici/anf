<?php

/**
 * Intefaccia ActionObject
 * 
 * Questa interfaccia stabilisce i metodi basilari che  deve avere ogni action processata dal portale
 * 
 */
Interface Interface_ActionObject extends Interface_HttpStatus
{      
    /**
     * Tipologia di action Esterne
     * @var String
     */
    const ACTION_TYPE_EXT         = 'ext';  
    
    /**
     * Tipologia di action Interne
     * @var String 
     */
    const ACTION_TYPE_INT         = 'int';
    
    /**
     * Tipologie di action Plugin
     * @var String 
     */
    const ACTION_TYPE_PLUGIN      = 'plg';     
    
    /**
     * Tipologie di action Ajax
     * @var String 
     */
    const ACTION_TYPE_AJAX        = 'ajax';  
    
    /**
     * Tipologie di action json
     * @var String 
     */
    const ACTION_TYPE_JSON        = 'json';
    
    /**
     * Tipologie di action json-parse JSON-P
     * @var String 
     */
    const ACTION_TYPE_JSONP       = 'jsonp';
    
    /**
     * Tipologia di action "JOLLY" valida per tutti i tipi di controllers
     * 
     * @var String
     */
    const ACTION_TYPE_ALL         = 'all';
    
    
    /**
     * Tipologia di action di default del portale
     * @var String
     */
    const      DEFAULT_ACTION_TYPE = 'ext';

    
    public function doProcessMe(Application_ActionRequestData $requestData);
    
    
    public function getResponse();    
}
