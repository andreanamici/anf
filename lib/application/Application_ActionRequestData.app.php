<?php

/**
 * Classe che gestisce i dati passati agli ActionObject, ed estende l'httpRequest elaborata dal kernel
 * 
 */
class Application_ActionRequestData extends Application_HttpRequest
{   
    /**
     * Parametri passati dall'actionController
     * 
     * @var \Application_ArrayBag
     */
    protected $_action_parameters = null;
    
    /**
     * Inizializza i dati di Request inviati agli ActionObject
     * 
     * @param \Application_HttpRequest $httpRequest HttpRequest [OPZIONALE] utilizza l'httpRequest indicato
     * @param \Application_ArrayBag $actionParameters [OPZIONALE] Parametri passati dall'actionController, default NULL
     */
    public function __construct(\Application_HttpRequest $httpRequest = null,  \Application_ArrayBag $actionParameters = null)
    {
        if($httpRequest)
        {            
            $this->initialize($httpRequest->getRequest()->getAll(false),
                              $httpRequest->getPost()->getAll(false),
                              $httpRequest->getGet()->getAll(false),
                              $httpRequest->getSession()->getAll(false),
                              $httpRequest->getServer()->getAll(false),
                              $httpRequest->getFile()->getAll(false),
                              $httpRequest->getCookie()->getAll(false),
                              $httpRequest->getEnv()->getAll(false));
        }
        
        if(!$actionParameters)
        {
            $actionParameters = new Application_ArrayBag(array());
        }
        
        $this->_action_parameters = $actionParameters;
    }
    
    /**
     * Parametri passati dall'actionController
     * 
     * @return \Application_ArrayBag
     */
    public function getActionParameters()
    {
        return $this->_action_parameters;
    }
    
    
    /**
     * Imposta gli action parameters, utilizzabili dall'actionObject
     * 
     * @param \Application_ArrayBag $parameters parametri
     * 
     * @return \Application_ActionRequestData
     */
    public function setActionParameters(\Application_ArrayBag $parameters)
    {
        $this->_action_parameters = $parameters;
        return $this;
    }
    
    
}
