<?php


/**
 * Classe che gestisce gli hook configurati
 */
class Application_Hooks implements Interface_ApplicationHooks
{
      
   use Trait_ObjectUtilities,Trait_Singleton;

   use Trait_ApplicationKernel, Trait_ApplicationConfigs;
      
   /**
    * Indica lo stack di attivazione e di ricerca degli hook per il package configurato
    * 
    * @var Array
    */
   private  $_HOOK_STACK_ORDER_LIST = Array(
       
       self::HOOK_TYPE_KERNEL_LOAD,
       self::HOOK_TYPE_PRE_ROUTING,
       self::HOOK_TYPE_POST_ROUTING,
       self::HOOK_TYPE_PRE_CONTROLLER,
       self::HOOK_TYPE_POST_CONTROLLER,
       self::HOOK_TYPE_PRE_ACTION,
       self::HOOK_TYPE_POST_ACTION,
       self::HOOK_TYPE_PRE_TEMPLATE,
       self::HOOK_TYPE_POST_TEMPLATE,
       self::HOOK_TYPE_PRE_RESPONSE,
       self::HOOK_TYPE_KERNEL_END
   );
   
   /**
    * Array contenente tutte le tipologie di hooks registrate
    * 
    * @var array
    */
   private static $_HOOKS_TYPE_REGISTERED = array();
   
   
   /**
    * Lista hooks attuali da processare per l'hook type corrente
    * 
    * @var ArrayIterator
    */
   private  $_HOOKS_STACK_ITERATOR = null;
   
   
   /**
    * Lista hooks type status (abilitati/disabilitati)
    * 
    * @var ArrayIterator
    */
   private  $_HOOKS_TYPE_STATUS_STACK_ITERATOR = null;
   
   
   /**
    * Tipologia hook attualmente elaborato
    * @var String
    */
   protected $_hook_current_type = false;
   
   
   /**
    * Indica se lo stack di attivazione degli hooks è stato interrotto
    * @var Boolean default false
    */
   protected $_hook_propagation_stop = false;
   
  
   /**
    * Dati passati allo stack degli hook
    * @var Application_HooksData
    */
   protected $_hooks_data = null;
   
   
   /**
    * package attualmente in uso, default NULL
    * <b>Finchè è null, gli hook verranno ricercati nella cartella self::HOOK_DEFAULT_DIRECTORY</b>
    * 
    * @var String
    */
   protected $_hooks_package = NULL;
   
   
   /**
    * Dati restituiti dallo stack di elaborazione degli hooks
    * 
    * @var Application_HooksResponseData  
    */
   protected $_hooks_response_data = null;
   
   /**
    * Indica se gli hooks sono abilitati
    * 
    * @var Boolean
    */
   protected $_is_enable           = true;
   
   /**
    * Tipologia di hook che è in processing
    * 
    * @var String
    */
   protected $_current_hook_type_processing = null;
   
   
   /**
    * Indica se questo manager sta gestendo uno stack di hook, utiler per gl hook processati in altri hook
    * 
    * @var Boolean
    */
   protected $_processing = false;
   
   /**
    * Classe manager degli hooks attivi sul portale
    * 
    * @return boolean
    */
   public function __construct() 
   {
      $this->_hooks_data              = null;
      $this->_hooks_response_data     = new Application_HooksResponseData();
      
      $this->_is_enable = defined("APPLICATION_HOOKS_ENABLE") ? APPLICATION_HOOKS_ENABLE : $this->_is_enable;
      
      $this->_HOOKS_STACK_ITERATOR              = new ArrayIterator();
      $this->_HOOKS_TYPE_STATUS_STACK_ITERATOR  = new ArrayIterator();
      
      static::$_HOOKS_TYPE_REGISTERED            = array_merge(static::$_HOOKS_TYPE_REGISTERED,self::getAllAvailableHooksTypes());
      
      if($this->_is_enable)
      {
         return $this->inizializeHooksStackIterator();
      }
      
      if($this->getApplicationKernel()->isDebugActive())
      {
         self::writeLog('[FAILED] Hooks disabilitati ',Application_Kernel::LOG_FILE_NAME);
      }
      
      return true;
   }
 
   /**
    * Indica se gli hooks sono abilitati
    * @var Boolean
    */
   public function isEnable()
   {
      return $this->_is_enable;
   }
   
   /**
    * Imposta se gli hooks sono abilitati o meno
    * 
    * @param Boolean $enable  TRUE o FALSE
    * 
    * @var Application_Hooks
    */
   public function setEnable($enable)
   {
      $this->_is_enable = $enable;
      return $this;
   }
   
   /**
    * Restituisce lo stack degli status degli hooks
    * 
    * @return ArrayIterator
    */
   public  function getHooksStatusStack()
   {
      $this->_HOOKS_TYPE_STATUS_STACK_ITERATOR->rewind();
      return $this->_HOOKS_TYPE_STATUS_STACK_ITERATOR;
   }
   
   
   /**
    * Imposta la tipologia di hook come disabilitata
    * 
    * @param String $hookType Tipologia di hook
    * 
    * @return Application_Hooks
    */
   public function setHookTypeDisabled($hookType) 
   {
      if(is_null($hookType))
      {
         $this->_HOOKS_TYPE_STATUS_STACK_ITERATOR = new ArrayIterator();
      }
      else
      {
         $this->_HOOKS_TYPE_STATUS_STACK_ITERATOR->offsetSet($hookType,self::HOOK_STATUS_DISABLE);
      }
      
      return $this;
   }
   
   
   /**
    * Imposta la tipologia di hook come abilitata
    * 
    * @param String $hookType Tipologia di hook
    * 
    * @return Application_Hooks
    */
   public function setHookTypeEnabled($hookType) 
   {
      $this->_HOOKS_TYPE_STATUS_STACK_ITERATOR->offsetSet($hookType,self::HOOK_STATUS_ENABLE);
      return $this;
   }
   
   
   /**
    * Indica se la tipologia di hook è disabiltiata
    * 
    * @param String $hookType Tipologia di hook
    * 
    * @return Boolean
    */
   public function isHookTypeDisabled($hookType)
   {
      $hookTypeStatusBag = $this->getHooksStatusStack();
      
      while($hookTypeStatusBag->valid())
      {
         if($hookTypeStatusBag->key() == $hookType)
         {
            if($hookTypeStatusBag->current() == self::HOOK_STATUS_DISABLE)
            {
               return true;
            }
         }
         
         $hookTypeStatusBag->next();
      }
      
      return false;
   }
   
   
   /**
    * Restituisce TRUE qualora la tipologia di hook fornita sia quella definita come "subscriber", ossia che fornisce autonomamente
    * la configurazione di processamento dell'hook, tramite il metodo getSubscriberConfiguration()
    * 
    * @param String $hookType Tipologia di hook
    * 
    * @return Boolean
    */
   public function isSubscriberHookType($hookType)
   {
      return $hookType == self::HOOK_TYPE_SUBSCRIBER;
   }
   
