<?php

/**
 * Questo hooks si occupa di caricare le configurazioni per l'applicazione di:
 * 
 *  - routing
 *  - hooks
 *  - services
 *  - configs
 * 
 */
class Hooks_ApplicationConfigs extends Abstract_Hooks
{
    protected $kernel;
    
    public function __construct()
    {
       $this->initMe(self::HOOK_TYPE_KERNEL_LOAD);
    }
    
    public function doProcessMe(\Application_HooksData $hookData)
    {
        $this->kernel = $hookData->getKernel();
        
        $this->loadConfigs(\Application_Configs::CONFIGS_FILE_EXTENSION_PHP)
             ->loadConfigs(\Application_Configs::CONFIGS_FILE_EXTENSION_YAML);
//             ->loadConfigs('xml');        
    }
    
    /**
     * Carica le configurazioni per una particolare estenzione
     * 
     * @param string $extension estenzione
     * 
     * @return \Hooks_ApplicationConfigs
     */
    protected function loadConfigs($extension)
    {
        $kernel = $this->kernel;
        
        $appConfigs = $kernel->get('@config')->initMe(APPLICATION_APP_PATH . '/configs', $extension,false); /*@var $appConfigs \Application_Configs*/
        
        $appConfigs->loadConfigsFile('application-configs');
        
        $routes = $appConfigs->loadConfiguration('application-routing',true);        

        if($routes)
        {
            $kernel->get('@routing')->addRoutingMaps($routes);
        }
        
        $hooks = $appConfigs->loadConfiguration('application-hooks',true);        

        if($hooks)
        {
            $kernel->get('@hooks')->registerHooksByConfigsData($hooks);
        }
        
        $services = $appConfigs->loadConfiguration('application-services',true);     
        
        if($services)
        {
            $kernel->get('@services')->registerServices($services);
        }       
        
        return $this;
    }
}