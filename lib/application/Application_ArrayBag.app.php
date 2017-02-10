<?php

/**
 * Estende gli arrayObject fornendo un interfaccia con funzionalità aggiuntive
 */
class Application_ArrayBag extends ArrayObject implements Interface_ArrayTraversable
{
   /**
    * Nome callback invocata quando viene eliminato un indice dell'array
    * @var String
    */
   const ON_OFFSET_UNSET  = 'onOffsetUnset';
   
   /**
    * Nome callback invocata quando viene ricercato un valore
    * @var String
    */
   const ON_OFFSET_GET    = 'onOffsetGet';
   
   /**
    * Nome callback invocata quando si verifica l'esistenza della chiave
    * @var String
    */
   const ON_OFFSET_EXISTS = 'onOffsetExists';
   
   /**
    * Nome callback invocata quando viene creato un indice dell'array
    * @var String
    */
   const ON_OFFSET_SET    = 'onOffsetSet';
   
   /**
    * Nome callback invocata quando si vuole ottenere tutto l'array
    * @var String
    */
   const ON_OFFSET_ALL    = 'onOffsetAll';
   
   /**
    * Nome callback invocata quando si vuole ottenere tutto l'array
    * @var String
    */
   const ON_ARRAY_EXCHANGE   = 'onArrayExchange';
   
   /**
    * Callback invocata quando viene settato un valore all'array bag
    * @var callable
    */
   protected $onOffsetSet   = array();
   
   /**
    * Callback invocata quando viene restituito un valore dall'array bag
    * @var callable
    */
   protected $onOffsetGet   = array();
   
   /**
    * Callback invocata quando viene richiesto se un elemento esiste nell'array bag
    * @var callable
    */
   protected $onOffsetExists = array();
   
   /**
    * Callback invocata quando viene eliminato un valore dall'array bag
    * @var callable
    */
   protected $onOffsetUnset  = array();
   
   /**
    * Callback invocata quando viene richiesto tutto l'array gestito dall'arrayObject
    * @var callable
    */
   protected $onOffsetAll    = array();
   
   
   /**
    * Callback invocata quando viene impostato il nuovo array da gestire
    * @var callable
    */
   protected $onArrayExchange = array();
   
   /**
    * Instanzia l'ArrayBag Object
    * 
    * @param Array  $array              Array
    * @param String $type               Tipologia di arrayObject, default ARRAY_AS_PROP
    * @param Array  $offsetsCallabacks  Array callable invocate in base alle operazioni effettuate sull'ArrayObject (1 sola callable alla volta per tipologia)
    * 
    * @return boolean
    */
   public function __construct($array,array $offsetsCallabacks = array())
   {
      $this->setBagArray($array);
      
      if(count($offsetsCallabacks) > 0)
      {
          foreach($offsetsCallabacks as $offfsetCallbackName => $callable)
          {
             $this->registerCallback($offfsetCallbackName, $callable);
          }
      }
      
      return true;
   }
   
   /**
    * Registra una callback per tutti i metodi disponibili
    * 
    * @param String   $offsetCallbackType  Tipolgia di callback (una delle self::ON_OFFSET_*)
    * @param callable $callable            Callable     
    * 
    * @return \Application_ArrayBag
    * 
    * @throws Exception
    */
   public function registerCallback($offsetCallbackType,callable $callable)
   {
       if(!$this->_isValidCallbackType($offsetCallbackType))
       {
          throw new \Exception('Questa tipologia di callback non è valida per questo oggetto: '.$offsetCallbackType,2837498237589255);
       }
       
       if(!is_callable($callable))
       {
           throw new \Exception('callable non valida per la tipogia specificata di offset: '.$offsetCallbackType,9025723942074);
       }
       
       switch($offsetCallbackType)
       {
           case self::ON_OFFSET_SET:      $this->onOffsetSet[]     = $callable; break;
           case self::ON_OFFSET_UNSET:    $this->onOffsetUnset[]   = $callable; break;
           case self::ON_OFFSET_EXISTS:   $this->onOffsetExists[]  = $callable; break;
           case self::ON_OFFSET_GET:      $this->onOffsetGet[]     = $callable; break;
           case self::ON_OFFSET_ALL:      $this->onOffsetAll[]     = $callable; break;
           case self::ON_ARRAY_EXCHANGE:  $this->onArrayExchange[] = $callable; break;  
       }
       
       return $this;
   }
   
   /**
    * Ricerca il valore nell'arrayObject, se non esiste, restituisce il default indicato
    * 
    * @param String $key      Indice chiave
    * @param Mixed  $default  Valore di default
    * 
    * @return Mixed
    */
   public function getIndex($key, $default = false)
   {
      $return = false;
      
      if(function_exists('array_dot_notation'))
      {
         $return =  $this->offsetExists($key) ? $this->offsetGet($key) : array_dot_notation($this->getAll(),$key,$default);
      }
      
      if($this->onOffsetGet)
      {
         return  $this->__arrayObjectCallback($this->onOffsetGet,  func_get_args());
      }
      
      return $return ? $return : ($this->offsetExists($key) ? $this->offsetGet($key) : $default);
   }
   
   public function removeIndex($index)
   {
       return $this->offsetUnset($index);
   }
   
   public function addIndex($index, $value,array $options = array())
   {
       return $this->offsetSet($index, $value,$options);
   }
   
   public function exists($index)
   {
       return $this->getIndex($index,false) !== false ? true : false;
   }
 