   /**
    * Imposta la tipologia di hook da ricerca
    * 
    * @param String   $hookType              Tipologia hooks, utilizza le costanti
    * 
    * @return \Application_Hooks
    */
   public function setCurrentHookType($hookType)
   {
      if(strlen($hookType) == 0){
         return self::throwNewException(98234710470194,'Hook Type non fornito!');
      }
      
      $allHooks = self::getAllRegisteredHooksType();
      
      if(is_array($allHooks) && count($allHooks) > 0)
      {
         foreach($allHooks as $key => $value)
         {
            if( $value == $hookType)
            {
                $this->_hook_current_type = $hookType;
                return $this;
            }
         }
      }
      
      return self::throwNewException(2374923470,'HookType fornito non supportato: '.$hookType);
   }
   
   
   /**
    * Imposta i dati da passati successivamente ad ogni esecuzione degli hook.
    * Tali dati saranno poi cancellati al termine dell'esecuzione degli hook configurati per lo stack attuale
    * 
    * @param Application_HooksData $params parametri da passare
    * 
    * @return Application_Hooks
    */
   public function setHooksData(Application_HooksData $hooksData = null)
   {  
      
      if(is_null($hooksData)){
         $hooksData = new Application_HooksData();
      }
      
      if(!($hooksData instanceof Application_HooksData)){
         return self::throwNewException(39485945332039842,'Questo parametro passato agli hooks da processare non è valido: '.print_r($hooksData,true));
      }
      
      $this->_hooks_data = $hooksData;
      
      return $this;
   }
   
   /**
    * Restitusce l'attuale hook type attualmente in uso
    * 
    * @param Boolean $setFirst Indica se inizializzare eventualmetne l'hooktype con il primo della lista qualora fosse FALSE
    * 
    * @return String
    */
   public function getCurrentHookType($setFirst = false)
   {
      if($setFirst && !$this->_hook_current_type){
         $this->_hook_current_type =  $this->getFirstHookType();
      }
      
      return $this->_hook_current_type;
   }
   
   
   /**
    * Restituisce la responseData degli hooks processati, se nessun hook verrà processaro restituisce sicuramente i dati passati all'inizio.
    * 
    * @return Application_HooksResponseData
    */
   public function getResponseData()
   {
      return $this->_hooks_response_data;
   }
   
   /**
    * Restituisce la tipologia di hook successiva a quella attuale
    * 
    * @return String|boolean FALSE se terminati
    */
   public function getNextHookType()
   {
         $currentType = $this->getCurrentHookType(true);
         
         $hooksStack  = $this->getHooksOrderStack();
         
         if(is_array($hooksStack) && count($hooksStack)>0)
         {
         
            foreach($hooksStack as $index => $hookType)
            {
               if($hookType == $currentType){
                  return isset($hooksStack[$index+1]) ? $hooksStack[$index+1] : false;
               }
            }
         }
         
         return false;
   }
   
   /**
    * Restituisce la tipologia di hook precedenti a quella attuale
    * 
    * @return String|boolean FALSE se terminati
    */
   public function getPreviusHookType()
   {
         $currentType = $this->getCurrentHookType(true);
         
         $hooksStack  = $this->getHooksOrderStack();
         
         if(is_array($hooksStack) && count($hooksStack)>0)
         {
            foreach($hooksStack as $index => $hookType)
            {
               if($hookType == $currentType){
                  return isset($hooksStack[$index-1]) ? $hooksStack[$index-1] : false;
               }
            }
         }
         
         return false;
   }
   
   /**
    * Restituisce il primo hookType dello stack di attivazione
    * @return String
    */   
   public function getFirstHookType()
   {
      $hooksStack = $this->getHooksOrderStack();
      return $hooksStack[0];
   }
   
   /**
    * Restituisce l'ultimo hookType dello stack di attivazione
    * @return String
    */
   public function getLastHookType()
   {
      $hooksStack = $this->getHooksOrderStack();
      return $hooksStack[count($hooksStack)-1];
   }
   
   
   /**
    * Restituisce la lista di tutte le tipogie di hooks supportate di base
    * 
    * @return Array
    */
   public static function getAllAvailableHooksTypes()
   {
      $self      = new ReflectionClass(__CLASS__);
      
      $hookTypes = array();
      
      foreach($self->getConstants() as $name => $value)
      {
         if(strstr($name,"HOOK_TYPE")!==false)
         {
            if($value != self::HOOK_TYPE_SUBSCRIBER)
            {
                $hookTypes[] = $value;
            }
         }
      }
      
      return $hookTypes;
   }
   
   /**
    * Restituisce tutte le tipologie di hooks registrate
    * 
    * @return Array
    */
   public static function getAllRegisteredHooksType()
   {
      return static::$_HOOKS_TYPE_REGISTERED;
   }
   
   
   
   /**
    * Restituisce la lista degli stack di attivazione degli hook ordinata logicamente
    * @return Array
    */
   public function getHooksOrderStack()
   {
      return $this->_HOOK_STACK_ORDER_LIST;
   }
   
   /**
    * Restituisce la lista dello stack di tutti gli hooks
    * @return ArrayIterator
    */
   public function getHooksStackIterator()
   {
      $this->_HOOKS_STACK_ITERATOR->rewind();  
      return $this->_HOOKS_STACK_ITERATOR;
   }
      
      
   /**
    * Restituisce la lista degli hooks da elaborare per una particolare tipologia
    * 
    * @return ArrayIterator
    */
   public function getHooksStackIteratorByType($hookType)
   {
      $hooksStatckIterator = $this->getHooksStackIterator();
      
      if($hooksStatckIterator->offsetExists($hookType))
      {
         $hookStackType = $hooksStatckIterator->offsetGet($hookType);
         $hookStackType->rewind();
         
         return $hookStackType;
      }
      
      return new ArrayIterator();
   }
   
   /**
    * Verifica che vi siano hooks registrati per la tipologia indicata
    * 
    * @param String $hookType Tipologia di hook
    * 
    * @return Boolean
    */
   public function hasHooksTypeRegistered($hookType)
   {
       if($this->isHookTypeRegistered($hookType))
       {
           $hooksStackIterator = $this->getHooksStackIteratorByType($hookType);
           
           return $hooksStackIterator->count() > 0;
       }
       
       return $this->throwNewException(23429034923494049,'Questa tipologia di hook fornita non è valida');
   }
      
   /**
    * Svuota tutto lo stack di attivazione degli hooks
    * 
    * @return Boolean
    */
   public function clearHooksStackIterator()
   {
      return $this->_HOOKS_STACK_ITERATOR = new ArrayIterator();
   }
   
   /**
    * Svuota tutto lo stack di attivazione degli hooks
    * 
    * @return Boolean
    */
   public function clearHooksStackIteratorByType($hookType)
   {
      $this->_HOOKS_STACK_ITERATOR->rewind();
      
      if($this->_HOOKS_STACK_ITERATOR->offsetExists($hookType))
      {
         $this->_HOOKS_STACK_ITERATOR->offsetUnset($hookType);
         $this->_HOOKS_STACK_ITERATOR->rewind();
         return true;
      }
      
      return false;
   }
   
   /**
    * Restituisce lo stack attuale in formato string, utile per log
    * 
    * @return string
    */
   public function getHooksStackIteratorToString($hookType = null)
   {
       $hooksStackIterator = $this->getHooksStackIterator();
              
       $hooksListString    = "";

       if($hooksStackIterator->count() > 0)
       {
          foreach($hooksStackIterator as $currentHookType => $hooksStackIteratorType)
          {  
             if(is_null($hookType) || $hookType == $currentHookType)
             {
               $hooksListString.= " [$currentHookType] (".$hooksStackIteratorType->count().")\n ";

               foreach($hooksStackIteratorType as $key => $hookInfo)
               {
                  $hook       = $hookInfo["hook"];      /*@var $hook Abstract_Hooks */
                  $method     = $hookInfo["method"];
                  
                  $hooksListString.= "\t\t { name: ".$hook->getHookName()."  priority: ".$key."  method: ". $method ." ".
                                           ($hook->getHookRegisterInfo() ? "\n\t\t   registerBy: ".$hook->getHookRegisterInfo() : "") .
                                           ($hook->getHookDescription() ? "\n\t\t   description: ".$hook->getHookDescription() : "")  .
                                     " } "."\n\n";
               }
             }
          }
       }
       else
       {
          $hooksListString = " Questo hook type non presenta hooks associati ";
       }
       
       return $hooksListString;
   }

