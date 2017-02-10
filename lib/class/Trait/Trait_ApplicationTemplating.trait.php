<?php

/**
 * Trait per la gestione del service di templating
 */
trait Trait_ApplicationTemplating
{
    /**
     * Restituisce il gestore del templating dell'applicazione
     * 
     * @return \Application_Templating
     */
    protected static function getApplicationTemplating()
    {
        return \ApplicationKernel::getInstance()->get('templating');
    }
}