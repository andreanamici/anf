<?php

/**
 * Questo hooks controlla l'esistenza delle directory di assets in nella document root
 */
class Hooks_Assets extends Abstract_Hooks
{
    
    public function __construct() 
    {
        $this->initMe(self::HOOK_TYPE_KERNEL_LOAD);    
    }
    
    
    public function doProcessMe(\Application_HooksData $hookData) 
    {
        
    }
}