   /**
    * Registra una o piu di una nuova tipologia di hooks 
    * 
    * @param Mixed $hookType Tipologia di hooks o lista di tipologie da registrare
    * 
    * @return boolean TRUE se la registrazione viene effettuata (quindi la tipologia indicata non è presente tra quelle già registrate), FALSE altrimenti
    */
   public static function registerHookType($hookType)
   {      
      $hookTypeArray = !is_array($hookType) ? array($hookType) : $hookType;
      
      $registered = 0;
      
      foreach($hookTypeArray as $hookTypeName)
      {
         if(!self::isHookTypeRegistered($hookTypeName))
         {
            static::$_HOOKS_TYPE_REGISTERED[] = $hookTypeName;
            $registered++;
         }
      }
      
      return $registered > 0;
   }
   
   /**
    * Indica se la tipologia di hook specificata è registrata
    * 
    * @param String $hookType Tipologia di hook
    * 
    * @return boolean
    */
   public static function isHookTypeRegistered($hookType)
   {
      $hookTypeFound = false;
      
      foreach(static::$_HOOKS_TYPE_REGISTERED as $hookTypeRegistered)
      {
         if($hookTypeRegistered == $hookType)
         {
            $hookTypeFound = true;  
         }
      }
      
      return $hookTypeFound;
   }
   
   /**
    * Verifica che l'hook sia registrato
    * 
    * @param Mixed $hook Instanza di un Hook (Abstract_Hooks) / Nome classe hook
    * 
    * @return Boolean
    */
   public function isHookRegister($hook)
   {
       foreach($this->_HOOKS_STACK_ITERATOR as $hookType => $hooksList)
       {
           foreach($hooksList as $hookInfo)
           {
               $hookInstance = $hookInfo["hook"];
               
               if($hookInstance->getHookName() == $hook || $hook == $hookInstance)
               {
                   return true;
               }
           }
       }
       
       return false;
   }
   
   
   /**
    * Aggiunge un hook allo stack di attivazione
    * 
    * @param Mixed $hook                     Callable, Closure, Abstract_Hooks da registrare
    * @param Mixed $hookType                 [OPZIONALE] array / self::HOOK_TYPE_* 
    * @param Mixed $hookPriority             [OPZIONALE] array / Int
    * @param Array $costructParameters       [OPZIONALE] array parametri passati al costruttore
    * 
    * @return \Abstract_Hooks
    * 
    * @throws \Exception 
    */
   public function registerHook($hook,$hookType = null,$hookPriority = self::HOOK_PRIORITY_MIN, array $costructParameters = array())
   {
       if(empty($hookPriority))
       {
           $hookPriority = self::HOOK_PRIORITY_MIN;
       }
       
       $hook = $this->generateHookObject($hook, true, $costructParameters);
       
       if(!is_null($hookType))
       {
           $hook->setHookType($hookType);
       }
                    
       if(!is_null($hookPriority))
       {
            $hook->setHookPriority($hookPriority);
       }
       
       if(!$this->_registerHook($hook))
       {
           self::throwNewException(3945823959246820, 'Registrazione dell\'hook fallita: '.$hook);
       }
       
       return $hook;
   }
   
   /**
    * Registra gli hooks tramite un array di configurazione
    * 
    * @param array $configsData array generato da una configurazione
    * 
    * @return int nr di hooks registrati
    */
   public function registerHooksByConfigsData(array $configsData)
   {
        $hooksRegistered = 0;
                
        foreach($configsData as $hookType => $hooksRegisterList)
        {
           if(!$this->isSubscriberHookType($hookType))
           {
              $this->registerHookType($hookType);
           }

           if(!empty($hooksRegisterList) && is_array($hooksRegisterList))
           {
              foreach($hooksRegisterList as $hookInfo)
              {
                 $hookClassName   = !empty($hookInfo["name"])           ? $hookInfo["name"]             : (!empty($hookInfo["class"]) ? $hookInfo["class"] : null);
                 $hookPriority    = isset($hookInfo["priority"])        ? $hookInfo["priority"]         : self::HOOK_PRIORITY_MIN;
                 $hookMethodName  = isset($hookInfo["method"])          ? $hookInfo["method"]           : self::HOOK_DEFAULT_METHOD;
                 $hookDescription = !empty($hookInfo["description"])    ? $hookInfo["description"]      : '';
                 
                 if(!$hookClassName)
                 {
                    return self::throwNewException(82348234282323, 'Non è possibile trovare il parametro "name" o "class" per l\'hookType '.$hookType.', configurazione: '.print_r($hookInfo,true));
                 }

                 /**
                  * Sovrascrivo le informazioni dell'hook
                  */
                 $hook =  $this->generateHookObject($hookClassName);

                 if(!$hook)
                 {
                    return self::throwNewException(2834924328349250250, 'Questo hook '.$hookClassName.' non esiste in '.$package->getName());
                 }

                 $hook->setHookName($hookClassName)
                      ->setHookType(array($hookType => $hookMethodName))
                      ->setHookDescription($hookDescription)
                      ->setHookPriority(array($hookType => $hookPriority));

                 if($this->_registerHook($hook))
                 {
                    $hooksRegistered++;
                 }
              }
           }
        }
        
        return $hooksRegistered;
   }
   
   
   /**
    * Aggiunge un hook allo stack di attivazione
    *
    * @param Abstract_Hooks $hook Hook da appendere, oggetto instanza di Abstract_Hooks, callback, o stringa con il nome dell'hook da invocare
    * 
    * @return Boolean
    */
   private function _registerHook(Abstract_Hooks $hook)
   {       
      $hookName            = $hook->getHookName();
      
      if(strlen($hookName) == 0)
      {
         $hookName = $hook->getDefaultName();
         $hook->setHookName($hookName);
      }
      
      /**
       *  Verifico che l'hook "subscriber" abbia una configurazione interna valida, e la configuro
       */
      if($hook->hasHookType(self::HOOK_TYPE_SUBSCRIBER))
      {  
         if(!is_array($hook->getSubscriberConfiguration()) || count($hook->getSubscriberConfiguration()) == 0)
         {
            return self::throwNewException(2893742893462894369,'Questo hook "'.$hook->getHookName().'" è stato registrato come '.self::HOOK_TYPE_SUBSCRIBER.' ma non ha una mappatura valida ');
         }
         
         /**
          * Registro la configurazione di sottoscrizione di questo hook qualora fosse fornita con il metodo $hook::getSubscriberConfiguration()
          * Ogni tipologia di hook indicata che non è conosciuta verrà registrata
          */
          $hook->setHookType($hook->getSubscriberConfiguration());
          $hook->setHookPriority($hook->getSubscriberConfiguration());
      }
      
      /**
       * Controllo l'integrità dell'hook
       */
      if(!$this->checkHookIntegrity($hook))
      {
         return false;
      }
      
      $hookPriorityArray   = $hook->getHookPriority();
      $hookTypeArray       = $hook->getHookType();

      foreach($hookTypeArray as $hookType => $hookInfo)
      {
         if($hook->isRegistrable())
         {
           /**
            * Se è un array sicuramente sto indicando una tipologia di hook con la lista di callback (nomi di metodi)
            */
           if(is_array($hookInfo))
           {
              foreach($hookInfo as $key => $hookSubscriberInfo)
              { 
                 foreach($hookSubscriberInfo as $methodName => $hookPriority)
                 {
                    if($hookRegisterInfo = $this->_appendSortHook($hook, $hookType, $methodName, $hookPriority))
                    {
                       if($this->getApplicationKernel()->isDebugActive())
                       {
                          self::writeLog('[REGISTERED] ['.$hookType.'] ['.$hookRegisterInfo["method"].'] '.$hook->getHookName(),'hooks');  
                       }
                    }
                 }
              }
           }
           else
           {
              $hookPriority = $hookPriorityArray[$hookType];
              if($hookRegisterInfo = $this->_appendSortHook($hook, $hookType, $hookInfo, $hookPriority))
              {
                 if($this->getApplicationKernel()->isDebugActive())
                 {
                    self::writeLog('[REGISTERED] ['.$hookType.'] ['.$hookRegisterInfo["method"].'] '.$hook->getHookName(),'hooks');  
                 }
              }
           }
        }
      }

      return true;      
   }
   
