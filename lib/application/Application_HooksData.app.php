<?php

/**
 * Classe che gestisce i dati inviati agli hook
 */
class Application_HooksData
{
    /**
     * Application Route Data elaborata dal Routing
     * @var Application_RoutingData
     */
    protected $_application_route_data = null;

    /**
     * Dati locali per questo hook
     * @var Mixed
     */
    protected $_data = null;

    /**
     * Controller Attuale
     * @var Controllers_ActionController
     */
    protected $_controller = null;

    /**
     * Restituisce il kernel usato
     * @var Application_Kernel
     */
    protected $_kernel = null;

    /**
     * Action Object processato
     * @var \Abstract_ActionObject
     */
    protected $_action_object = null;

    /**
     * Indica se fermare lo stack degli hook attualmente gestiti
     * @var Boolean
     */
    protected $_propagation_stop = false;

    /**
     * Classe che gestisce i dati passati agli hooks
     * @param Array $array
     */
    public function __construct()
    {
        return true;
    }

    /**
     * Imposta l'actionObject
     * 
     * @param \Abstract_ActionObject $actionObject
     * 
     * @return Application_HooksData
     */
    public function setActionObject(\Abstract_ActionObject $actionObject)
    {
        $this->_action_object = $actionObject;
        return $this;
    }

    /**
     * Restiusce l'actionObject da elaborare
     * 
     * @return \Abstract_ActionObject
     * 
     * @throws Exception Se l'ActionObject non è inizializzato
     */
    public function getActionObject()
    {
        if ($this->_action_object instanceof \Abstract_ActionObject)
        {
            return $this->_action_object;
        }
        
        throw new Exception("Non è possibile accedere all' ActionObject, questo hook è lanciato prima della sua inizializzazione da parte dell'ActionController", 9347289342643);
    }

    /**
     * Imposta il rifermento all'attuale controller
     * 
     * @param Application_Controller $controller Controller
     * 
     * @return Application_HooksData
     */
    public function setController(\Application_Controller $controller)
    {
        $this->_controller = $controller;
        return $this;
    }

    /**
     * Imposta il Kernel attualmente utilizzato
     * 
     * @param Application_Kernel $kernel Kernel
     * 
     * @return Application_HooksData
     */
    public function setKernel(\Application_Kernel $kernel)
    {
        $this->_kernel = $kernel;
        return $this;
    }

    /**
     * Restiusce il controller attualmente gestito dal Kernel
     * 
     * @return Controllers_ActionController
     * 
     * @throws Exception Se il controller non è inizializzato
     */
    public function getController()
    {
        if ($this->_controller instanceof \Application_Controller)
        {
            return $this->_controller;
        }

        throw new Exception("Non è possibile accedere al controller, questo hook è lanciato prima della sua inizializzazione nel Kernel.", 19239192391239);
    }

    /**
     * Restiusce il Kernel attualmente utilizzato
     * 
     * @return Application_Kernel
     */
    public function getKernel()
    {
        return $this->_kernel;
    }

    /**
     * Imposta tutti i dati aggiuntivi
     * 
     * @param Mixed $data Data opzionale per questo hook
     * 
     * @return Mixed
     */
    public function setData($data)
    {
        $this->_data = $data;
        return $this;
    }

    /**
     * Restiusce i dati aggiuntivi elaborati
     * 
     * @return Mixed
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * [ALIAS]
     * 
     * Restituisce la request HTTP
     * 
     * @return \Application_HttpRequest|null
     * 
     * @throws \Exception
     */
    public function getHttpRequest()
    {
        return $this->_kernel->getApplicationHttpRequest();
    }
    
    /**
     * Imposta lo stop della propagazione degli altri hook di questo tipo
     * 
     * @param Boolean $propagationStop TRUE|FALSE
     * 
     * @return \Application_HooksData
     */
    public function setPropagationStop($propagationStop)
    {
        $this->_propagation_stop = $propagationStop;
        return $this;
    }
    
    /**
     * Stoppa la propagazione di questa tipologia di hook
     * 
     * @return \Application_HooksData
     */
    public function stopPropagation()
    {
        return $this->setPropagationStop(true);
    }
    
    /**
     * Continua la propagazione di questa tipologia di hook
     * 
     * @return \Application_HooksData
     */
    public function continuePropagation()
    {
        return $this->setPropagationStop(false);
    }
    

    /**
     * Restituisce lo stop della propagazione degli hooks
     * 
     * @return Boolean
     */
    public function getPropagationStop()
    {
        return $this->_propagation_stop;
    }

    /**
     * Imposta il Route Data attuale
     * 
     * @param Application_RoutingData $appRouteData Route Data attuale
     * 
     * @return \Application_HooksData
     */
    public function setApplicationRouteData(Application_RoutingData $appRouteData)
    {
        $this->_application_route_data = $appRouteData;
        return $this;
    }

    /**
     * Restituisce il Route Data attuale
     * 
     * @return Application_RoutingData
     * 
     * @throws \Exception
     */
    public function getApplicationRouteData()
    {
        if($this->_application_route_data)
        {
            return $this->_application_route_data;
        }
        
        throw new \Exception('Non è possibile accedere ai dati di Routing poiché non è stato ancora inizializzato dall\'applicationRouting',90252709373455);
    }

}
