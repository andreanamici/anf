<?php

/**
 * Questa classe contiene il servizio registrato dall'Application_Services
 */
class Application_ServicesInstance
{    
    use Trait_ApplicationKernel,
            
        Trait_Exception;
    
    /**
     * Instanza del servizio
     * @var mixed
     */
    protected $_serviceInstance = null;
    
    /**
     * Dati utilizzati per instanziare il service
     * @var \ArrayObject 
     */
    protected $_serviceData     = null;
    
    /**
     * Indica se è stato chiamata la callback di registrazione per il servizio
     * 
     * @var Boolean
     */
    protected $_onRegisterCalled = false;
    
    /**
     * Costruisce l'instanza del servizio
     * 
     * @param Mixed $serviceInstance    Instanza / Closure
     * @param Array $serviceData        Dati
     */
    public function __construct($serviceInstance,array $serviceData = array())
    {
        $this->_serviceInstance = $serviceInstance;
        $this->_serviceData     = new \ArrayObject($serviceData);
    }
    
    
    /**
     * Restituisce l'instanza del service
     * 
     * @param Array               $parameters   [OPZIONALE] Parametri utilizzati per i services "lazy" o per i "service-closure", default Array()
     * 
     * @return Mixed
     */
    public function getServiceInstance(array $parameters = array())
    {
        $serviceInstance = $this->_serviceInstance;
        
        /**
         * Se il servizio è "dormiente", eseguo solamente una volta la closure relativa per svegliare il servizio
         */
        if($this->getIsLazy() && !$this->getIsAwake())
        {
           $serviceInstanceLazyCallback = $this->_serviceInstance;
           $serviceInstance             = call_user_func_array($serviceInstanceLazyCallback,$parameters);
           $this->_serviceInstance      = $serviceInstance;
        }
        /**
         * Se il service in realtà è una closure che ritorna qualcosa, la eseguo ogni volta
         */
        else if($this->getIsClosure())
        {
            $serviceClosure  = $this->_serviceInstance;
            $serviceInstance = $serviceClosure($parameters);
        }
        
        /**
         * Applico una callback ai servizi quando vengono invocati, solamente per i service che non sono già una closure
         */
        if(!$this->getIsClosure())
        {
            $callback = $this->getCallback();

            if($callback && is_callable($callback))
            {
                $serviceInstance = call_user_func_array($callback,array($serviceInstance));                
                
                if(empty($serviceInstance))
                {
                    return $this->throwNewException(209374029750235, 'La callback chiamata ad ogni utilizzo del servizio "'.$this->getName().'" deve restituire l\'istanza del service');
                }
            }
        }
        
        return $serviceInstance;
    }
    
    /**
     * Ricerca l'eventuale callback "register_callback" e la esegue quando il servizio viene registrato
     * 
     * @return \Application_ServicesInstance
     */
    public function onRegister()
    {
        if(!$this->_onRegisterCalled)
        {
            $this->getRegisterCallback() ? call_user_func_array($this->getRegisterCallback(), array($this)) : null;
        }
        
        $this->_onRegisterCalled = true;
        
        return $this;
    }
    
    /**
     * Restituisce i dati del service
     * @return \ArrayObject
     */
    public function getServiceData()
    {
        return $this->_serviceData;
    }
    
    /**
     * Indica se il servizio è Lazy (dormiente)
     * @return Boolean
     */
    public function getIsLazy()
    {
        return $this->_serviceData->offsetExists('lazy') ? $this->_serviceData->offsetGet('lazy') : false;
    }
    
    /**
     * Restituisce la callback da invocare quando il servizio viene richiamato
     * 
     * @return callable
     */
    public function getCallback()
    {
        return $this->_serviceData->offsetExists('callback') ? $this->_serviceData->offsetGet('callback') : null;
    }
    
    /**
     * Restituisce la callback da invocare quando il servizio viene registrato
     * 
     * @return callable
     */
    public function getRegisterCallback()
    {
        return $this->_serviceData->offsetExists('registerCallback') ? $this->_serviceData->offsetGet('registerCallback') : null;
    }
    
    /**
     * Restituisce il nome del service
     * @return String
     */
    public function getName()
    {
        return $this->_serviceData->offsetExists('name')     ? $this->_serviceData->offsetGet('name') : null;
    }
    
    /**
     * Indica se il service sarà univoco non sovrascrivibile
     * @return Boolean
     */
    public function getIsUnique()
    {
        return $this->_serviceData->offsetExists('unique')     ? $this->_serviceData->offsetGet('unique') : false;
    }
    
    
    /**
     * Indica se il servizio utilizza un plugin
     */
    public function getIsPlugin()
    {
        return $this->_serviceData->offsetExists('plugin')     ? $this->_serviceData->offsetGet('plugin') : false;
    }
    
    /**
     * Indica se il servizio è un closure, per il quale ogni volta verrà eseguita la closure indicata come "instance".
     * 
     * Questa tipologia di servizio è utile se si vuole aggiungere funzionalità "stand-alone" che ritornano qualcosa.
     *
     * @return Boolean
     */
    public function getIsClosure()
    {
        return $this->_serviceData->offsetExists('closure') ? $this->_serviceData->offsetGet('closure') : false;
    }
    
    /**
     * Restituisce il nome della classe del servizio
     * 
     * @return string
     */
    public function getClassName()
    {
        return $this->_serviceData->offsetExists('class') ? $this->_serviceData->offsetGet('class') : false;   
    }
    
    /**
     * Indica se il servizio è "sveglio" (attivato)
     *
     * @return Boolean
     */
    public function getIsAwake()
    {
        if($this->getIsClosure())
        {
            return true;
        }
        
        if($this->_serviceInstance instanceof \Closure)
        {
            return false;
        }
        
        return true;
    }
    
    /**
     * Nome del service
     * 
     * @return String
     */
    public function __toString()
    {
        return $this->getName();
    }
}