   /**
    * Appende l'hook allo stack iterator di registrazione degli hooks, ordinando in base alla priorità indicata
    * 
    * @param Abstract_Hooks    $hook            Instanza oggetto Hook
    * @param String            $hookType        Tipologid di hook
    * @param String            $hookMethodName  [OPZIONALE] Metodo da invocare su questo hook, default self::HOOK_DEFAULT_METHOD
    * @param Int               $hookPriority    [OPZIONALE] Priorità di esecuzione (ordinamento dello stack), defeault self::HOOK_PRIORITY_MIN
    * 
    * @return Array     Array infomazioni di registrazione dell'hook
    */
   private function _appendSortHook(Abstract_Hooks $hook,$hookType,$hookMethodName = self::HOOK_DEFAULT_METHOD,$hookPriority = self::HOOK_PRIORITY_MIN)
   {  
      /**
       * Se non esiste l'hookType nello stack di attivazione lo creo
       */
      if(!$this->_HOOKS_STACK_ITERATOR->offsetExists($hookType))
      {
         $hookStackForType = new ArrayIterator();
         $this->_HOOKS_STACK_ITERATOR->offsetSet($hookType,$hookStackForType);
      }
      
      /**
       * Se questo hook non specifica una priority è perchè è stato sottoscritto senza prioritò, indicando il metodo, quindi verrà appeso alla lista già esistente.
       */
      if(is_string($hookPriority))
      {
         $hookMethodName = $hookPriority;
         $hookPriority   = self::HOOK_PRIORITY_MIN;
      }
      
      $hookStackForType = $this->getHooksStackIteratorByType($hookType);
      
      while($hookStackForType->offsetExists($hookPriority))
      {
         ++$hookPriority;
      }
      
      $returnInfo = array(
                 "hook"       => $hook,
                 "method"     => $hookMethodName,
                 "priority"   => $hookPriority
      );
      
      $hookStackForType->offsetSet($hookPriority, $returnInfo);
      
      $hookStackForType->uksort(function($a,$b){return $a < $b; });  //Ordino decrescente 

      $this->_HOOKS_STACK_ITERATOR->offsetSet($hookType,$hookStackForType);
      
      return $returnInfo;
   }   
   
   
   /**
    * Rimuove un hook specifico tramite nome dallo stack
    * 
    * @param String $hookName Nome Hook
    * @param String $hookType Tipologia di hook, se NULL la ricerca sarà effettuata su tutto lo stack
    * 
    * @return Boolean
    */
   public function removeHookByName($hookName,$hookType = null)
   {
      $hooksStatckIterator = $this->getHooksStackIterator();
      
      if($hooksStatckIterator->count() > 0)
      {
         foreach($hooksStatckIterator as $currentHookType => $hooksStatckIteratorType)
         {
            if($hooksStatckIteratorType->count() > 0 )
            {
               foreach($hooksStatckIteratorType as $currentHookKey => $hookInfo)
               {
                  $hook = $hookInfo["hook"]; /*@var $hook Abstract_Hooks */
                  
                  if($hook->getHookName() == $hookName)
                  {
                     if(is_null($hookType) || $hookType == $currentHookType)
                     {
                        $hooksStatckIteratorType->offsetUnset($currentHookKey);               
                        $hooksStatckIteratorType->uksort(function($a,$b){ return $a<$b; });              //Ordino decrescente
                        $this->_HOOKS_STACK_ITERATOR->offsetSet($currentHookType,$hooksStatckIteratorType);
                     }
                  }
               }
            }
         }
      }
      
      return false;
   }
   

