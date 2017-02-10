<?php

/**
 * Questo trait mette a disposizione il gestore dell'HTTP Request del Kernel
 */
trait Trait_ApplicationHttpRequest
{
    
    /**
     * Restituisce la request http sviluppata dal Kernel
     * 
     * @return \Application_HttpRequest
     */
    public static function getApplicationHttpRequest()
    {
        return \ApplicationKernel::getInstance()->getApplicationHttpRequest();
    }
}