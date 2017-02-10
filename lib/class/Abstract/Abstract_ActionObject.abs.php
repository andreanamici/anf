<?php

/**
 * Classe Astratta da utilizzare per tutte le classi che vogliono implementare il sistema degli actionObject come metodo di processing delle action dell'applicazione
 * 
 * Questi oggetti devono produrre una response in formato array, che verrà passata all'ActionController invocato,
 * il quale, generarà una response da fornire al Kernel, che rilascerà l'output.
 * 
 * Tali azioni possono anche essere richiamate dall'ActionController come "main" = false, che permette 
 * di sfruttare il pattern HMVC, producendo cosi una struttura modulare.
 * 
 * E' Possibile comunque bypassare questo sistema e fornire dal routing un sistema diverso, indicando una classe alternativa che non deve necessariamente estendere questo oggetto.
 */
abstract class Abstract_ActionObject implements Interface_ActionObject
{  
    use Trait_Singleton,Trait_DAO,Trait_ObjectUtilities,Trait_FlashMessages,
    
        Trait_Application,
            
        Trait_Controller,    
            
        Trait_ApplicationTemplating,
            
        Trait_ApplicationHooks { 
            registerHook as private _registerHookInActionObject; //Questo  permette di poter controllare la registrazione degli hook solamente per un particolare tipo di hooks
        }
   
   /**
    * Restituisce la lista degli hooks disponibili per gli ActionObject
    * 
    * @var Array
    */
   private static $_HOOKS_AVAILABLE = Array(
         \Interface_HooksType::HOOK_TYPE_PRE_ACTION,
         \Interface_HooksType::HOOK_TYPE_POST_ACTION,
         \Interface_HooksType::HOOK_TYPE_PRE_TEMPLATE,
         \Interface_HooksType::HOOK_TYPE_POST_TEMPLATE,
         \Interface_HooksType::HOOK_TYPE_KERNEL_END,
         \Interface_HooksType::HOOK_TYPE_PRE_RESPONSE,
         \Interface_HooksType::HOOK_TYPE_SESSION_EXPIRE,
         \Interface_HooksType::HOOK_TYPE_LOCALE_TRANSLATE_BEFORE,
         \Interface_HooksType::HOOK_TYPE_LOCALE_TRANSLATE_TRANS,
         \Interface_HooksType::HOOK_TYPE_LOCALE_TRANSLATE_AFTER,
         \Interface_HooksType::HOOK_TYPE_EXCEPTION
   );   
   
   /**
    * Azione Processata dall'ActionObject
    * 
    * @var String, detaul ""
    */
   protected $_action                = "";  
   
   /**
    * Azione Preocessata in caso di Sessione Scaduta, default "logout"
    * 
    * @var String
    */
   protected $_action_session_expire = "logout";
   
   /**
    * Method Processata dall'ActionObject, utile per realizzare oggetti che smistano piu azioni al loro interno
    * 
    * @var String, default ""
    */
   protected $_method           = "";
   
   
   /**
    * Nome del metodo invocato sull'actionObject
    * 
    * @var type 
    */
   protected $_methodName       = "";
   
   /**
    * Tipologia di action che determina l'invocazione di un Controller diverso in fase di elaborazione
    *
    * @var String 
    */
   protected $_action_type          = self::ACTION_TYPE_ALL;
   
   /**
    * Indica se Controllare lo stato di alcuni campi della sessione, default FALSE
    * 
    * @var Boolean 
    */
   protected $_check_session        = false;
   
   /**
    * Closure da applicare per controllare la sessione
    * 
    * @var Closure 
    */
   protected $_check_session_closure = null;
   
   /**
    * Response Restituita al Controller invocante che verrà poi passata all'applicationTemplating per il Rendering
    * 
    * @var Array 
    */
   protected $_response             = Array(); 
   
   /**
    * Lista dei template da caricare
    * 
    * @var Array
    */
   protected $_tpl_list             = Array();
   
   /**
    * Indica se skippare o meno il caching dei file template precedentemente elaborati e cachati
    * 
    * @var Boolean, default FALSE
    */
   protected $_tpl_skip_cache       = false;
   
   /**
    * Tipologia di template engine da utilizzare, settato di default dall'ActionController
    * 
    * @var String
    */
   protected $_tpl_engine           = false;
   
   /**
    * Estensione dei file template in uso, default <APPLICATION_TEMPLATING_TPL_FILE_EXTENSION>
    * 
    * @var String 
    */
   protected $_tpl_file_extension    = APPLICATION_TEMPLATING_TPL_FILE_EXTENSION;
   