   /**
    * Restituisce un hook specifico tramite nome dallo stack
    * 
    * @param String $hookName Nome hook
    * @param String $hookType Tipologia di hook, se NULL la ricerca sarà effettuata su tutto lo stack
    * 
    * @return Abstract_Hooks
    * 
    * @throws \Exception
    */
   public function getHook($hookName,$hookType = null)
   {
      
      if(strlen($hookName) == 0)
      {
         return self::throwNewException(98234710470194,'Nome Hook non fornito!');
      }
      
      $hooksStatckIterator = $this->getHooksStackIterator();
      
      if($hooksStatckIterator->count() > 0)
      {
         foreach($hooksStatckIterator as $currentHookType => $hooksStatckIteratorType)
         {                 
            if($hooksStatckIteratorType->count() > 0)
            {
               foreach($hooksStatckIteratorType as $key => $hookInfo)
               {                        
                  $hook = $hookInfo["hook"]; /*@var $hook Abstract_Hooks */
                  
                  if($hook->getHookName() == $hookName)
                  {                     
                     if(is_null($hookType) || ($currentHookType == $hookType))
                     {
                        return $hook;
                     }
                  }
               }
            }
         }
      }

      return $this->throwNewException(3967047293472350, 'Impossibile trovare l\'Hook richiesto: "'.$hookName.'" per la tipologia indicata "'.$hookType."'");
   }
  
   
   /**
    * Verifica che vi sia un hook con il nome indicato e per la tipologia indicata nello stack gestito
    * 
    * @param String $hookName Nome hook
    * @param String $hookType Tipologia di hook, se NULL la ricerca sarà effettuata su tutto lo stack
    * 
    * @return Boolean
    */
   public function hasHook($hookName,$hookType = null)
   {
       try
       {
           if($this->getHook($hookName,$hookType))
           {
               return true;
           }
       } 
       catch (Exception $e) 
       {
           return false;
       }
       
       return false;
   }
   
   
   /**
    * Processa tutti gli hooks presenti nella lista attuale relativi all'attuale hookType
    * 
    * @param String                  $hookType              [OPZIONALE] Tipologia hook, se NULL verrà ricercato l'hook nella tipologia attualmente in uso,altrimenti ricerca in quella specificata
    * @param Mixed                   $hookData              [OPZIONALE] Dati passati ad ogni hooks eseguito
    * @param \Application_HooksData  $applicationHookData   [OPZIONALE] Oggetto dei dati passati agli hooks, verrà generato automaticamente, default NULL
    * 
    * @return Application_Hooks
    */
   public function processAll($hookType = null,$hookData = null,\Application_HooksData $applicationHookData = null,$level = self::HOOK_SUBLEVEL)
   {      
      $currentHookType            = is_null($hookType) ? $this->getCurrentHookType() : $hookType;
      
      /**
       * Se gli hooks non sono abilitati restituisco direttamente i dati passati
       */
      if(!$this->isEnable())
      {
          if($this->getKernelDebugActive())
          {
             self::writeLog('[ERROR] Hooks disabilitati','hooks');
          }

          return $this;
      }
            
      if(is_null($applicationHookData))
      {
          $applicationHookData = new Application_HooksData();
      }
      
      if($hookData)
      {
         $applicationHookData->setData($hookData);
      }
      
      
      $this->_hooks_data = $applicationHookData;
      $this->_hooks_data->setPropagationStop(false);
      $this->_hooks_response_data = $this->_hooks_data;
      
      /**
       * Questi hooks sono disabilitati?
       */
      if($this->isHookTypeDisabled($hookType))
      {
         return $this; 
      }
      
      $hooksTypeStackIterator = $this->getHooksStackIteratorByType($currentHookType);

      if($this->_processing) //Sotto hooks, scatenati quando un hooks chiama delle funzionalità che a loro volta dipendono dagli hooks
      {
          $applicationHooks = clone $this;
          $applicationHooks->setProperty('_processing', false);
          
          return $applicationHooks->processAll($currentHookType,$hookData,$applicationHookData,$level+1);
      }
      else
      {
            $this->setCurrentHookType($currentHookType);

            $this->_processing = true;
                        
            if($hooksTypeStackIterator->count() > 0)
            {

                 foreach($hooksTypeStackIterator as $key => $hookInfo)
                 {
                    $hook    = $hookInfo["hook"];     /*@var $hook Abstract_Hooks */
                    $method  = $hookInfo["method"];

                    $hook = $this->_processSingleHook($hook,$method);

                 }  

            }

            $this->_processing = false;
          
      }
      
      return $this;
   }
   
  
   /**
    * Processa una hook mediante nome e tipologia (opzionale)
    * 
    * @param String $hookName Nome hook
    * @param String $hookType [OPZIONALE] Tipologia hook, se NULL verrà ricercato l'hook nella tipologia attualmente in uso,altrimenti ricerca in quella specificata
    * 
    * @return boolean
    */
   public function processHookByName($hookName,$hookType = null)
   {
      $hookType = is_null($hookType) ? $this->getCurrentHookType() : $hookType;
      $hook     = $this->getHook($hookName, $hookType);
      
      if($hook)
      {
         return $this->_processSingleHook($hook);
      }
      
      return false;
   }
   
   
   
   /**
    * Genera un hookObject
    * 
    * @see Trait_Singleton
    * 
    * @param callable  $hookCallable          Nome della classe da invocare, Closure function, o callable array. Se è già un hook valido, lo restituisce direttamente.
    * @param Boolean   $useSingletonInstance  [OPZIONALE] Indica se deve utilizzare l'instanza singleton dell'hook qualora fosse già stato invocato, altrimenti genera un nuov hook diverso, default TRUE
    * @param Array     $costructParameters    [OPZIONALE] array parametri passati al costruttore
    * 
    * @return Abstract_Hooks
    */
   public function generateHookObject($hookCallable,$useSingletonInstance = true, array $costructParameters = array())
   {        
      $hook        = null;/*@var $hook Abstract_Hooks */
      $hookClosure = false;
      
      /**
       * L'hook che sto registrando è un'instanza di un Abstract_Hooks valido
       */
      if($hookCallable instanceof \Abstract_Hooks)  //Instanza di un \Abstract_Hooks
      {
          $hook = $hookCallable;
      }
      
       /**
       * L'hook che sto registrando è una stringa di un Abstract_Hooks valido
       */
      else if($this->checkHookClassName($hookCallable)) /*@var $hookCallable \Abstract_Hooks*/  //Nome di una instanza di \Abstract_Hooks
      {
         $hook  = $useSingletonInstance ? $hookCallable::getInstance() : new $hookCallable();
      }
      
      /**
       * L'hook che sto registrando è una callable array
       */
      else if(is_array($hookCallable)) //Callable array
      {
         if(empty($hookCallable))
         {
             return self::throwNewException(2093809235246993495, 'Non è possibile registrare l\'hook perchè la callable indicata è vuota!');
         }
         
         if(count($hookCallable) == 2)  //Callable 'oggetto/nome classe','metodo'
         {
            $object = $hookCallable[0];
            $method = $hookCallable[1];  
            $objectClassName = (is_string($object) ? $object : get_class($object));
            
            if(!is_callable($hookCallable) && !method_exists($object,$method))
            {
                return self::throwNewException(34603972283482934, 'Non è possibile registrare l\'hook perchè l\'oggetto "'.$objectClassName.'" non ha il metodo '.$method);
            }
            
            if(is_string($object)) //Parametro 1 della callable è una stringa, verifico che sia di una classe valida
            {
                $reflectionClass = new ReflectionClass($object);
                
                $parametersRequiredNumber = $reflectionClass->getConstructor()->getNumberOfRequiredParameters();
                $parametersRequired       = $reflectionClass->getConstructor()->getParameters();
                
                if($parametersRequiredNumber > 0)
                {
                    $requiredParameterString = array();
                    foreach($parametersRequired as $reflectionParameter)/*@var $reflectionParameter \ReflectionParameter*/
                    {
                        $requiredParameterString[]= "[{$reflectionParameter->getClass()->getName()}] {$reflectionParameter->getName()}";
                    }
                    $requiredParameterString = implode(",", $requiredParameterString);
                    return self::throwNewException(2382379834693860346, 'Non è possibile registrare l\'hook perchè il costruttore della classe  "'.$objectClassName.'" richiede '.$parametersRequiredNumber.' parametro/i obbligatorio/i: '.$requiredParameterString);
                }
            }
            
            $object = is_object($object) ? $object : (method_exists($object, 'getInstanceWithoutConstructor') ? $object::getInstanceWithoutConstructor() : new $object($costructParameters));
            $hookCallable = array($object,$method);
         }
         else if(count($hookCallable) == 1)
         {
             if(is_string($hookCallable[0]) && !function_exists($hookCallable[0]))
             {
                 return self::throwNewException(9328965329834725, 'Non è possibile registrare l\'hook perchè la function '.$hookCallable[0].' non esiste ');
             }             
         }
        
         else if(!is_callable($hookCallable))
         {
             return self::throwNewException(2093499293488234, 'Non è possibile registrare l\'hook poiché la callable indicata non è valida');
         }
                  
         $hookClosure  = function(\Application_HooksData $hookData,$methodName,\Abstract_Hooks $hook) use($hookCallable)
         {
             return call_user_func_array($hookCallable,array($hookData,$methodName,$hook));
         };

      }
      
      /**
       * L'hook che sto registrando è una callable, magari una function?
       */
      else if(is_callable($hookCallable))
      {          
         $hookClosure = function(Application_HooksData $hookData,$methodName,\Abstract_Hooks $hook) use($hookCallable)
         {
            return call_user_func_array($hookCallable,array($hookData,$methodName,$hook));
         };

      }
      
      /**
       * L'hook che sto registrando è una service string, la wrappo in una closure ad hoc
       */
      else if(strstr($hookCallable,"@")!==false)
      {
         $kernel = $this->getApplicationKernel();
         $hookClosure = function() use($kernel,$hookCallable)
         {
             return $kernel->getApplicationServices()->callServiceString($hookCallable);
         };
         
      }
      
      /**
       * Genero un hook Closure
       */
      if($hookClosure instanceof \Closure)
      {
         $hookClosureName = self::HOOK_CLOSURE_OBJECT_NAME;
//         $hook            = $useSingletonInstance ? $hookClosureName::getInstance() : new $hookClosureName();  /*@var $hook \Basic_HookClosure*/
         $hook            =  new $hookClosureName($costructParameters);  /*@var $hook \Basic_HookClosure*/
         
         $hook->setHookClosure($hookClosure)
              ->setHookName('Hook_generated_'.uniqid());
      }
      
      /**
       * Registro le informazioni di registrazione dell'hook
       */
      if($hook && ($hook instanceof \Abstract_Hooks))
      { 
            $debugBacktrace   = debug_backtrace();
            $debugLevel       = 2;
            
//            while(!isset($debugBacktrace[$debugLevel]['class']) || (isset($debugBacktrace[$debugLevel]['class']) && $debugBacktrace[$debugLevel]['class'] == __CLASS__ || in_array($debugBacktrace[$debugLevel]['class'],array('Application_Kernel','Application_ServicesInstance','ReflectionClass','Application_Services'))))
//            {
//               $debugLevel++;
//            }
            
            $caller           = $debugBacktrace[$debugLevel];
            $hookRegisterInfo = '';            
            
            if(!empty($caller))
            {
                  if(isset($caller['class']) && !empty($caller['function']))
                  {
                     $hookRegisterInfo = $caller['class'].'::'.$caller['function'].'()';
                  }
                  else if(isset($caller['object']) && !empty($caller['function']))
                  {
                     $hookRegisterInfo = $caller['object'].'::'.$caller['function'].'()';
                  }

                  if(isset($caller['file']))
                  {
                     $hookRegisterInfo.=' file '.$caller['file'].' on line '.$caller['line'];
                  }
            }
                        
            $hook->setHookRegisterInfo($hookRegisterInfo); 
      }
        
      if($hook)
      {
         if(strlen($hook->getHookName()) == 0)
         {
            $hook->setHookName($hook->getDefaultName());
         }
         
         if($hook && ($hook instanceof \Abstract_Hooks))
         {                
            return $hook;
         }
         
      }
      
      return self::throwNewException(1982738964290298376,'Impossibile generare un  hook! Questo valore: '.var_export($hookCallable,true).' Non è ne una Closure function ne un nome di una classe di un hook valida! Se si vuole utilizzare un oggetto, questo dovrà estendere la classe "'.self::HOOK_ABSTRACT_CLASS_NAME.'"');

   }
  
   
   /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ CONTROLLO VALIDITA' DATI HOOK ~~~~~~~~~~~~~~~~~~~~~~~~~~  */
   /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~   */
   
   
   /**
    * Controlla la tipologia e la priorità dell'hook specifcato
    * 
    * @param Abstract_Hooks $hook Hook da controllare
    * 
    * @return Boolean
    * 
    * @throws \Exception
    */
   public function checkHookIntegrity(Abstract_Hooks $hook)
   {
      $hookPriorityArray = $hook->getHookPriority();
      $hookTypeArray     = $hook->getHookType();
      
      /**
       * Tutti gli hooks che modificano il comportamento della sessione devono implementare l'interfaccia "Interface_SessionHandler"
       */
      if($hook->hasHookType(self::HOOK_TYPE_SESSION_REGISTER))
      {
         if(!($hook instanceof Interface_SessionHandler))
         {
            return self::throwNewException(99891293791274, 'Questo hook "'.$hook.'" deve implementare l\'interfaccia "Interface_SessionHandler" poichè ascolta la tipologia di hook "'.self::HOOK_TYPE_SESSION_REGISTER.'" ');
         }
      }
       
      /**
       * Controllo la configurazione della tipologia di hook
       */
      if(!$this->_checkHookType($hook))
      {
          return false;
      }

      /**
       * Controllo la configurazione delle priorità per i metodi
       */
      if(!$this->_checkHookPriority($hook))
      {
          return false;
      }
      
      return true;
   }
   
