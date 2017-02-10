<?php

/**
 * Action Object Closure invocata dagli ActionController qualora l'action invocata sia una callable
 * 
 * Questo actionObject è un wrapper per poter utilizzare funzioni anonime, ed altri oggetti che non estendono gli actionObject nativi del framework
 * Tutti i metodi magici degli actionObject verranno processati anche sull'actionObject wrappato da questo oggetto, come ad esempio il metodo __doOnInit etc..
 * 
 * Anche per questi oggetti è attivo il meccanismo di dependencyInjection sui metodi, settando i nomi delle variabili dei metodi che corrispondono al nome
 * dei service instanziati nell'Application_Services
 * 
 */
class Basic_ActionObjectCallable extends \Basic_ActionObject
{
    /**
     * ActionController che ha generato questo actionObject
     * 
     * @var \Controllers_ActionController
     */
    protected $actionController = null;
    
    /**
     * Instanza dell'oggetto utilizzata per processare l'action
     * 
     * @var mixed
     */
    protected $object = null;
    
    /**
     * Metodo invocato sull'oggetto della callable
     * 
     * @var String
     */
    protected $method = null;
    
    /**
     * Callable utilizzata, object/function
     * 
     * @var callable
     */
    protected $callable = null;
    
    
    protected $toString = '';

    
    /**
     * Action Object Closure invocata dagli ActionController qualora l'action invocata sia una callable
     * 
     * @param \Controllers_ActionController $actionController Instanza dell'actionController
     * @param callable                      $callable         Callable che processerà l'action
     * 
     */
    public function __construct(\Controllers_ActionController $actionController = null,$callable = null)
    {
        parent::__construct();
        
        $this->toString = '';
        
        if($actionController)
        {
            $this->actionController = $actionController;
        }
        
        if($callable)
        {
            $this->setCallable($callable);
        }
        
        $this->setTemplateList(array(),false);
    }
    
    public function getFallbackMethodName()
    {
        return 'doProcessMe';
    }


    /**
     * Restituisce l'oggetto reale che processera l'action
     * 
     * @return mixed
     */
    public function getObject()
    {
        return $this->object;
    }
    
    /**
     * Restituisce il metodo invocato sull'oggetto
     * 
     * @return String
     */
    public function getMethod()
    {
        return $this->method;
    }
    
        
    /**
     * Restituisce la callable generata per processare questo actionObject
     * 
     * @return callable
     */
    public function getCallable()
    {
        return $this->callable;
    }
    
    /**
     * Imposta la closure function da eseguire
     * 
     * @param \Closure $closure Closure
     * 
     * @return \Abstract_ActionObject
     */
    public function setCallable($callable)
    {        
        $callable = $callable;
        $method   = null;
        
        if(is_array($callable) && count($callable) == 2)
        {
           $className  = $callable[0];
           $object     = null;
           
           if(is_string($className))
           {
                if(!class_exists($className))
                {
                    return self::throwNewException(20934082348274, 'La callable indicata utilizza un nome di una classe non valida: '.$className);
                }
                
                $constructParameters = $this->actionController->getObjectMethodParameters($className, '__construct');

                if(method_exists($className, 'getInstance'))
                {
                   $object  =  call_user_func_array(array($className,'getInstance'),$constructParameters ? $constructParameters : array());
                }
                else
                {
                    $reflectionClass = new ReflectionClass($className);
                    $object = $reflectionClass->newInstanceArgs($constructParameters);
                }
           }
           else
           {
                $object = $className;
           }
           
           if(!is_object($object))
           {
              return self::throwNewException(849656452054, 'La rotta '.$this->getApplicationRoutingCurrentRouteData()->getRouteName().' utilizza una callable non valida perchè  "'.get_class($object).'" non è un oggetto valido da instanziare');
           }
           
           $method = $callable[1];
          
           $this->object = $object;
           $this->method = $method;
           
           /**
            * Passo tutti gli attributi dell'actionController instanziato all'actionController di base invocato che sto utilizzando per processare la request
            */
           if($object instanceof \Application_Controller && $this->actionController instanceof \Application_Controller) 
           {
              foreach($this->actionController->getAllProperties() as $property => $value)
              {
                  $object->setProperty($property,$value);
              }
           }
           
           if(strlen($method) > 0 && !method_exists($object, $method))
           {
              return self::throwNewException(29384923406923, 'La rotta '.$this->getApplicationRoutingCurrentRouteData()->getRouteName().' utilizza una callable non valida perchè l\'oggetto "'.get_class($object).'" non ha il metodo "'.$method.'" ', NULL,\Exception_ExceptionHandler::DEFAULT_EXCEPTION_CLASS_NAME,(string) $this);
           }
          
           $callable = array($object,$method);
        }
        /**
         * Bindo la closure a questo actionObject
         */
        else if($callable instanceof \Closure)
        {
           $this->object = null;
           $callable = $callable->bindTo($this,$this);           
        }
        else if(function_exists($callable))
        {
            $this->object   = null;
        }
        
        
        $this->_methodName = $method;    
        $this->callable    = $callable;
                
        return $this;
    }
    
    
    /**
     * Processa la Callable
     * 
     * @param \Application_ActionRequestData $requestData
     * 
     * @return \Abstract_ActionObject
     */
    public function doProcessMe(\Application_ActionRequestData $requestData)
    {    
        if($this->object)
        {
            $this->getApplicationServices()->registerService('controller.action', $this->object);
        }
        
        $response = call_user_func_array($this->callable,  func_get_args()); //Processo l'action
        
        if(is_object($response) && ($response instanceof $this))
        {
            return $this;
        }    
        else if(is_array($response))
        {
            return $this->setResponse($response);
        }
        else if($response instanceof \Application_ControllerResponseData)
        {
            return $response;
        }
        else if(is_string($response))
        {
            return response($response);
        }
        
        if($this->object instanceof \Abstract_ActionObject)
        {
            return $this->throwNewException(94035203949925, 'La response restituita da questo actionObject "'.$this.'" non è valida. Può essere restituito un array, un istanza di \Application_ControllerResponseData o un boolean',  Interface_ExceptionThrowers::TYPE_ERROR,  Interface_ExceptionThrowers::DEFAULT_EXCEPTION_CLASS_NAME,$this->object);
        }
        
        return $this->throwNewException(674563478468456, 'La response restituita da questo actionObject "'.$this.'" non è valida. Deve essere restituito un\'istanza di \Application_ControllerResponseData',  Interface_ExceptionThrowers::TYPE_ERROR,  Interface_ExceptionThrowers::DEFAULT_EXCEPTION_CLASS_NAME,$this->object);
    }
    
