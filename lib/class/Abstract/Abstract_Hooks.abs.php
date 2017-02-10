<?php

/**
 * Classe astratta da estendere per creare un hook valido
 */
abstract class Abstract_Hooks implements Interface_Hooks
{
   
   use Trait_ObjectUtilities,Trait_Singleton;
   
   /**
    * Nome di questo hook
    * 
    * @var String
    */
   private $_hook_name = "";
   
   /**
    * Descrizione di questo hook
    * 
    * @var String
    */
   private $_hook_description = "";
   
   /**
    * Informazioni da dove è stato registrato l'hook
    * 
    * @var String
    */
   private $_hook_register_info = "";
   
   /**
    * Tipologie di hook  per il quale è valido questo hook
    * 
    * @var Array
    */
   private $_hook_type          = array();
   
   
   /**
    * Priorità di esecuzione degli hook dello stesso tipo, default 0
    * 
    * @var Array
    */
   private $_hook_priority      = array();
   
   
   /**
    * Tipologia hook attualmente gestita da questo hook
    * 
    * @var String
    */
   private $_hook_current_type  = null;
   
   
   /**
    * Indica se la propagazione degli hook è attiva
    * 
    * @var Boolean
    */
   private $_hooks_propagation_stop   = false;
   
   
   /**
    * Indica se processato, contiene un array chiave(hookType) => valore (Bool) in cui indicare per ogni tipologia di hook lo stato di processamento
    * 
    * @var Array
    */
   private $_processed               = false;
   
   
   /**
    * Indica se l'hook è processabile più volte, default true
    * 
    * @var Boolean
    */
   private $_isProcessableMultipleTimes = true;
   
   /**
    * Restituisce il nome della classe invocata
    * 
    * @return String
    */
   public static function getDefaultName()
   {
      return get_called_class();
   }
   
   
   /**
    * Imposta il Nome di questo hook
    * 
    * @param String $hookName Nome
    * 
    * @return \Abstract_Hooks
    */
   public function setHookName($hookName)
   {
      $this->_hook_name = $hookName;
      return $this;
   }
   
   /**
    * Imposta la descrizione di questo hook
    * 
    * @param String $hookDescription Descrizione
    * 
    * @return \Abstract_Hooks
    */
   public function setHookDescription($hookDescription)
   {
      $this->_hook_description = $hookDescription;
      return $this;
   }
   
   /**
    * Imposta le informazioni di registrazione dell'hook
    * 
    * @param String $hookRegisterInfo info
    * 
    * @return \Abstract_Hooks
    */
   public function setHookRegisterInfo($hookRegisterInfo)
   {
      $this->_hook_register_info = $hookRegisterInfo;
      return $this;
   }
   
   /**
    * Imposta la tipologia attuale elaborata da questo hook
    * 
    * @param String $hookType HookType
    * 
    * @return \Abstract_Hooks
    */
   public function setHookCurrentType($hookType)
   {
      $this->_hook_current_type = $hookType;
      return $this;
   }
   
   
   
