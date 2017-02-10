<?php

class Hooks_TemplateServicesInjection extends \Abstract_Hooks
{
    
    public function __construct()
    {
        $this->initMe(self::HOOK_TYPE_PRE_TEMPLATE);
    }
    
    
    public function doProcessMe(\Application_HooksData $hookData)
    {        
        $data = $hookData->getData();
        
        $data['params'] = array_merge(is_array($data['params']) ? $data['params'] : array(),array(
            
            'httprequest' => $hookData->getKernel()->get('@httprequest'),
            'session'     => $hookData->getKernel()->get('@session'),
            'cookie'      => $hookData->getKernel()->get('@cookie'),
            'configs'     => $hookData->getKernel()->get('@config')->getAllConfigs()->getArrayCopy()

        ));
        
        $hookData->setData($data);
    }
    
}