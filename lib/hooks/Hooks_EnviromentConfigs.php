<?php

/**
 * Questo hooks trasforma tutte le variabili di Enviroments in configurazioni disponibile configs 
 * 
 */
class Hooks_EnviromentConfigs extends Abstract_Hooks
{
    
    public function __construct() 
    {
        $this->initMe(self::HOOK_TYPE_KERNEL_LOAD);    
    }
    
    
    public function doProcessMe(\Application_HooksData $hookData) 
    {
        $allEnvironments = $hookData->getHttpRequest()->getEnv()->getAll();
        
        if($allEnvironments && count($allEnvironments) > 0)
        {
            foreach($allEnvironments as $name => $value)
            {
                $hookData->getKernel()->getApplicationConfigs()->addConfig("ENV_".strtoupper($name), $value);
            }
        }
    }
}