   /**
    * Restituisce la tipologia di hook attualmente gestita da questo hook
    * 
    * @return String
    */
   public function getHookCurrentType()
   {
      return $this->_hook_current_type;
   }
   
   
   /**
    * Imposta il Tipo di questo hook
    * 
    * @param   Mixed  $hookType       Tipologia  hook o array contenente le tipologie con associati i metodi da richiamare (opzionali, richiamto di default il metodo dell'hook principale)
    * 
    * @return \Abstract_Hooks
    */
   public function setHookType($hookType)
   {
      if(is_string($hookType) && strlen($hookType) > 0)
      {
         $hookType = array($hookType => self::HOOK_DEFAULT_METHOD);
      }
      else if(is_array($hookType))
      {    
         foreach($hookType as $type => $methodName)
         {
            if(is_numeric($type))
            {
               unset($hookType[$type]);
               $type          = $methodName;
               $methodName    = self::HOOK_DEFAULT_METHOD;
            }
            
            $hookType[$type] = $methodName;
         }
         
      }
      else
      {
         return self::throwNewException(92834898343490284,' Tipologia di hook non valida: '.print_r($hookType,true));
      }
          
      $this->_hook_type = $hookType;
      return $this;
   }
   
   
   /**
    * Imposta la prioritò di hook con il quale a pari tipologia verranno eseguiti o prima o dopo
    * 
    * <b>Se esiste un hook dello stesso tipo di pari priorità verrà caricato aumentandone la priorità ed eseguendolo di conseguenza prima</b>
    * 
    * @param   Mixed    $hookPriority   [OPZIONALE] Priorità o array con le priorità per ogni tipologia di hook, default = 0 appende agli hook già registrati
    * 
    * @return \Abstract_Hooks
    */
   public function setHookPriority($hookPriority)
   {
      $hookPriorityArray = array();
      
      if(is_numeric($hookPriority))
      {
         $hookType = $this->getHookType();
         
         if(!is_array($hookType) || count($hookType) == 0)
         {
            return $this->throwNewException(98398439022038297429842, 'Prima di impostare la priorità dell\'hook si deve configurare la tipologia');
         }
         
         $this->_hook_priority = array();

         foreach($hookType as $type => $methodName)
         {
            $hookPriorityArray[$type] = $hookPriority;
         }
         
      }
      else if(is_array($hookPriority))
      {    
         foreach($hookPriority as $type => $priority)
         {  
            
            if(is_numeric($type))
            {
               unset($hookPriority[$type]);
               $type = $priority;
               $priority = 0;
            }
            
            $hookPriorityArray[$type] = $priority;
         }
      }
      else
      {
         return self::throwNewException(92834898343490284,' Priorità specificata all\'hook non valida: '.print_r($hookType,true));
      }
      
      $this->_hook_priority = $hookPriorityArray;

      return $this;
   }
   
   
   /**
    * Restituisce l'hook type
    * 
    * @return Array
    */
   public function getHookType()
   {
      return $this->_hook_type;
   }
   
   /**
    * Indica se l'hook ha la tipologia di specificata tra quelle configurate
    * 
    * @param String $hookType Hook type
    * 
    * @return Boolean
    */
   public function hasHookType($hookType)
   {       
       if(empty($this->_hook_type))
       {
           return false;
       }
       
       return isset($this->_hook_type[$hookType]);
   }
   
   /**
    * Restituisce il nome di questo hook
    * @return String
    */
   public function getHookName(){
      return $this->_hook_name;
   }
   
   /**
    * Restituisce il nome di questo hook
    * @return String
    */
   public function getHookDescription(){
      return $this->_hook_description;
   }
   
   /**
    * Restituisce le info di registrazione dell'hook
    * @return String
    */
   public function getHookRegisterInfo(){
      return $this->_hook_register_info;
   }
   
   
   /**
    * Restituisce la prioritò di hook con il quale a pari tipologia verranno eseguiti o prima o dopo
    * 
    * @return Array
    */
   public function getHookPriority(){    
      return $this->_hook_priority;
   }
   
   
   /**
    * Inizializza questo hook
    * <b> Questo medoto inizializza l'hook dal child</b>
    * 
    * @param   Mixed          $hookType       Tipologia  hook o array contenente le tipologie con associati i metodi da richiamare (opzionali, richiamto di default il metodo dell'hook principale)
    * @param   String         $hookName       [OPZIONALE] Nome hook, default nome della classe
    * @param   Mixed          $hookPriority   [OPZIONALE] Priorità o array con le priorità per ogni tipologia di hook, default = 0 appende agli hook già registrati
    * 
    * @return Abstract_Hooks
    */
   protected function initMe($hookType,$hookName = null,$hookPriority = 0)
   {
       
       if(is_null($hookName))
       {
           $hookName = $this->getDefaultName();
       }
       
       $this->setHookType($hookType)
            ->setHookName($hookName)
            ->setHookPriority($hookPriority);
      
      return $this;
   }
 
   
   /**
    * Restituisce la configurazione di sottoscrizione degli hooksType e delle Priority relative a questo hook
    * 
    * Questo metodo va sovrascitto nell'hook figlio e verrà utilizzato esclusivamente se l'hook è impostato con l'hookType di default self::HOOK_TYPE_SUBSCRIBER
    * 
    * es: 
    *    array(
    *       <hookType> =>  array(
    *               array(<methodName1>  => <priority1>),
    *               array(<methodName2>  => <priority2>)
    *               array(<methodName3>  => <priority3>)
    *       )
    *    ) 
    * 
    * 
    * @return boolean
    */
   public static function getSubscriberConfiguration()
   {
      return false;
   }
   
