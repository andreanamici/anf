<?php

/**
 * Questo hook si occupa di impostare la lingua da usare nell'applicazione
 * 
 * Invocato subito dopo che il kernel instanzia l'actionController principale
 * 
 */
class Hooks_LocaleSelector extends \Abstract_Hooks
{
    
    public function __construct(array $parameters = null)
    {
        $this->initMe(self::HOOK_TYPE_POST_CONTROLLER);
    }
    
    public function doProcessMe(\Application_HooksData $hookData)
    {
        $controller = $hookData->getData();
        
        $appLanguage    = $hookData->getKernel()->get('@translate');/*@var $appLanguage Application_Languages*/
        $sessionManager = $hookData->getKernel()->get('@session'); /*@var $sessionManager Application_SessionManager*/
        $httpRequest    = $hookData->getKernel()->get('@httprequest');/*@var $httpRequest Application_HttpRequest*/
        
        $lang           = $httpRequest->get('lang',$sessionManager->getIndex('lang',null));
        
        if($lang)
        {
            $appLanguage->changeLanguage($lang);
        }
        
        $sessionManager->addIndex("lang",$appLanguage->getPortalLanguage());
        $sessionManager->addIndex("locale",$appLanguage->getPortalLocale());
    }        
}