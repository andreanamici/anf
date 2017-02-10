<?php

namespace webUtility;

class webUtility extends \Abstract_Package
{
    public function isEnable() 
    {
        return false;
       return $this->getKernelDebugActive();
    }
    
    public function getConfigsFileExtension()
    {
        return self::CONFIGS_FILE_EXTENSION_YAML;
    }
}