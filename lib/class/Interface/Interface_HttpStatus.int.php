<?php


/**
 * Intefaccia che fornisce le costanti di errore HTTP
 */
interface Interface_HttpStatus
{
    
    /**
     * Http status success
     * @var Int
     */
    const      HTTP_STATUS_OK                   = 200;
    
    /**
     * HTTP errore pagina non trovata
     * @var Int
     */
    const      HTTP_ERROR_PAGE_NOT_FOUND        = HTTP_STATUS_PAGE_NOT_FOUND;
    
    /**
     * HTTP errore Interno del server
     * @var Int
     */
    const      HTTP_ERROR_INTERNAL_SERVER_ERROR = HTTP_STATUS_INTERNAL_SERVER_ERROR;
    
    /**
     * HTTP errore Richiesta inviata al server non valida, non è stato rispettato correttamente il protocollo http
     * @var Int
     */
    const      HTTP_ERROR_BAD_REQUEST           = HTTP_STATUS_BAD_REQUEST;
    
    
    /**
     * HTTP Redirect status code
     * @var Int
     */
    const      HTTP_ERROR_REDIRECT               = HTTP_STATUS_REDIRECT;
    
    
    /**
     * HTTP Forbidden status code
     * @var Int
     */
    const      HTTP_ERROR_FORBIDDEN              = HTTP_STATUS_FORBIDDEN;
    
    
    /**
     * HTTP status serivizio non disponibile
     * @var Int
     */
    const      HTTP_ERROR_SERVICE_UNAVAILABLE    = HTTP_STATUS_SERVICE_UNAVAILABLE;
    
}
