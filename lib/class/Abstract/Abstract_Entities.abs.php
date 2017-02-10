<?php

/**
 * Classe astratta padre di tutte le Entities gestite dagli Entities Entity Manager
 */
abstract class Abstract_Entities implements Interface_Entities
{
    
   /**
    * Questa costante serve per determinare quando un valore di una proprietà dell'entity cambia rispetto al valore iniziale di quanto viene instanziata, 
    * prima che l'entityManager popoli il valore dell'attributo dal campo del database tramite PDO
    * 
    * @var String
    */
   const NULL                               = "__NULL__"; 
    
   /**
    * Nome della classe
    * 
    * @var String
    */
   protected static  $_class_name           = __CLASS__;
   
   /**
    * Fields statici,  gestiti da questo Entity
    * 
    * @var Array
    */
   protected static  $_fields               = Array();   
   
   /**
    * Properties gestiti da questo Entity
    * 
    * @var Array
    */
   private         $_properties           = Array();
   
   /**
    * Methodi Closure gestiti dall'Entity
    * 
    * @var Array
    */
   private         $_methods              = Array();
   

   
   /**
    * Restituisce il nome dell'entità
    * 
    * @return String
    */
   public static function getClassName() 
   {
      return static::$_class_name;
   }
   
   
   /**
    * Controlla che il valore non sia NULL e che sia diverso da Abstract_Entities::NULL
    * 
    * @param valore da controllare $value
    * 
    * @return Boolean
    */
   public static function isNULL($value)
   {
       return is_null($value) || $value == self::NULL;
   }

   /**
    * Verifica che il campo dell'entity non sia NULL o self::NULL
    * 
    * @param String $entityFieldName Campo dell'entity
    * 
    * @return Boolean
    * 
    * @throws Exception Qualora l'entity non abbia il campo indicato
    */
   public function isNULLField($entityFieldName)
   {
       if($this->hasField($entityFieldName))
       {
           return self::isNULL($this->$entityFieldName);
       }
       
       throw new Exception('Questa Entità non ha il campo '.$entityFieldName.' definito! ',9238427348);
   }
   
   /**
    * Invocato Quando si tenta di settare una attributo / metodo non dichiarato nell'entità
    * 
    * @param String $name  Nome della proprietà/metodo
    * @param Miexd  $value Valore
    * 
    * @return Void
    */
   public function __set($name,$value)
   {         
      if((preg_match("/^get[A-z0-9]+/",$name) || preg_match("/^set[A-z0-9]+/",$name)) && ($value instanceof Closure))    //Sto creando un metodo Closure di GET / SET property $this-><method>  = <Closure function>
      {
         $this->addMethod($name,$value);
      }
      else  //Sto assegnando il Valore all'attributo $this-><property>  = <value>
      {
         $this->addStaticField($name);
         $this->setField($name, $value);
         $this->init();
      }      
      
      return true;
   }
   
   /**
    * Invocato quando si tenta di accedere ad una attributo / metodo non dichiarato nell'entità
    * 
    * @param String $name  Nome della proprietà/metodo
    * 
    * @return Mixed
    */
   public function __get($name)
   {
      if(!preg_match("/^get(.*)/",$name) && !preg_match("/^set(.*)/",$name))
      {
         return isset($this->_properties[$name]) ?  $this->_properties[$name] : false;
      }
      
      return false;
   }
   
   /**
    * Controlla che l'attributo esista sull'entità
    * 
    * @param String $name Nome attributo
    * 
    * @return Boolean
    */
   public function __isset($name)
   {      
      if(!preg_match("/^get(.*)/",$name) && !preg_match("/^set(.*)/",$name))
      {
         return array_key_exists($name, $this->_properties);
      }
  
      return false;
   }
   
   /**
    * Eseguito quando si fa unset() su un attributo dell'oggetto
    * 
    * @param String $name Nome Attributo
    * 
    * @return Void
    */
   public function __unset($name)
   {
      if(!preg_match("/^get(.*)/",$name) && !preg_match("/^set(.*)/",$name))
      {
         unset($this->_properties[$name]);
         
         foreach(static::$_fields as $key => $value)
         {
            if($value == $name){
               unset(static::$_fields[$key]);
            }
            if($key == $name){
               unset(static::$_fields[$key]);
            }
         }
      }
   }
    
   /**
    * Magic method invocato prima della serializzazione tramite function <b>serialize()</b>
    * 
    * @return Boolean
    */
   public function __sleep()
   {
       return $this->_getAllProperties();
   }
   