   /**
    * Controlla la configurazione della tipologia di hook e del relativo metodo da invocare
    * 
    * @param \Abstract_Hooks $hook Hook
    * 
    * @return boolean
    */
   protected function _checkHookType(\Abstract_Hooks $hook)
   {
      $hookTypeArray = $hook->getHookType();
           
      /**
       * Hook senza nessuna priorità configurata
       */
      if(!is_array($hookTypeArray) || count($hookTypeArray) == 0)
      {
         return self::throwNewException(91284319029029384,'Questo hook "'.$hook.'" non ha configurato nessuna tipologia');
      }
      
      /**
       * Controllo la mappatura delle tipologie di hook
       */
      foreach($hookTypeArray as $hookType => $hookInfo)
      {    
         if(is_array($hookInfo)) //L'hook accetta piu metodi per la stessa tipologia
         {
             foreach($hookInfo as $key => $hookSubscriberInfo)
             { 
                foreach($hookSubscriberInfo as $methodName => $hookPriority)
                {
                   
                   if(is_numeric($methodName))
                   {
                       $methodName   = $hookPriority;
                       $hookPriority = self::HOOK_PRIORITY_MIN;
                   }
                    
                   if(!$this->_checkHookMethod($hook,$hookType,$methodName))
                   {
                     return false;
                   }
                }
             }
          } 
          
          else if(!$this->_checkHookMethod($hook, $hookType, $hookInfo))    //Unico metodo con priorità
          {
             return false;
          }
      }
      
      return true;
   }
   