   /**
    * Sottodirectory in cui ricercare i template da renderizzare
    * 
    * @var String
    */
   private   $_tpl_sub_folder        = '';
   
   
   /**
    * Inizializza l'actionObject
    * 
    * @return Abstract_ActionObject
    */
   public function __construct()
   {
       $action = strtolower(str_replace('Action_','',get_called_class()));
       return $this->initMe($action,self::ACTION_TYPE_ALL,false);
   }
   
   /**
    * Imposta il nome dell'action, utile se l'object istanziato è quello generico
    * 
    * @param String $action Action
    * 
    * @return Abstract_ActionObject
    */
   public function setAction($action)
   {
      $this->_action = $action;
      return $this;
   }
   
   
   /**
    * Imposta una seconda azione utile per eventuali switch di comandi / metodi
    * 
    * @param String  $method            Stringa che indica il metodo 
    * @param Boolean $updateMethodName  [OPZIONALE] Indica se rielaborare tramite l'actionController il nome del metodo reale di questo actionObject, default true
    * 
    * @return Abstract_ActionObject
    */
   public function setMethod($method,$updateMethodName = true)
   {
      $this->_method     = $method;
      
      if($updateMethodName)
      {
         $this->_methodName = $this->getActionController()->getActionObjectMethodName($method);
      }
      
      return $this;
   }
   
   /**
    * Imposta il metodo invocato sull'actionObject
    * 
    * @param string $methodName metodo
    * 
    * @return \Abstract_ActionObject
    */
   public function setMethodName($methodName)
   {
       if(!method_exists($this, $methodName))
       {
           return self::throwNewException(239050927502935, 'Questo actionObject "'.$this.'" non ha il metodo indicato '.$methodName);
       }
       
       $this->_methodName = $methodName;
       return $this;
   }
   
   /**
    * Imposta il tipo  di action, utile se l'object istanziato è quello generico
    * 
    * @param String $actionType action type
    * 
    * @return Abstract_ActionObject
    */
   public function setActionType($actionType)
   {
      $this->_action_type = $actionType;
      return $this;
   }

   
   /**
    * Imposta se l'action deve controllare lo stato della sessione attiva
    * 
    * @param Boolean $check Flag TRUE=>controllo sessione attivo, FALSE non controlla
    * 
    * @return Abstract_ActionObject
    */
   public function setCheckSession($check)
   {
      $this->_check_session = $check;
      return $this;
   }
   
   
   /**
    * Imposta la Closure da applicare per determinare se la sessione è scaduta
    * 
    * @param Closure $checkSessionClosure Closure da applicare
    * 
    * @return Abstract_ActionObject
    */
   public function setCheckSessionClosure(Closure $checkSessionClosure = null)
   {
       $this->_check_session_closure = $checkSessionClosure;
       return $this;
   }
   
   /**
    * Imposta la lista di template da caricare, di default Effettua il merge di tutti i tpl passati
    * 
    * @param Array    $tmpListArr  Array / Singolo tpl da caricare
    * @param Boolean  $merge       Indica se concatenare la lista
    * 
    * @return Abstract_ActionObject
    */
   public  function setTemplateList($tmpListArr,$merge = false)
   {
      $tmpListArr = is_array($tmpListArr) ? $tmpListArr : (strlen($tmpListArr) > 0 ? array($tmpListArr) : false);
      
      if($tmpListArr!==false){
          $this->_tpl_list =  $merge ? array_merge($tmpListArr,$this->_tpl_list) : $tmpListArr;
      }
      
      return $this;
   }
   
   
   /**
    * Imposta il tipo di template Engine che l'applicationTemplating dovrà utilizzare per processare questa Action
    * 
    * @param String $tplEngine Nome del servizio del template Engine
    * 
    * @return Abstract_ActionObject
    */
   public function setTemplateEngine($tplEngine)
   {
      $this->_tpl_engine = $tplEngine;
      return $this;
   }
   
     
   /**
    * Imposta l'estenzione dei file template in uso
    * 
    * @param String $tplEngine Nome del template Engine
    * 
    * @return Abstract_ActionObject
    */
   public function setTemplateFileExtension($fileExt)
   {
      $this->_tpl_file_extension  = $fileExt;
      return $this;
   }
   
   /**
    * Imposta una sottodirectory in cui ricercare i template da renderizzare
    * 
    * @param String $folder directory, es: 'ajax/directory/subdirectory/...'
    * 
    * @return \Abstract_ActionObject
    */
   public function setTemplateSubFolder($folder)
   {
      $this->_tpl_sub_folder = $folder;
      return $this;
   }   
      
