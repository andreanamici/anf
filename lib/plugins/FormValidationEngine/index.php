<?php

use plugins\FormValidationEngine\Form\FormValidationEngine;

/**
 * Registro al kernel.load il plugin per la validazione del form,
 * includendo le functions da usare nelle viste "form_*" e creando un servizio
 * "form_validation", da usare come validatore
 * 
 */
anf('@hooks')->registerHook(function(\Application_HooksData $hookData){
    
    $appConfigs = $hookData->getKernel()->getApplicationConfigs();
    $env        = $hookData->getKernel()->getEnvironment();
    
    $configFilePathByEnv   = $appConfigs->getConfigsFilePath('form_validation_'.$env);
    $configFilePathDefault = $appConfigs->getConfigsFilePath('form_validation');
    
    if(file_exists($configFilePathByEnv))
    {
       $appConfigs->loadConfigsFile('form_validation_'.$env);
    }
    else if(file_exists($configFilePathDefault))
    {
       $appConfigs->loadConfigsFile('form_validation');
    }
    else
    {
       $appConfigs->initMe(__DIR__ . '/configs',  \Interface_ApplicationConfigs::CONFIGS_FILE_EXTENSION_PHP)
                  ->loadConfigsFile('form_validation');                  
    }
    
    $functions = scandir(dirname(__FILE__).'/functions');
    
    if(!empty($functions))
    {
        foreach($functions as $functionFile)
        {
            $functionFilePath = dirname(__FILE__).'/functions/'.$functionFile;

            if(is_file($functionFilePath))
            {
                require_once $functionFilePath;
            }
        }
    }
    
    $formValidationEngine = FormValidationEngine::getInstance();
        
    $hookData->getKernel()->getApplicationServices()
                          ->unregisterService('form_validation')
                          ->registerService('form_validation',$formValidationEngine);
        
}, \Interface_HooksType::HOOK_TYPE_KERNEL_LOAD);