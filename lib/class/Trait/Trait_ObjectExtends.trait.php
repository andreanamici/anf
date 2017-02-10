<?php


/**
 * Questo trait permette la multiereditarietà degli oggetti in php (cosa non permessa nativamente)
 */
trait Trait_ObjectExtends
{
    /**
     * Iteratore contenente tutti gli oggetti estesi da questo
     * 
     * @var ArrayIterator
     */
    private $extendedObjectsIterator = null;
    
    /**
     * Estende un altro oggetto (simula l'estenzione, ma in realtà l'oggetto verrà ingabbiato in questo oggetto invocante)
     * 
     * @param Mixed    $object                 Oggetto / Nome della classe da estendere
     * @param Array    $constructorArguments   [OPZIONALE] Parametri da passare al costruttore, default array()
     * @param Boolean  $forceSingleton         [OPZIONALE] Indica se forzare il singleto, bypassandolo, default FALSE
     * 
     * @return self
     * 
     * @throws \LogicException
     */
    public function extend($class, array $constructorArguments = array(), $forceSingleton = false)
    {        
        if(is_array($class))
        {
            foreach($class as $value)
            {
                $this->extend($value, $constructorArguments);
            }
        }
        else
        {
            $reflectionObject = new ReflectionClass($class);
            $object           = $reflectionObject->hasMethod('getInstance') && !$forceSingleton ? $reflectionObject->getMethod('getInstance')->invokeArgs(null,$constructorArguments) : $reflectionObject->newInstanceArgs($constructorArguments);

            if(is_null($this->extendedObjectsIterator))
            {
                $this->extendedObjectsIterator = new ArrayIterator();
            }

            $extended = false;

            if($this->extendedObjectsIterator->count() > 0)
            {
                foreach($this->extendedObjectsIterator as $objectExtended)
                {
                    if(is_subclass_of($object,get_class($objectExtended)))
                    {
                       $this->extendedObjectsIterator->offsetSet(get_class($objectExtended), $object); 
                       $extended = true;
                    }
                    else if(is_subclass_of($objectExtended,get_class($object)))
                    {
                       throw new \LogicException('Cannot extend this object '.get_class($object).' becouse this object is already extedende by '.get_class($objectExtended).' for '.  get_called_class());
                    }
                }
            }

            if(!$extended)
            {
                $this->extendedObjectsIterator->offsetSet(get_class($object), $object); 
            }
        }
        
        return $this;
    }
    
    /**
     * Verifica che l'oggetto ne estenda un'altro
     * 
     * @param String $className nome della classe
     * 
     * @return boolean
     */
    public function hasExtend($className)
    {
        try
        {
            return $this->getExtended($className) ? true : false;
            
        } catch (Exception $ex) {
                return false;
        }
        
        return false;
    }
    
    /**
     * Restituisce l'istanza dell'oggetto esteso
     * 
     * @param String $className nome classe
     * 
     * @return Mixed
     * 
     * @throws \LogicException
     */
    public function getExtended($className)
    {
        if($this->extendedObjectsIterator->count() == 0 || !$this->extendedObjectsIterator->offsetExists($className))
        {
            throw new LogicException('This object '.get_called_class().' does not extend '.$className. ' class ');
        }
        
        return $this->extendedObjectsIterator->offsetGet($className);
    }
    
    /**
     * Attraverso il metodo __call viene simulato l'estenzione multipla degli oggetti
     * 
     * @param String $methodName    Nome del metodo
     * @param Array  $args          Argomenti
     * 
     * @return Mixed
     * 
     * @throws \LogicException
     */
    public function __call($methodName,$args)
    {
         if($this->extendedObjectsIterator->count() > 0)
         {
            foreach($this->extendedObjectsIterator as $objectExtended)
            {
                if(method_exists($objectExtended, $methodName))
                {
                    $reflectionObject  = new ReflectionObject($objectExtended);
                    $reflectionMethod  = $reflectionObject->getMethod($methodName);
                    
                    if(!$reflectionMethod->isPublic())
                    {
                        throw new \LogicException("This method ".$methodName." for object ".get_class($objectExtended) ." is not public and is not callable from this class ".get_class($this));
                    }
                    
                    $requiredArguments = $reflectionMethod->getNumberOfRequiredParameters();
                    if(count($args) != $requiredArguments)        
                    {
                        throw new \LogicException("This method ".$methodName." required ".$requiredArguments." argument, ".count($args). " given");
                    }
                    
                    //Call Method class extended
                    return call_user_func_array(array($objectExtended,$methodName),$args);
                }
            }
         }
         
         throw new LogicException("Method {$methodName} is not defined!");
    }
    
}