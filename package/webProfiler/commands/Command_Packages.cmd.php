<?php

namespace webProfiler\commands;

class Command_Packages extends \Abstract_Commands
{
    
    public function getName() 
    {
        return 'debug:package:list';
    }
    
    public function doProcessMe() 
    {
        $packages = $this->getApplicationKernel()->getPackagesRegistered()->getArrayCopy();
        
        $list    =  "<ul>{list}</ul>";
        $listLi  = "";
        
        foreach($packages  as $package)
        {
            $listLi.= '<li><strong>'.(string) $package.'</strong> in '.$package->getAbsolutePath().'</li>';
        }
        
        $list = str_replace("{list}",$listLi,$list);
        
        return $this->setResponse('<h2>Packages: </h2>'.$list);
    }
    
}
