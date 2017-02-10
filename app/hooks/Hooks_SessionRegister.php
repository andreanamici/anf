<?php

/**
 * Hook per l'avvio della sessione al KernelLoad
 */
class Hooks_SessionRegister extends \Abstract_Hooks
{
    public function __construct()
    {   
        $this->initMe(self::HOOK_TYPE_KERNEL_LOAD);
    }
    
    public function doProcessMe(\Application_HooksData $hookData)
    {        
        $hookData->getKernel()->get('session')->sessionStart();   
    }
}