   /**
    * Controlla l'integrità dell'hook nel dettaglio, andando ad analizzare  per l'hook la tipologia e la correttezza del metodo (usa Reflection)
    * 
    * @param \Abstract_Hooks $hook        Hook
    * @param String          $hookType    Tipologia di hook
    * @param String          $methodName  Nome del metodo
    * 
    * @return boolean
    */
   protected function _checkHookMethod(\Abstract_Hooks $hook,$hookType,$methodName)
   {
         if(!is_string($methodName))
         {
            return self::throwNewException(3284092347356,' Questo hook "'.$hook.'" non è registrabile per la tipologia "'.$hookType.'" poiché indica un metodo non valido, il nome del metodo deve essere un metodo valido ');
         }

         if(strlen($methodName) == 0)
         {
            return self::throwNewException(8926342406925,' Questo hook "'.$hook.'" non ha un metodo specificato da processare per la tipologia: '.$hookType);
         }

         /**
          * Analizzo nel dettaglio i metodi indicati nella configurazione degli hook, andando anche a verificare la tipologia delle varibili passate
          */
         if(strlen($methodName) > 0 && !($hook instanceof Basic_HookClosure))
         {
            if(!method_exists($hook, $methodName))
            {
               return self::throwNewException(20939029482777,'Questo hook "'.$hook.'"  non ha il metodo: "'.$methodName.'" indicato nella configurazione');
            }

            $reflectionHooksAbstract          = new ReflectionClass('Abstract_Hooks');
            $reflectionHooks                  = new ReflectionClass($hook);
            
            $hookMethodParameterDefaultMethod = $reflectionHooksAbstract->getMethod(self::HOOK_DEFAULT_METHOD)->getParameters();
            $hookMethodParameter              = $reflectionHooks->getMethod($methodName)->getParameters();
            
            /**
             * L'hook ha un nr di parametri minori rispetto a quelli di default
             */
            if(count($hookMethodParameterDefaultMethod) > count($hookMethodParameter))
            {
               return self::throwNewException(9458309458219814, 'Non è possibile registrare questo hook "'.$hook.'" per l\'hookType "'.$hookType.'" poichè il metodo specificato "'.$methodName.'" deve implementare i parametri del metodo '.self::HOOK_DEFAULT_METHOD);
            }
            
            /**
             * Analizzo i parametri del metodo del nome dell'hook indicato nella configurazione
             */
            if(count($hookMethodParameterDefaultMethod) > 0 )
            {
               foreach($hookMethodParameterDefaultMethod as $key => $reflectionParameter) /*@var $reflectionParameter ReflectionParameter*/
               {
                  if($hookMethodParameter[$key]->getName() != $reflectionParameter->getName() || $reflectionParameter->getClass()->getName() != $hookMethodParameter[$key]->getClass()->getName())
                  {
                     return self::throwNewException(9458309458219814, 'Non è possibile registrare questo hook "'.$hook.'" per l\'hookType "'.$hookType.'" poichè il metodo specificato "'.$methodName.'" ha il parametro nr '.($reflectionParameter->getPosition()+1).' che dovrebbe chiamarsi '.$reflectionParameter->getName().', type hint: '.$reflectionParameter->getClass()->getName());
                  }
               }
            }    
         }

         /**
          * Controllo che l'hook sia valido e che il nome sia univoco
          */
         if(defined("APPLICATION_HOOKS_NAME_UNIQUE") && APPLICATION_HOOKS_NAME_UNIQUE && $this->hasHook($hook->getHookName(),$hookType))
         {
//            return self::throwNewException(9893842939877774,'Attenzione! Esiste già un hook chiamato: '.$hook.' per la tipologia "'.$hookType.'"');
         }
         else if($this->isHookTypeDisabled($hookType))
         {
            return self::throwNewException(9823409283408384834,'Attenzione! Questa tipologia di hook risultata disabilitata: '.$hookType);
         }
         
         return true;
   }
   
   /**
    * Controlla la priorità dell'hook
    * 
    * @param \Abstract_Hooks $hook Hook
    * 
    * @return boolean
    * 
    * @throws \Exception Lancia un eccezione qualora si verificasse un errore logico di validazione dell'hook
    */
   protected function _checkHookPriority(\Abstract_Hooks $hook)
   {
       
      $hookPriorityArray = $hook->getHookPriority();
      
      /**
       * Hook senza nessuna priorità configurata
       */
      if(!is_array($hookPriorityArray) || count($hookPriorityArray) == 0)
      {
         return self::throwNewException(894568236582305,'Questo hook "'.$hook.'" non è registrato correttamente, verificare che il metodo initMe() sia stato invocato correttamente ');
      }      

      /**
       * Controllo la mappatura delle priorità
       */
      foreach($hookPriorityArray as $hookType => $priority)
      {
           if(!$hook->hasHookType($hookType)) //Non è configurata la priorità per questa tipologia di hook
           {
              return self::throwNewException(394982398295394,'Questo hook "'.$hook.'" non ha configurato questa tipologia di hook "'.$hookType.'" ma ha configurato una priorità per la tipologia di hook: '.$hookType.' => '.$priority );
           }
           else if(is_array($priority)) //Priority di un hook che processa piu metodi per una tipologia
           {
               foreach($priority as $priorityArray)
               {
                    if(!is_array($priorityArray))
                    {
                       return self::throwNewException(8148961298461284,'Questo hook "'.$hook.'" presenta una configurazione per la priorità non valida, si deve definire un array di array con la priorità per ogni metodo definito per la tipologia "'.$hookType.'" '); 
                    }
                    else if(end($priorityArray) < self::HOOK_PRIORITY_MIN || end($priorityArray) > self::HOOK_PRIORITY_MAX)
                    {
                       return self::throwNewException(8148961298461284,'Priorità specificata per l\'hook "'.$hook.'" non valida, hookType: '.$hookType.' , priorità: '.print_r($priorityArray,true));
                    }
               }
           }
           else if($priority < self::HOOK_PRIORITY_MIN || $priority > self::HOOK_PRIORITY_MAX)
           {
              return self::throwNewException(8148961298461284,'Priorità specificata per l\'hook "'.$hook.'" non valida, hookType: '.$hookType.' , priorità: '.print_r($priority,true));
           }
      }
     
      return true;  
   }
   
   /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~  */
   /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~  */
   
   
   /**
    * Inizializza la lista degli hooks disponibili nel path di default configurato
    * 
    * @return Int nr di hooks registrati totali
    */
   private function inizializeHooksStackIterator()
   {     
      if(is_null($this->_HOOKS_STACK_ITERATOR))
      {
         $this->_HOOKS_STACK_ITERATOR  = new ArrayIterator();
      }
      
      if(is_null($this->_HOOKS_TYPE_STATUS_STACK_ITERATOR))
      {
         $this->_HOOKS_TYPE_STATUS_STACK_ITERATOR  = new ArrayIterator();
      }
      
   
      $hooksPaths  = $this->getHooksDirectoryPathsDefault();
      
      if($hooksPaths)
      {
         foreach($hooksPaths as $hooksDirectory)
         {
            $hooksRegistered = $this->_registerHooksInDirectory($hooksDirectory);
         }
      }
      
      return $hooksRegistered;
   }
   
   /**
    * Registra tutti gli hooks che sono attivi nel package specificato
    * 
    * @param Abstract_Package $package Instanza package
    * 
    * @return int numero di hooks registrati
    */
   public function registerHooksForPackage(\Abstract_Package $package)
   {
      if(!$this->_is_enable)
      {
         return 0;
      }
      
      $packageName               = $package->getName();
      $hooksDirectory            = $this->getHooksDirectoryPathByPackage($packageName);
      $hooksRegistered           = 0;
      
      if($package->getHooksAutoregister() || (defined("APPLICATION_HOOKS_REGISTER_WITHOUT_FILE") && APPLICATION_HOOKS_REGISTER_WITHOUT_FILE))  //Gli hooks si registrano automaticamente, tramite le informazioni nel proprio costruttore
      {
         $hooksRegistered           = $this->_registerHooksInDirectory($hooksDirectory);
      }
      else     //Registro gli hooks tramite file di configurazione
      {
         $hooksConfigsFilePath = $this->getApplicationConfigs()->getConfigsFilePathForPackage(self::HOOK_CONFIGS_FILENAME, $packageName);
                 
         if(!$this->getApplicationConfigs()->isConfigsExistsForPackage(self::HOOK_CONFIGS_FILENAME,$packageName))
         {
            return 0;   //Nessun hook presente e nessuna configurazione presente
         }
         
         $configsData = $this->getApplicationConfigs()->getConfigsFromCacheForPackage(self::HOOK_CONFIGS_FILENAME, $packageName,false);
        
         if(!$configsData)
         {
            $configsData = $this->getApplicationConfigs()->getParseConfigsForPackage(self::HOOK_CONFIGS_FILENAME, $packageName,$package->getConfigsFileExtension());
         }
         
         if(is_array($configsData) && count($configsData) > 0)
         {
             
            $this->getApplicationAutoload()->addAutoloadPath('Hooks',array( 
                            "path"      => $package->getHooksPath() ,
                            "extension" => self::HOOK_FILE_EXTENSION 
            ))->addAutoloadPath('',array( 
                            "path"      => $package->getHooksPath() ,
                            "extension" => self::HOOK_FILE_EXTENSION 
            ));
            
            $hooksRegistered = $this->registerHooksByConfigsData($configsData);
         }         
      }
      
      return $hooksRegistered;
   }
   