   /**
    * Setta l'hook come processato  per un determinato hookType e methodName
    * 
    * @param String  $hookType      Tipologia di hook elaborato
    * @param String  $methodName    [OPZIONALE] Nome del metodo processato, default self::HOOK_DEFAULT_METHOD
    * @param Boolean $processed     [OPZIONALE] Indica lo stato di processamento, default TRUE
    * 
    * @return Abstract_Hooks
    */
   public function setProcessed($hookType,$methodName = self::HOOK_DEFAULT_METHOD,$processed = true)
   {
      
      if(!is_array($this->_processed))
      {
         $this->_processed = array();
      }
      
      $this->_processed[$hookType][$methodName] = $processed;
      return $this;
   }
   
   
   /**
    * Indica se l'hook è stato elaborato
    *  
    * @param String $hookType    Tipologid di hook
    * @param String $methodName  Metodo invocato
    * 
    * @return Boolean
    */
   public function isProcessed($hookType,$methodName = self::HOOK_DEFAULT_METHOD)
   {
      return is_array($this->_processed) && isset($this->_processed[$hookType][$methodName]) ? $this->_processed[$hookType][$methodName] : false;
   }
   
   
   /**
    * Questo metodo permette di determinare se l'hook deve essere attivo o meno in base ai parametri passati in fase di esecuzione
    * 
    * @param Application_HooksData  $hookData Dati processati per questo hook
    * 
    * @return Boolean
    */
   public function isEnable(Application_HooksData $hookData)
   {
      return true;
   }
   
   
   /**
    * Indica se questo hook è registrabile dall'HookManager
    * 
    * @return boolean
    */
   public function isRegistrable()
   {
      return true;
   }
   
   
   /**
    * Indica se questo hook è processabile più volte o solamente una volta
    * 
    * @return boolean
    */
   public function isProcessableMultipleTimes()
   {
       return $this->_isProcessableMultipleTimes;
   }
   
   
   /**
    * Indica se l'hook è registrabile più volte
    * 
    * @param Boolean $isProcessableMultipleTimes
    * 
    * @return \Abstract_Hooks
    */
   public function setIsProcessableMultipleTimes($isProcessableMultipleTimes)
   {
       $this->_isProcessableMultipleTimes = $isProcessableMultipleTimes;
       return $this;
   }
   
   /**
    * Elabora questo hook
    * 
    * <b>Questo metodo va sovrascritto per l'elaborazione dell'hook</b>
    * 
    * @param Application_HooksData $hookData Dati passati dal kernel o dal controller che richiama questo hook
    * 
    * @return Application_HooksData Dati elaborati
    */
   public function doProcessMe(Application_HooksData $hookData)
   {
      return $hookData;
   }

   
   /**
    * Hook in formato string
    * 
    * @return String
    */
   public function __toString()
   {
      return $this->getHookName();
   }
   
   
   public function __construct(array $parameters = null)
   {       
       if($parameters)
       {
           foreach($parameters as $key => $val)
           {
               if(isset($this->{"_{$key}"}))
               {
                    $this->{"_{$key}"} = $val;
               }
           }
       }
   }
}

