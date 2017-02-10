<?php

/**
 * Appende al tag title il meta di generazione
 */
class Hooks_AnfMetaGenerator extends Abstract_Hooks
{
    public function __construct()
    {
        $this->initMe(self::HOOK_TYPE_PRE_RESPONSE);
    }
    
    
    public function doProcessMe(\Application_HooksData $hookData)
    {   
        if($siteGenerator = $hookData->getKernel()->get("%SITE_GENERATOR",[],null))
        {
            $controllerAction = $hookData->getKernel()->get('@controller',[],null);

            if($controllerAction && $controllerAction->isMainAction())
            {
                $response = $hookData->getData();
                $content  = $response->getContent();
                if(!strstr($content,'meta name="generator"'))
                {
                    $content  = str_replace('</title>','</title>'."\n\t\t".'<meta name="generator" content="'.$siteGenerator.'" />',$content);
                    $response->setContent($content);
                    $hookData->setData($response);
                }
            }
        }
    }
}