   /**
    * Registra tutti gli hooks una directory specifica
    * 
    * @param String $directory Path directory assoluto
    * 
    * @return int nr di hooks registrati
    */
   private function _registerHooksInDirectory($directory)
   {
      $allHooks   = $this->getHooksInDirectory($directory);
            
      $hooksRegistered = 0;
      
      if($allHooks !== false)
      {         
         foreach($allHooks as $hookFile)
         {
            $hookClassName = $this->getHookClassName($hookFile);
            $hook          = $this->generateHookObject($hookClassName);
                    
            if($this->_registerHook($hook))
            {
               $hooksRegistered++;
            }
         }
      }
      
      return $hooksRegistered;
   }
   
   /**
    * Processa il singolo hook specifico
    * <b>Questo metodo controlla anche che l'hook sia abilitato e che non sia stato invocato un propagationStop()</b>
    * 
    * @param Abstract_Hooks $hook         Hook da processare
    * @param String         $methodName   [OPZIONALE] Nome metodo da invocare, default self::HOOK_DEFAULT_METHOD
    * 
    * @return Boolean
    */
   private function _processSingleHook(Abstract_Hooks $hook,$methodName = self::HOOK_DEFAULT_METHOD,  \Application_HooksData $hookData = null)
   { 
      $hookName  = $hook->getHookName();
      
      $this->_current_hook_type_processing = $this->getCurrentHookType();
            
      /**
       * Clono i dati gestiti passati all' hooks così che nel caso vengano scatenati sub-hooks questi non siano manipolati
       * in maniera inaspettata dagli altri sutto hooks.
       */
      $hookData = clone $this->_hooks_data;
      
      /**
       * Ricerco il metodo da invocare per questo hook
       */
      $hookTypeArray = $hook->getHookType();
       
      /**
       * Verifico se posso processare questo hook
       */
      if(   $hookData->getPropagationStop() ||  (
              $this->isHookTypeDisabled($this->_current_hook_type_processing) || 
             !$hook->isEnable($hookData)                    || 
             ($hook->isProcessed($this->getCurrentHookType(),$methodName)  && !$hook->isProcessableMultipleTimes())
        ))
      {  
         return $hook;
      }
      
            
      $hook->$methodName($hookData);    //HookData viene modificato poichè è passato per reference
      
      $this->_hooks_response_data = $this->_hooks_data = $hookData;

      if(!$this->_hooks_data)
      {
         return self::throwNewException(92384902735017990,'Attenzione! Questo hook "'.$hookName.'" per il metodo "'.$methodName.'()" restituisce una response invalida. Ogni hook deve restituire un oggetto instanza di Application_HooksData() ');
      }
      
      $hook->setProcessed($this->getCurrentHookType(),$methodName,true);      
      
      if($this->_hooks_response_data->getPropagationStop())
      {
          $this->setHookTypeDisabled($this->getCurrentHookType());
      }
      else
      {
          $this->setHookTypeEnabled($this->getCurrentHookType());
      }
            
      if($this->getApplicationKernel()->isDebugActive())
      {
         self::writeLog('[PROCESSED] ['.$this->getCurrentHookType().'] ['.$methodName.'] '.$hook->getHookName(),'hooks');
      }
      
      $this->_current_hook_type_processing = null;
      return $hook;
   }
   
   /**
    * Verifica  che l'hook esista provando prima con l'autoload, altrimenti verifica che il file esista fisicamente e che vi sia definita una classe adatta
    *  
    * @param String $hookFile Nome classe
    * 
    * @return Boolean
    */
   private function checkHookClassName($hookClassName)
   {  
      if(!is_string($hookClassName) || strlen($hookClassName) == 0)
      {
         return false;
      }
      
      try
      {
         if(class_exists($hookClassName) && is_subclass_of($hookClassName, self::HOOK_ABSTRACT_CLASS_NAME))
         {
            return true;
         }
      }
      catch(\Exception $e)
      { 
          return false;
      }
      
      return false;
   }
   
   /**
    * Restituisce il nome della classe dell'hook a partire dal percorso assoluto del file
    * 
    * @param String $hookFile Path assoluto file hook
    * 
    * @return String nome classe
    *
    */
   private function getHookClassName($hookFile)
   {
      $pattern = "/(Hooks[A-z_]+)\.".str_replace('.','\.',self::HOOK_FILE_EXTENSION)."/";
      
      if(preg_match($pattern,$hookFile,$matches))
      {
         if($matches){
            return $matches[1];
         }
      }
      
      return self::throwNewException(914707519037410947,'Attenzione! Questo percorso contiene un nome hook invalido: '.$hookFile);
   }
   
   /**
    * Restituisce la directory degli hooks attualmente puntata tramite il package specificato
    * <b>Viene controllata l'esistenza della directory generate, nel caso non esista restituisce FALSE</b>
    * 
    * @param String $package package
    * 
    * @return String or FALSE se directory non esiste
    */
   private static function getHooksDirectoryPathByPackage($package)
   {
      $hooksDirectoryPackage = ROOT_PATH .'/'. APPLICATION_TEMPLATING_PACKAGE_DIRECTORY_NAME . '/' . $package . '/hooks';
      return file_exists($hooksDirectoryPackage)!==false ? $hooksDirectoryPackage : false;
   }
   
   
   /**
    * Restituisce il path alla directory in cui torvare gli hook di default
    * <b>Viene controllata l'esistenza della directory generate, nel caso non esista restituisce FALSE</b>
    * 
    * @return Array  array con la lista dei paths, o FALSE
    */
   private static function getHooksDirectoryPathsDefault()
   {
      $hooksPaths = self::getApplicationConfigs()->getConfigsValue("APPLICATION_HOOKS_PATHS");
      
      if(!is_array($hooksPaths))
      {
          $hooksPaths = array($hooksPaths);
      }
      
      return $hooksPaths;
   }
   
   /**
    * Restituisce la lista dei file hooks presenti all'interno della directory specificata
    * <b>Viene controllata l'esistenza della directory, nel caso non esista restituisce FALSE</b>
    * 
    * @param String   $directory          Path directory
    * @param Boolean  $registerAutoload   [OPZIONALE] Indica se registrare anche l'autoload, default TRUE
    * 
    * @return Array o FALSE
    */
   private function getHooksInDirectory($directory, $registerAutoload = true)
   {
      
      if(!file_exists($directory))
      {
         return false;
      }
      
      $allHooks =  glob($directory . DIRECTORY_SEPARATOR . '*' . self::HOOK_FILE_EXTENSION);

      if($registerAutoload)
      {
         $this->getApplicationAutoload()->addAutoloadPath('Hooks',Array( 
                            "path"      => $directory ,
                            "extension" => self::HOOK_FILE_EXTENSION 
         ));         
      }
      
      if(is_array($allHooks) && count($allHooks) > 0)
      {
         return $allHooks;
      }
      
      return false;
   }
}
