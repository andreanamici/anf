<?php

/**
 * Trait per l'ereditarietà dei metodi dell'ApplicationCommand
 */
trait Trait_ApplicationCommands
{         
    /**
     * Restituisce il gestore dei commamnds
     * 
     * @return Application_Commands
     */
    public static function getApplicationCommands()
    {
       return \ApplicationKernel::getInstance()->getApplicationCommands();
    }
    
    
    /**
     * Esegue il command specificato, passando anche eventuali parametri e opzioni
     * 
     * @return Mixed
     */
    protected static function executeCommand($commandName,Application_ArrayBag $parameter = null,Application_ArrayBag $options = null)
    {
       return self::getApplicationCommands()->executeCommand($commandName,$parameter,$options);
    }
}


