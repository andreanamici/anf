<?php

use plugins\AnnotationsReader\Components\Reader;
use plugins\AnnotationsReader\Components\Annotation;

/**
 * Questo hook si occupa di caricare le configurazioni sfruttando le annotazioni
 */
class Hooks_AnnotationLoader extends \Abstract_Hooks
{
    /**
     * Kernel
     * 
     * @var \Application_Kernel
     */
    private $kernel;
    
    /**
     * Reader delle annotazioni
     * 
     * @var plugins\AnnotationsReader\Components\Reader
     */
    private $annotationReader;
    
    
    public function __construct()
    {
        return $this->initMe(self::HOOK_TYPE_KERNEL_LOAD);
    }

    /**
     * Carica le annotazioni per le Rotte, Configurazioni, Servizi nei Controllers e negli ActionObjects
     * 
     * @param \Application_HooksData $hookData
     */
    public function doProcessMe(\Application_HooksData $hookData)
    {
        $this->kernel            = $hookData->getKernel();
        $this->annotationReader  = $this->kernel->get('*AnnotationsReader');
        
        $paths  = array(
              APPLICATION_APP_PATH.'/controllers',
              APPLICATION_APP_PATH.'/action'
        );
        
        $packages = $this->kernel->getPackagesRegistered();
        
        foreach($packages as $package)/*@var $package \Abstract_Package*/
        {
            $paths[] = $package->getActionObjectAbsolutePath();
            $paths[] = $package->getAbsolutePath().'/controllers';
            $paths[] = $package->getAbsolutePath().'/Controlller';
        }
        
        foreach($paths as $path)
        {
            $files   = $hookData->getKernel()->utility->File_getFilesInDirectory($path);
        
            $appConfigs = $this->kernel->getApplicationConfigs();

            foreach($files as $fileName)
            {
                if(strstr($fileName,'php'))
                {
                   $filePath = $path.'/' . $fileName;
                   $this->_load($filePath);
                }
            }
        }
    }
    
    private function _load($filePath)
    {
        $className = $this->kernel->getApplicationAutoload()->getClassesInFile($filePath, null,true);
        
        $this->annotationReader->setClass($className);
        $this->annotationReader->setProperties(array('Route','Service','Config'));

        $annotations      = $this->annotationReader->read();
       
        $appAutoload      = $this->kernel->getApplicationAutoload();
        $appRouting       = $this->kernel->getApplicationRouting();
        $appService       = $this->kernel->getApplicationServices();
        $appConfigs       = $this->kernel->getApplicationConfigs();
            
        if(!empty($annotations))
        {
            foreach($annotations as $annotation) /*@var $annotation Annotation*/
            {
                switch($annotation->getName())
                {
                    case 'Route':

                        $routeInfo = $annotation->getValue();
                        
                        if($routeInfo)
                        {
                            $routeName = key($routeInfo);
                            
                            if(!isset($routeInfo[$routeName]['action']))
                            {
                                $routeInfo[$routeName]['action'] = $annotation->getClass().'::'.$annotation->getMethod();
                            }
                            
                            $appRouting->addRoutingMaps($routeInfo,true,true);    
                        }
                        else
                        {
                            return self::throwNewException(457237498237492834, 'Non è possibile importare l\'annotazione "Route" per l\'oggetto '.($method ? $className.'::'.$method->getName() : $className).' poiché i parametri passati non sono un JSON valido!');
                        }

                    break;

                    case 'Service':

                        $appService->registerServices($annotation->getValue());

                    break;

                    case 'Config':

                        $configName  = key($annotation->getValue());
                        $configValue = $configData[$configName];
                        $appConfigs->addConfig($configName, $configValue,false);

                    break;
                }
            }
        }
        
        return $this;
    }
}