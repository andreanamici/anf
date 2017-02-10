<?php

/**
 * Controller per le action che generano HTML
 */
class Controllers_ActionControllerHTML extends Controllers_ActionController
{
   
    /**
     * Controller per la gestione delle ActionObject HTML
     * @return boolean
     */
    public function  __construct() 
    {
        parent:: __construct();
        return true;
    }

    /**
     * Distruttore
     * @return Boolean
     */
    public function  __destruct() 
    {
        parent::__destruct();
        unset($this);
        return true;
    }
    
    /**
     * Processa l'oggetto Action instanziato e ne restituisce una response utile fruibile dal Kernel
     * 
     * @return Application_ControllerResponseData
     */
    public function doActionProcess()
    {
         $actionObject           = $this->_action_object;
         
         $actionResponse         = $this->doActionObjectProcess()->getActionResponse();
         
         if($actionResponse instanceof \Application_ControllerResponseData)
         {
             return $actionResponse;
         }
         
         if($this->_action_exception)
         {
             return $this->generateControllerResponse('['.$this->_action_exception->getCode().'] '.$this->_action_exception->getMessage());
         }
         
         $actionType             = $actionResponse->getActionType();
         $actionResponse         = $actionResponse->getResponse()->getArrayCopy();
         
         if($actionResponse!==false)
         {
            switch($actionType)
            {
               /**
                * Tipologie di Action finalizzate a processare html
                */
               case \Interface_ActionObject::ACTION_TYPE_INT:
               case \Interface_ActionObject::ACTION_TYPE_EXT:
               case \Interface_ActionObject::ACTION_TYPE_ALL:
                        
                        $templating     = $this->getApplicationTemplating();
                   
                        $currentPackage           = $templating->getPackage();
                        $currentTplFileExtensions = $templating->getTemplateFileExtension();
                        $currentTplEngine         = $templating->getTemplateEngine();
                        $currentTplSubFolder      = $templating->getTemplateSubFolder();
                        
                        $templating->clearTemplateList();
                        
                        $templateList           = $actionObject->getTemplateList();              //Lista di template da caricare
                        $templateEngine         = $actionObject->getTemplateEngine();            //Template Engine da utilizzare per il Rendering
                        $templateFileExtension  = $actionObject->getTemplateFileExtension();     //Estenzione file tpl
                        $templateSubFolder      = $actionObject->getTemplateSubFolder();
                        
                        if(is_array($templateList) && count($templateList)>0)
                        {
                           $templating->addTemplateArr($templateList);
                        }
                                    
                        $responseContent = $templating
                                                ->setTemplateEngine($templateEngine)
                                                ->setTemplateFileExtension($templateFileExtension)
                                                ->setTemplateSubFolder($templateSubFolder)
                                                ->addParamsArray($actionResponse)
                                                ->setPackage($actionObject->getPackageInstance() ? $actionObject->getPackageInstance()->getName() : null)
                                                ->view();
                        
                        /**
                         * Ripristino i valori base
                         */
                        $templating->setPackage($currentPackage)
                                   ->setTemplateEngine($currentTplEngine)
                                   ->setTemplateSubFolder($currentTplSubFolder)
                                   ->setTemplateFileExtension($currentTplFileExtensions);
                        
                        return $this->generateControllerResponse($responseContent);
                        
               break;
                                                
               default:
                        
                    self::throwNewException(1239192391293," Tipologia di Action invalida per questo Controler: ".__CLASS__.", response: ".$actionType);

               break;
            }
         }
         
         return self::throwNewException(94584734734,"Errore, Response Invalida!");
    }
   
}