   /**
    * Magic Method invocato all'unserialize con <b>unserialize()</b>
    * 
    * @returnBoolean
    */
   public function __wakeup()
   {
      foreach($this->_getAllProperties(true) as $fieldName => $fieldValue)
      {
         $this->addField($fieldName, $fieldValue);
         $this->addStaticField($fieldName);
      }
      
      return $this->init();
   }
   
   /**
    * Restituisce la stringa serialized  corrispondente per la classe.
    * 
    * @return String
    */
   public function toString()
   {
      $this->beforeToString();
      
      return serialize($this->toArray());
   }
   
   
   /**
    * Restituisce la stringa serialized  corrispondente per la classe.
    * 
    * @return String
    */
   public function __toString() 
   {
      return $this->toString();
   }
   
   
   /**
    * Callback invocata prima di una query di Insert
    * 
    * @param Abstract_EntitiesManager $entityManager EntityManager
    * 
    */
   public function __beforeInsert(Abstract_EntitiesManager $entityManager)
   {
       
   }
   
   
   /**
    * Callback invocata prima di una query di update
    * 
    * @param Abstract_EntitiesManager $entityManager EntityManager
    */
   public function __beforeUpdate(Abstract_EntitiesManager $entityManager)
   {
       
   }
   
   /**
    * Callback invocata prima di una query di delete
    * 
    * @param Abstract_EntitiesManager $entityManager EntityManager
    * 
    */
   public function __beforeDelete(Abstract_EntitiesManager $entityManager)
   {
       
   }
   
   
   /**
    * Magic Method invocato alla chiamata di ogni attributo/Metodo dell'oggetto
    * 
    * @param String $method  Metodo invocato
    * @param Array  $args    Array argomenti
    * 
    * @return Mixed
    */
   public function __call($method, $args)
   {
      if (isset($this->_methods[$method]) && $this->_methods[$method] instanceof Closure ) 
      {
         return call_user_func_array($this->_methods[$method],$args);
      }
      
      else if(preg_match('/^set/',$method) !== false)
      {
         $field = $this->getFieldNameByMethodCamelCase($method);
         $this->_properties[$field] = $args[0];
         $this->init();
         
         if(isset($this->_methods[$method]) && $this->_methods[$method] instanceof Closure )
         {
            return call_user_func_array($this->_methods[$method],$args);
         }
      }
   }
   
   /**
    * callback invocata prima della trasformazione dell'oggetto in Array
    * 
    * @return Mixed
    */
   protected function beforeToArray()
   {
      return true;
   }
   
   /**
    * callback invocata prima della trasformazione dell'oggetto in Stringa
    * 
    * @return Mixed
    */
   protected function beforeToString()
   {
      return true;
   }
   
   
   /**
    * callback invocata prima del unserialize
    * 
    * @return Mixed
    */
   protected function beforeToWake()
   {
      return true;
   }
   
   
   /**
    * Restituisce L'array associavito con il valore dei campi della classe in formato attributo => valore
    * 
    * @param Boolean $translate  Indica se tradurre eventualmente le stringhe "@<string>@" presenti come valore, default TRUE
    * @param Boolean $null       Indica se restituire gli elementi NULL o vuoti,default TRUE
    * 
    * @return Array
    */
   public function toArray($translate = true,$null = true)
   {
      $retArr = Array();
      
      $this->beforeToArray();
      
      $fields = $this->_getAllProperties(true);
      
      if(is_array($fields) && count($fields)>0)
      {
         foreach($fields as $fieldName => $fieldValue)
         {
             if($translate)
             {
                if(preg_match("/@([A-Za-z0-9\_]+)@/",$fieldValue,$match))
                {
                   $fieldValue =  translate($match[1]);
                }
             }
             
             if(!$null)
             {
                if(!self::isNULL($fieldValue))
                {
                    $retArr[$fieldName] = $fieldValue;
                }
             }
             else
             {
                $retArr[$fieldName] = $fieldValue;
             }
         }
      }
      
      return $retArr;
   }
  
   /**
    * 
    * Aggiunte il nome del campo che verrà preso in condiderazione in caso di serialize dell'object (per cache)
    * 
    * @param String $fieldname Nome del campo dell'oggetto
    * 
    * @return Abstract_Entities
    */
   protected function addStaticField($fieldname)
   {
      if(!in_array($fieldname,static::$_fields))
      {
         return static::$_fields[] = $fieldname;
      }
      
      return $this;
   }
   
