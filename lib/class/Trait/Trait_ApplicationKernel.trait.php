<?php

/**
 * Trait utile per l'accesso al kernel
 */
trait Trait_ApplicationKernel
{
    /**
     * Restiusce il Kernel in uso
     * 
     * @return Application_Kernel
     */
    protected static function getApplicationKernel()
    {
        return call_user_func(APPLICATION_KERNEL_CLASS.'::getInstance');
    }
    
    
    /**
     * Indica se il kernel attuale è in debug
     * 
     * @return Boolean
     */
    protected static function getKernelDebugActive()
    {
       return self::getApplicationKernel()->getInstance()->isDebugActive();
    }
    
    
    /**
     * Restituisce l'attuale Environment utilizzato dal kernel
     * 
     * @return String
     */
    protected static function getKernelEnvironment()
    {
       return self::getApplicationKernel()->getInstance()->getEnvironment();
    }
    
    
    /**
     * Restiusce l'autoload del Kernel
     * 
     * @return Application_Autoload
     */
    protected static function getApplicationAutoload()
    {
       return self::getApplicationKernel()->getInstance()->getApplicationAutoload();
    }
    
}