   /**
    * Ricerca il valore nell'arrayObject se esiste e non è empty(), se non esiste, restituisce il default indicato
    * 
    * @param String $key      Indice chiave
    * @param Mixed  $default  Valore di default
    * 
    * @return Mixed
    */
   public function getVal($key,$default = false)
   {
      $value = call_user_func_array(array($this,'getIndex'),  func_get_args());
      
      if($value!==false)
      {
         if(!empty($value))
         {
            return $value;
         }
      }
      
      return $default;
   }
   
   /**
    * Effettua il merge dell'array gestito da questo actionObject con l'array/ArrayObject indicato
    * 
    * @param array $array
    * 
    * @return \Application_ArrayBag
    */
   public function merge($array)
   {
       return $this->setBagArray(array_merge($this->getArrayCopy(),$array));
   }
   
   public function getArrayCopy()
   {
       $return = parent::getArrayCopy();
       
       if($this->onOffsetAll)
       {
          $return = $this->__arrayObjectCallback($this->onOffsetAll,  func_get_args());
       }
       
       return $return;
   }
   
   
   public function exchangeArray($input)
   {
       $return = parent::exchangeArray($input);

       if($this->onArrayExchange)
       {
          $input   = (array) $input;
          $args    = func_get_args();
          $args[0] = $input;
          
          $return = $this->__arrayObjectCallback($this->onArrayExchange,  $args);
       }
       
       return $return;
   }
   
   /**
    * Ricerca il valore di chiave $index
    * 
    * @param String $index   Indice
    * @param Mixed  $newval  Valore
    * @param Array  $options Opzioni passati alle callback registrate al set del nuovo index
    * 
    * @return Mixed|Void
    */
   public function offsetSet($index, $newval,array $options = array())
   {
       $return =  parent::offsetSet($index, $newval);
       
       if($this->onOffsetSet)
       {
          $return = $this->__arrayObjectCallback($this->onOffsetSet,  func_get_args());
       }
       
       return $return;
   }
   
   /**
    * Elimina il valore di chiave $index
    * 
    * @param String $index  Indice
    * 
    * @return Mixed|Void
    */
   public function offsetUnset($index)
   {
       $return =  @parent::offsetUnset($index);
       
       if($this->onOffsetUnset)
       {
          $return = $this->__arrayObjectCallback($this->onOffsetUnset,  func_get_args());
       }
       
       return $return;
   }
   
   /**
    * Controlla che esista il valore di chiave $index
    * 
    * @param String $index  Indice
    * 
    * @return Boolean
    */
   public function offsetExists($index)
   {
       $return = parent::offsetExists($index);
       
       if($this->onOffsetExists)
       {
          $return = $this->__arrayObjectCallback($this->onOffsetExists,  func_get_args());
       }
       
       return $return;
   }
   
   /**
    * Restitituisce in valore di indice $index
    * 
    * @param String $index  Indice
    * 
    * @return Mixed|NULL
    */
   public function offsetGet($index)
   {
       $return = null;
       
       try
       {
            $return = parent::offsetGet($index);

            if($this->onOffsetGet)
            {
               $return = $this->__arrayObjectCallback($this->onOffsetGet,  func_get_args());
            }
       }
       catch (\Exception $e)
       {
           try
           {
              if($this->onOffsetGet)
              {
                  $return = $this->__arrayObjectCallback($this->onOffsetGet,  func_get_args());
              }
           }
           catch(\Exception $e)
           {
               $return =  null;
           }
       }
       
       return $return;
   }
   
   /**
    * Restitusce tutti i dati contenuti
    * 
    * @return array
    */
   public function getAll()
   {
       $all = $this->getArrayCopy();
      
       $allCallback = $this->__arrayObjectCallback($this->onOffsetAll,  func_get_args());
       
       if(is_array($allCallback))
       {
           $all = array_merge($all,$allCallback);
       }
       
       return $all;
   }
   
   /**
    * Imposta l'array da gestire tramite questa interfaccia
    * 
    * @param array $array   Dati da gestire
    *
    * @return \Application_ArrayObjectBag
    */
   public function setBagArray(array $array)
   {
      $this->exchangeArray($array);
      return $this;
   }
   
   
   public function __get($name)
   {
       return $this->offsetGet($name);
   }
   
   
   public function __set($name, $value)
   {
       return $this->offsetSet($name, $value);
   }
   
   
   public function __unset($name)
   {
       return $this->offsetUnset($name);
   }
   
   /**
    * Processa la callbacks sull'array object
    * 
    * @param array    $callbacks       Lista delle callable da processare
    * @param array    $params          Parametri
    * 
    * @return Mixed
    */
   private function __arrayObjectCallback($callbacks,array $params = array(),$print = false)
   {
       $return = false;
       
       if(is_array($callbacks) && count($callbacks) > 0)
       {
            foreach($callbacks as $callable)
            {
                $callbableClosure = $callable;
                
                if(!$callbableClosure instanceof \Closure)
                {
                    $callbableClosure = function() use($callable){ return call_user_func_array($callable, func_get_args()); };
                }
                
                $callbableClosure = $callbableClosure->bindTo($this,$this);
                $return = call_user_func_array($callbableClosure, $params);
            }
       }
       
       return $return;
   }
   
   /**
    * Indica se la tipologia di callback è valida
    * 
    * @param String $callabackType tipologia di callbacl
    * 
    * @return Boolean
    */
   private function _isValidCallbackType($callabackType)
   {
       return in_array($callabackType,array(
                    self::ON_OFFSET_EXISTS,
                    self::ON_OFFSET_GET,
                    self::ON_OFFSET_SET,
                    self::ON_OFFSET_UNSET,
                    self::ON_OFFSET_ALL,
                    self::ON_ARRAY_EXCHANGE
       ));
   }
}