   /**
    * Aggiungie un campo a questa Entity
    * 
    * @param String $field  Nome del campo
    * @param Mixed  $value  Valore, default Abstract_Entities::NULL
    * 
    * @return Abstract_Entities
    */
   protected function addField($field,$value = self::NULL)
   {
       $this->_properties[$field] = $value;
        
        if(!$this->hasField($field))
        {
           $this->{$field}  = $value;
        }
        
        return $this;
   }
   
   
   /**
    * Modifica un campo di questa Entity
    * 
    * @param String $field  Nome del campo
    * @param Mixed  $value  Valore
    * 
    * @return Abstract_Entities
    */
   protected function setField($field,$value)
   {
        $this->addField($field, $value);
        
        $this->_properties[$field] = $value;
        $this->{$field}            = $value;
        
        return $this;
   }
   
   /**
    * Restituisce TRUE se l'entity ha il campo indicato
    * 
    * @param String $field Nome del campo
    * 
    * @return Boolean
    */
   protected function hasField($field)
   {
       return property_exists($this, $field);
   }
   
   /**
    * Aggiungue un metodo di "Set" all'entity per il campo specificato
    * 
    * @param String $setMethodName      Nome del metodo di set
    * @param String $field              Nome del campo
    * 
    * @return \Abstract_Entities
    */
   protected function addSetMethod($setMethodName,$field)
   {
      if(!method_exists($this,$setMethodName))
      {
         $_this = &$this;
         
         $this->addMethod($setMethodName,function($val) use ($_this,$field) { 
                   $_this->setField($field, $val); 
                   return $_this;
         });
      }
      
      return $this;
   }
   
   /**
    * Aggiungue un metodo di "Get" all'entity per il campo specificato
    * 
    * @param String $setMethodName      Nome del metodo di set
    * @param String $field              Nome del campo
    * 
    * @return \Abstract_Entities
    */
   protected function addGetMethod($getMethodName,$field)
   {
       if(!method_exists($this,$getMethodName))
       {
          $_this = &$this;
          
          $this->addMethod($getMethodName,  function() use ($_this,$field){
                    if(function_exists("translate"))
                    {
                       if(isset($_this->_properties[$field]) && preg_match("/\@(.*)\@/",$_this->_properties[$field],$matches))
                       {
                          return translate($matches[1]);
                       }
                    }
                    else 
                    {
                       throw new Exception_PortalErrorException("Funzione di traduzione translate() non disponibile!",9933994);   
                    }

                    return $_this->_properties[$field]; 
            });
        }
        
        return $this;
   }
   
   /**
    * Aggiunge un method a questo entity
    * 
    * @param String  $methodName       Nome del metodo
    * @param Closure $methodClosure    Closure function
    * 
    * @return \Abstract_Entities
    */
   protected function addMethod($methodName,Closure $methodClosure)
   {
       $this->_methods[$methodName] = $methodClosure;
       
       return $this;
   }
   
   /**
    * Elimina il nome del campo che verrà preso in condiderazione in caso di serialize dell'object (per cache)
    * 
    * @param String $fieldname Nome del campo dell'oggetto
    * 
    * @return Boolean
    */
   private function deleteStaticField($fieldname)
   {
       foreach(static::$_fields as $key=>$field)
       {
          if($fieldname == $field)
          {
             unset(static::$_fields[$key]);
          }
       }
   }
   
   /**
    * Inizializza Entità 
    * 
    * @param  Boolean $autoBuildSetMethod  Indica se costruire i metodi set dell'entità
    * @param  Boolean $autoBuildGetMethod  Indica se costruire i metodi get dell'entità
    * @param  String  $entName             [OPZIONALE] Nome entità classe child
    * 
    * @return Abstract_Entities 
    */
   protected function init($autoBuildSetMethod = true,$autoBuildGetMethod = true,$entName = null)
   {
       if(strlen($entName)>0)
       {
          static::$_class_name = $entName;
       }
       else
       {
          static::$_class_name = function_exists("get_called_class") ? get_called_class() : static::$_class_name;
       }
       
       if($autoBuildSetMethod)
       {
           $this->_initSetMethods();
       }
       
       if($autoBuildGetMethod)
       {
           $this->_initGetMethods();
       }
      
       return $this;
   }
   