   /**
    * Imposta la response
    * 
    * @param Mixed    $response, se é di tipo Array
    * @param Boolean  $merge     se TRUE e la response è di tipo Array effettua il Merge
    * 
    * @return Abstract_ActionObject
    */
   public  function setResponse($response,$merge = true)
   {
      $this->_response =  gettype($response) == "array" && gettype($this->_response) == "array" && $merge ? array_merge($this->_response,$response) : $response;
      return $this;
   }
   
   /**
    * Imposta l'Action da processare il caso di sessione scaduta. Il Portale effettuerà un redirect automatico nel momento in cui
    * L'actionObject elaborato prevede un controllo di validità sulla sessione e questa risulta non valida.
    * 
    * @param String   $action Action da processare
    * 
    * @return Abstract_ActionObject
    */
   public function setActionSessionExpire($action)
   {
      $this->_action_session_expire = $action;
      return $this;
   }
      
   /**
    * Restituisce il nome dell'action
    * @return String
    */
   public function getAction()
   {
      return $this->_action;
   }
   
   /**
    * Restituisce la seconda azione utile per eventuali switch di comandi / metodi
    * 
    * @return String
    */
   public function getMethod()
   {
      return $this->_method;
   }
   
   /**
    * Restituisce il nome del metodo invocato su questo actionObject
    * 
    * @return String
    */
   public function getMethodName()
   {
       return $this->_methodName ?: $this->_method;
   }
   
   /**
    * Restituisce il tipo di action
    * @return String
    */
   public function getActionType()
   {
      return $this->_action_type;
   }
   
   /**
    * Restituisce se controllare lo stato della sessione
    * @return Boolean
    */
   public function getCheckSession()
   {
      return $this->_check_session;
   }
   
   /**
    * Campi da controllare nella sessione, default "id_usr"
    * 
    * La Closure sarà bindata sull'ActionObject invocato
    * 
    * @return Closure
    */
   public function getCheckSessionClosure()
   {
      return $this->_check_session_closure;
   }
   
   /**
    * Restituisce la lista dei template da elaborare
    * 
    * @return Array
    */
   public function getTemplateList()
   {
      return $this->_tpl_list;
   }
   
   /**
    * Restiusce il nome del servizio del template Engine da utilizzare
    * 
    * @return String
    */
   public function getTemplateEngine()
   {
      return $this->_tpl_engine;
   }
   
   
   /**
    * Restituisce l'estenzione dei file tpl che verranno usati
    * 
    * @return String
    */
   public function getTemplateFileExtension()
   {
      return $this->_tpl_file_extension;
   }

   
   /**
    * Restituisce la sottodirectory in cui ricercare i template
    * 
    * @return String
    */
   public function getTemplateSubFolder()
   {
      return $this->_tpl_sub_folder;
   }
   
   /**
    * Restituisce l'Action da processare il caso di sessione scaduta.
    * 
    * @return String Action da processare
    */
   public function getActionSessionExpire()
   {
      return $this->_action_session_expire;
   }
   
   
   /**
    * Restituisce la resonse
    * 
    * @param Boolean $adapted Indica se restituirla in formato adattato (Array), default TRUE
    * 
    * @return Mixed
    */
   public function getResponse($adapted = true)
   {
      if($adapted)
      {
         $this->_response = $this->_adaptResponse($this->_response);
      }
      
      return $this->_response;
   }
      
   /**
    * Restituisce il nome del proprio Package, sfruttando il posizionamento dell'ActionObject navigando a ritroso nelle cartelle del filesystem.
    * 
    * @return String
    */
   public static function getPackage()
   {
      $reflectionClass    = new ReflectionClass(get_called_class());
      return basename(realpath(dirname($reflectionClass->getFileName()).'/../'));
   }
   
   
   /**
    * Restituisce il path assoluto di questo actionObject
    * 
    * @return String
    */
   public function getAbsolutePath()
   {
       $reflectionClass    = new ReflectionClass(get_called_class());
       return $reflectionClass->getFileName();
   }
   
   /**
    * Restituisce l'instanza del proprio package in cui l'ActionObject è creato
    * 
    * @param Boolean $default Indica se restituire il package di default, default TRUE
    * 
    * @return Abstract_Package
    */
   public function getPackageInstance($default = true)
   {   
      $package    = null;
      $appKernel       = $this->getApplicationKernel();
      $appRoutingData  = $this->getApplicationRoutingCurrentRouteData();
      
      try
      {
          $package =  $appKernel->getPackageInstance(self::getPackage());
      }
      catch(\Exception $e)
      {
          return $package;
      }

      return $package;
   }
   