    public function __clone()
    {
        $this->callable = $this->callable->bindTo($this, $this);
    }
    
    public function __call($name, $arguments)
    {        
        if(method_exists($this->object, $name))
        {
            return call_user_func_array(array($this->object,$name),$arguments);
        }
        else if(method_exists($this, $name))
        {
            return call_user_func_array(array($this,$name),$arguments);
        }
        
        return self::throwNewException(598646894856, 'Il metodo '.$name.' non esiste per questo ActionObject callable '.$this);
    }
    
    /**
    * Questo metodo viene invocato quanto l'actionObject è stato inizializzato dall'ActionController, prima di invocare eventuali hooks di preaction
    * 
    * @return boolean
    */
   public function __doOnInit()
   {
      return true;
   }
   

   public function __doManipulateActionRequestData(\Application_ActionRequestData $actionRequestData)
   {
      if($this->object && method_exists($this->object, '__doManipulateActionRequestData'))
       {
           return call_user_func_array(array($this->object,'__doManipulateActionRequestData'),array($actionRequestData));
       }
       
       return parent::__doManipulateActionRequestData($actionRequestData);
   }
   
   public  function __doOnPreProcess(\Application_ActionRequestData $actionRequestData)
   {
       if($this->object && method_exists($this->object, '__doOnPreProcess'))
       {
           return call_user_func_array(array($this->object,'__doOnPreProcess'),array($actionRequestData));
       }
       
       return parent::__doOnPreProcess($actionRequestData);
   }

   public  function __doOnPostProcess(array $responseAdapted)
   {
       if($this->object && method_exists($this->object, '__doOnPostProcess'))
       {
           return call_user_func_array(array($this->object,'__doOnPostProcess'),array($responseAdapted));
       }
       
       return parent::__doOnPostProcess($responseAdapted);
   }
    
   
    /**
     * Reppresenta questo actionObject in formato string
     * 
     * @return String
     */
    public function __toString() 
    {                     
       $string = '';
       
       if(!$this->toString)
       {
            if(is_object($this->object))
            {
                try
                {
                    $string = (string) $this->object;
                }
                catch(\Exception $e)
                {
                    $string = (string) get_class($this->object);
                }
            }

            $this->toString = $string;

            if(!$this->toString)
            {
                $this->toString = parent::__toString();
            }
       }
       
       return $this->toString;
    }
    
    public function getAbsolutePath()
    {
       if(is_object($this->object))
       {
           try
           {
               $reflectionClass    = new ReflectionClass($this->object);
               return $reflectionClass->getFileName();
           }
           catch(\Exception $e)
           {
               return parent::getAbsolutePath();
           }
       }
        
       return parent::getAbsolutePath();
    }
}