   /**
    * Crea alla classe tutti i metodi Set Publici necessari.
    * 
    * <b>Questo metodo effettua anche le traduzioni delle stringhe fetchate dal db che matchano il pattern /@([A-Za-z0-9\_]+)@/</b>
    * 
    * @return Abstract_Entities
    */
   private function _initSetMethods()
   {
      $fields = $this->_getAllProperties(true);
      
      if(is_array($fields) && count($fields)>0)
      {
         foreach($fields as $key => $value)
         {
            $field         = is_string($key) ? $key       : $value;
            $default_value = is_int($key)    ? self::NULL : $value;

            $match         = Array();
            
            if($this->hasField($field))
            {
                $default_value = $this->{$field};
            }
            
            if(!array_key_exists($field,$this->_properties))
            {
               $this->addField($field, $default_value);
            } 
             
            $setMethodName = $this->_getChildClassSetMethod($field);
            
            $this->addSetMethod($setMethodName,$field);
         }
      }
      
      return $this;
   }
   
   /**
    * Crea alla classe tutti i metodi Get Publici necessari
    * 
    * @return Abstract_Entities
    */
   private function _initGetMethods()
   {
      $fields = $this->_getAllProperties(true);

      if(is_array($fields) && count($fields)>0)
      {            
         foreach($fields as $key => $value)
         {
            $field         = is_string($key) ? $key : $value;       
            $getMethodName = $this->_getChildClassGetMethod($field);
            
            $this->addGetMethod($getMethodName,$field);        
         }
      }

      return $this;
   }
   
   
   /**
    * Costruisce il nome dei Methodo Set della classe invocante su base nome campo db.
    * 
    * esempio: ag_id => setAgId
    * 
    * @param String $db_field Nome del campo dei DB
    * 
    * @return String
    */
   private function _getChildClassSetMethod($field)
   {
      $tmpArr    = explode("_",$field);
      $setMethod = "set";
        
      if(is_array($tmpArr) && count($tmpArr)>0)
      {
         foreach($tmpArr as $value)
         {
            $setMethod.=ucfirst($value); 
         }
      }
      else
      {
         $setMethod.=ucfirst($field); 
      }
        
      return $setMethod;
   }
  
   /**
    * Costruisce il nome dei Methodo Get della classe invocante su base nome campo db.
    * esempio: ag_id => getAgId
    * 
    * @param String $db_field Nome del campo dei DB
    * 
    * @return String
    */
   private function _getChildClassGetMethod($field)
   {
      $tmpArr    = explode("_",$field);
      $setMethod = "get";
        
      if(is_array($tmpArr) && count($tmpArr)>0)
      {
         foreach($tmpArr as $value)
         {
            $setMethod.=ucfirst($value); 
         }
      }
      else
      {
         $setMethod.=ucfirst($field); 
      }
        
      return $setMethod; 
   }
   
   /**
    * Restituisce Tutti i campi, Non Metodi, dell'oggetto
    * 
    * @return Array
    */
   private function _getAllProperties($getvalue = false)
   {
      
      $retPropertiesArrayDefaults = $this->_properties;
      
      $retPropertiesArray = Array();
      
      foreach(get_object_vars($this) as $propertyName => $propertyValue)
      {         
         if(!($propertyValue instanceof Closure) && !is_array($propertyValue) && !preg_match("/^\_[A-z0-9\_]+$/",$propertyValue,$matches))
         {
            if(!array_key_exists($propertyName,$retPropertiesArray))
            {
               $retPropertiesArray[$propertyName] = $propertyValue;    
            }
         }
      }
      
      $retArray =  array_merge(array_flip(static::$_fields),$retPropertiesArrayDefaults,$retPropertiesArray);
      
      
      if(!$getvalue)
      {
         return array_keys($retArray);
      }
      
      return $retArray;
   }
   
   
   /**
    * Restituisce il nome del campo che si intende modificare a partire dal nome del metodo
    * 
    * @param String $camelCaseMethod Nome del metodo
    * @param String $splitter        Stringa di unione del campo
    * 
    * @return String
    */
   private function getFieldNameByMethodCamelCase($camelCaseMethod,$splitter = "_") 
   {
        $fieldName = preg_replace('/(?!^)[[:upper:]][[:lower:]]/', '$0', preg_replace('/(?!^)[[:upper:]]+/', $splitter.'$0', $camelCaseMethod));
        
        $fieldName = str_replace('set_', '', $fieldName);
        
        return strtolower($fieldName);
   }
}