   /**
    * Indica se questo actionObject è soggetto ad essere tracciato nello stack di esecuzione
    * 
    * @return boolean
    */          
   public function isLoggable()
   {
      return true;
   }
  
   /**
    * Restituisce il nome del metodo da invocare nel caso in cui non sia disponibile quello specificato dal controller, default FALSE metodo esatto ricercato
    * 
    * @return boolean|string
    */
   public function getFallbackMethodName()
   {
       return false;
   }
   
   /**
    * Inizializza l'actionObject 
    * 
    * @param String  $action              Nome action, (nome del template utilizzato senza estenzioni)
    * @param String  $actionType          [OPZIONALE] Tipologia Action, default self::ACTION_TYPE_ALL
    * @param Boolean $checkSession        [OPZIONALE] Indica se controllare la sessione,default FALSE
    * @param Closure $checkSessionFields  [OPZIONALE] Indica la closure da applicare per determinare se la sessione è scaduta, default NULL
    * 
    * @return Abstract_ActionObject
    */
   public function initMe($action,$actionType = self::ACTION_TYPE_ALL,$checkSession = false,Closure $checkSessionClosure = null)
   {
      $this->initDAO()
           ->setAction($action)
           ->setActionType($actionType)
           ->setCheckSession($checkSession)
           ->setCheckSessionClosure($checkSessionClosure)
           ->setTemplateList($action);
      
      return $this;
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
   
       
   /**
    * Manipola la request che questo action object riverà per ogni metodo, utile per inserire / modificare / eliminare eventuali elementi all'interno dell'oggetto Application_ActionRequestData
    * 
    * @param \Application_ActionRequestData $actionRequestData
    * 
    * @return \Application_ActionRequestData
    */
   public function __doManipulateActionRequestData(\Application_ActionRequestData $actionRequestData)
   {
      return $actionRequestData;
   }
   
   /**
    * Questo metodo è richiamto automaticamente dal controller prima di elaborare ogni richiesta di questo ActionObject
    * 
    * @param \Application_ActionRequestData $actionRequestData
    * 
    * @return boolean
    */
   public  function __doOnPreProcess(\Application_ActionRequestData $actionRequestData)
   {
       return true;
   }

    /**
     * Questo metodo è richiamto automaticamente dal controller dopo l'elaborazione di ogni response fornita da questo ActionObject
     * 
     * Gestisce response in formato array
     * 
     * <b>Questo metodo deve restituire una response array</b>
     * 
     * @param Array $responseAdapted   Response Adattata
     * 
     * @return Arrray $responseAdapted
     */
   public  function __doOnPostProcess(array $responseAdapted)
   {
       return $responseAdapted;
   }
    
    
   /**
    * Metodo invocato di default per processare l'action attuale, in cui verrà impostata la response dell'ActionObject
    * 
    * @param Application_ActionRequestData $requestData   Parametri da passare all'action Object
    * 
    * @return Abstract_ActionObject
    */
   public function doProcessMe(Application_ActionRequestData $requestData)
   {
       return $this->setResponse(array());
   }
   
   /**
    * Lancia un eccezione di tipo Redirect
    * 
    * @param String  $where      Può essere una rotta, singolo nome <action>, nome action+method <action>/<method>, o ul url assoluto con il prefisso http://it-it.sito.com
    * @param Array   $data       Parametri passati sostituendoli nella rotta o come queryString
    * @param Boolean $absolute   Indica se l'url sarà assoluto (solo in caso in cui $where non è un url assoluto)
    *  
    * @throws Exception_RedirectException
    */
   protected final function doRedirect($where,array $data = Array(),$absolute = false)
   {            
      $redirectUrl = $this->generateUrl($where, $data, $absolute);
      return self::throwNewExceptionRedirect($redirectUrl);
   }
   
   /**
    * Mostra uno status HTTP 
    * 
    * @param Int    $httpStatus Status HTTP
    * @param String $message    Messaggio
    * 
    * @return Void
    * 
    * @throws \Exception_HttpStatusException
    */
   protected final function showHttpStatus($httpStatus,$message = "Http Status")
   {
       return self::throwNewExceptionHttpStatus($httpStatus,$message);
   }
   
   /* ~~~~~~~~~~~~ Override Metodi ~~~~~~~~~~~~~~~~~~~~~~~ */
   
   /**
    * Registra un hook
    * 
    * In questo step verranno eseguiti solamente gli hook presenti in self::$_HOOKS_AVAILABLE
    * 
    * {@inheritdoc}
    * 
    * @return Abstract_ActionObject
    */
    public function registerHook($hook,$hookType = null, $hookPriority = 0)
    {
       $hookType = is_array($hookType) ? $hookType : Array($hookType => Application_Hooks::HOOK_DEFAULT_METHOD);
       
       foreach($hookType as $hookTypeName => $hookMethod)
       {
          if(!in_array($hookTypeName,self::$_HOOKS_AVAILABLE))
          {
             return self::throwNewException(89236487236982,'Impossibile registrare questa tipologia di hook '.$hookTypeName.' con il metodo '.$hookTypeName.' nell\'ActionObject '.get_called_class());
          }
       }
              
       $this->_registerHookInActionObject($hook,$hookType, $hookPriority);
       
       return $this;
    }
    
    
   
   /* ~~~~~~~~~~~~~~~~~~~~~~~ Fine Metodi ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
   

   /**
    * Addata la response  restituendola sempre in formato Array se possibile, può lanciare eccezioni in caso di errori
    * 
    * @param Mixed $response Response
    * 
    * @return Array 
    */
   private function _adaptResponse($response)
   {    
       
       $responseType = gettype($response);
       
       if($responseType == "array")
       {
          return $response;
       }
       
       switch($responseType)
       {
             case "boolean":
             case "integer":
             case "double":
             case "string":
                             $response = Array("response"=>$response);
                             break;
             case "object":
                             if(!$response instanceof \Application_ControllerResponseData)
                             {
                                $response = $this->getUtility()->ObjectToArray($response);
                             }
                             break;
             case "NULL":
                             $response = Array("response"=>NULL);
                             break;
             default:
                             self::throwNewException(83740293723479203," Action Object ".  get_called_class() .", Response Invalida: ".print_r($response,true));
                           break;
       }
       
       return $response;
    }
    
   /**
    * Inoltra la richiesta all'actionController attualmente instanziato, che invocherò il relativo actionObject della rotta specificata, restituendo la response del controller utile al Kernel.
    * 
    * @param String          $routeName    Nome della rotta
    * @param array           $parameters   [OPZIONALE] Dati per la request, default generati dall'action controller
    * 
    * @return \Application_ControllerResponseData
    */
   protected function forwardActionControllerResponseByRoute($routeName,array $parameters = array())
   {
        return call_user_func_array(array($this->getActionController(),'forwardActionControllerResponseByRoute'),  func_get_args());
   }
   
   
   /**
    * Inoltra la richiesta all'actionController attualmente instanziato, che invocherò il relativo actionObject indicato, restituendo la response del controller utile al Kernel.
    * 
    * Questo metodo va invocato qualora si voglia sfruttare l'output di un ActionObjet specifico per incapsularlo nella response dell'actionObject attualmente elaborata.    
    * 
    * @param Mixed                         $action              String actionObject,callable o int es: "Action_index" oppure "webDefault\Action_index::sayHello"
    * @param Application_ActionRequestData $actionRequestData   [OPZIONALE] Dati per la request, default generati dall'action controller
    * 
    * @return \Application_ControllerResponseData
    */
   protected function forwardActionControllerResponse($action,Application_ActionRequestData $actionRequestData = null,$actionControllerType = null)
   {  
      return call_user_func_array(array($this->getActionController(),'forwardActionControllerResponse'),  func_get_args());
   }
   
   /**
    * Inoltra la richiesta all'actionController attualmente instanziato, che invocherò il relativo actionObject indicato, restituendo ne i dati elaborati. Questi dati sono 
    * utili per essere impostati come response di questo ActionObject, o per avare a disposizione dati da utilizzare, centralizzando cosi una serie di elaborazione in un ActionObject specifico
    * 
    * @param Mixed                         $action              String actionObject,callable o int es: "Action_index" oppure "webDefault\Action_index::sayHello"
    * @param Application_ActionRequestData $actionRequestData   [OPZIONALE] Dati per la request, default generati dall'action controller
    * @param String                        $controllerType      [OPZIONALE] Controller type, default NULL (Quello instanziato dal routing)
    * 
    * @return \Application_ActionResponse
    */
   protected function forwardActionResponseData($action,Application_ActionRequestData $actionRequestData = null,$controllerType = null)
   {
       return call_user_func_array(array($this->getActionController(),'forwardActionResponseData'),  func_get_args());
   }
   
    
    /**
     * Reppresenta questo actionObject in formato string
     * @return String
     */
    public function __toString() 
    {
       return get_called_class();
    }
}

