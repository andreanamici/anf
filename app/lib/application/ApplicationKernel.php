<?php

/**
 * Questo è il kernel dell'applicazione, estende quello base e permette di implementare eventuali logiche personalizzate
 */
class ApplicationKernel extends \Application_Kernel
{    
    
    public function __construct()
    {
        parent::__construct();
        $this->autoloadRegister(__DIR__,'');
    }
    
    
    protected function getKernelServices()
    {
        return array_merge(parent::getKernelServices(),array(

                     'routing'  => array(
                                        'class' => 'ApplicationRouting'
                                   ),
            
                     'database' => array(
                                        'class' => 'ApplicationDatabase'
                                   )
        ));
    }
    
    public function _onServiceRegistered(\Application_ServicesInstance $serviceInstance)
    {
        parent::_onServiceRegistered($serviceInstance);
        
        switch($serviceInstance->getName())
        {
            case 'routing':
                
                //Registrare eventualmente qui le rotte dell'applicazione

            break;
        
            case 'hooks':
                
                //Registrare eventuali hooks personalizzati oppure creare i file nella cartella hooks dedicata
                
            break;
        }
        
    }
}