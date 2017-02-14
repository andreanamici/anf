<?php

/**
 * Controller principale di gestione e di elaborazione degli ActionObject
 */
class Controllers_ActionController extends Application_Controller
{   
      
    /**
     * Nome della cartella in cui sono contenute le ActionObject
     * 
     * @var String 
     */
    public static $_action_object_directory_name = ACTION_CNT_ACTION_OBJECT_FOLDER_NAME;
    
    /**
     * Nome della directory attuale in cui cercare le ActionObject Class
     *
     *  @var String 
     */
    protected static $_action_object_package = APPLICATION_PACKAGE_DEFAULT;
    
    /**
     * Prefisso Metodo Aggiuntivi disponibile per un determinato ActionObject. es: doIndex()
     * 
     * @var String
     */
    protected static $_action_object_method_method_prefix = ACTION_CNT_ACTION_OBJECT_SUBACTION_METHOD_PREFIX;
    
    /**
     * Prefisso delle classi ActionObject
     * 
     * @var Strings 
     */
    public static $_action_object_name_prefix        = ACTION_CNT_ACTION_OBJECT_PREFIX;
    
    /**
     * Nome della classe padre di ogni ActionObject
     * 
     * @var String 
     */
    public static $_action_object_abstract            = ACTION_CNT_ACTION_OBJECT_ABSTRACT_CLASS;

    /**
     * Nome della classe dell'ActionObject base per azioni senza un ActionObject definito nella cartella <ACTION_CNT_ACTION_OBJECT_FOLDER_NAME>
     * 
     * @var String
     */
    public static $_action_object_classname_def       = ACTION_CNT_ACTION_OBJECT_BASIC_CLASS;    
    
    /**
     * Nome dell'action di base utilizzata qualora non si utilizzi un'action specifica
     * 
     * @var String
     */
    public static $_action_object_basic_name          = ACTION_CNT_ACTION_OBJECT_BASIC_ACTION_NAME;
    
    /**
     * Action Object instanziato per l'action preparata dal portale
     * 
     * @var Abstract_ActionObject
     */
    protected $_action_object                      = null;
    
    /**
     * Numero di action processate
     * 
     * @var Int
     */
    protected $_action_processed_number            = 0;
    
    /**
     * Nome della classe dell'ActionObject instanziata
     * 
     * @var String 
     */
    protected $_action_object_name                 = null;
    
    /**
     * Dati passati agli actionObject elaborati per gestire la request, post , get, cookie, files etc..
     * 
     * @var Application_ActionRequestData
     */
    protected $_action_request_data                = null;
    
    /**
     * Dati elaborati dall'actionObject quando viene processato correttamente
     * 
     * @var \Application_ActionResponse
     */
    protected $_action_response                    = null;
    
    /**
     * Eccezione lanciata durante l'elaborazione di un actionObject
     * 
     * @var \Exception
     */
    protected $_action_exception                   = null;
    
    /**
     * Indica se si sta processando l'action principale
     * 
     * @var Boolean
     */
    protected $_main_action                        = true;
        
    /**
     * Action Controller Base
     * 
     * @param String $action Action
     * 
     * @return Boolean
     */
    public function __construct($action = self::DEFAULT_ACTION)
    { 
       parent::__construct($action);
       $this->_init();
    }
     
    /**
     * Inizializza il controller in base all'azione
     * 
     * @param String $action
     * 
     * @return Boolean
     */
    protected function _init()
    {        
        return $this;
    }
    
    /**
     * Restituisce un  Application_ActionRequestData utile agli ActionObject, questo oggetto estende l'httpRequest globale
     * 
     * @return \Application_ActionRequestData
     */
    public static function getActionRequestData(Application_ArrayBag $params = null)
    {
        $httpRequest = self::getApplicationHttpRequest();
        return new Application_ActionRequestData($httpRequest,$params);
    }  
    
    
    /**
     * Restituisce l'Action Object inizializzato
     * 
     * @return Abstract_ActionObject
     */
    public function getActionObject()
    {
       return $this->_action_object;
    }
    
    
    /**
     * Restituisce i dati elaborati dall'actionObject
     * 
     * @return \Application_ActionResponse
     */
    public function getActionResponse()
    {
        return $this->_action_response;
    }
    
    /**
     * Imposta l'actionObject da processare
     * 
     * @param Abstract_ActionObject $actionObject ActionObject
     * 
     * @return Controllers_ActionController
     */
    public function setActionObject(Abstract_ActionObject $actionObject)
    {
        $this->_action_object = $actionObject;
        return $this;
    }
     
    /**
     * Indica se l'action processata è quella principale, per la quale non verranno
     * controllate le eccezioni propagate verso il Kernel, come quella di redirect. Le action
     * secondarie processate, qualora lanciassero eccezioni, restiuirano tale eccezione come response
     * 
     * 
     * @param Boolean $mainAction 
     * 
     * @return \Controllers_ActionController
     */
    public function setMainAction($mainAction)
    {
        $this->_main_action = $mainAction;
        return $this;
    }
    
    /**
     * Indica se l'action processata è quella principale
     * 
     * @return Boolean
     */
    public function isMainAction()
    {
        return $this->_main_action;
    }
    
    /**
     * [SHORTCUT]
     * 
     * Prepara e processa l'action
     * 
     * @param Mixed $action     Azione da processare
     * @param Mixed $method     Metodo da invocare
     * @param Array $actionParameters
     * 
     * @return \Application_ControllerResponseData
     */
    public function processAction($action = self::DEFAULT_ACTION,$method = self::DEFAULT_SUBACTION,ArrayObject $actionParameters = null,$mainAction = null)
    {   
        if(!is_null($mainAction))
        {
            $this->setMainAction($mainAction);
        }
        else
        {
            $this->setMainAction($this->_action_processed_number == 0);
        }
        
        if($this->doActionPrepare($action, $method, $actionParameters))
        {
            return $this->doActionProcess();
        }
        
        return self::throwNewException(3409737694809457025, 'Non è possibile processare l\'action '.print_r($action,true).($method ? ', method '.print_r($method,true) : ''));
    }
    
    /**
     * Prepara l'azione da processare
     *
     * Se $action e' vuota gira la richiesta alla pagina di default configurata, se è di tipo numerica, lancia una pagina con il relativo status presente nella directory error
     * 
     * @param Mixed         $action             Azione da processare, es: "index", "Action_index", "webDefault\action\Action_index", o callable
     * @param String        $method             [OPZIONALE] Metodo da invocare sull'ActionObject processato,default self::DEFAULT_SUBACTION
     * @param ArrayObject   $actionParameters   [OPZIONALE] Parametri da passare all'ActionObject processato, può contenere anche parametri utili a questo controller, es: lang, locale, package etc..
     * 
     * @return \Controllers_ActionController
     */
    public function doActionPrepare($action = self::DEFAULT_ACTION,$method = self::DEFAULT_SUBACTION,ArrayObject $actionParameters = null)
    {
       $this->_action          = $action;
       $this->_method          = $method;
       
       $this->_initActionParameters($actionParameters);
       
       $actionObject = $this->generateActionObject($action, $method);
       
       /**
        * Quando processo un controller, configuro nei services l'actionController utilizzato per processare l'actionObject
        */
       $this->getApplicationServices()->unregisterService('controller')
                                      ->registerService('controller', $this);

       /**
        * Verifico l'integrità dell'actionObject generato
        */
       if($this->_checkActionObject($actionObject))
       {
            $this->_action_object = $actionObject;            
            $this->_action_object_name = (string) $this->_action_object;

            $this->_action_object->__doOnInit();
            
            //[Application_Hooks::HOOK_TYPE_PRE_ACTION] +++++++++++++++++++++++++++++++++++++++++++++++++++
            $this->_action_object = $this->processHooks(self::HOOK_TYPE_PRE_ACTION,$this->_action_object)->getActionObject();
            //[Application_Hooks::HOOK_TYPE_PRE_ACTION] +++++++++++++++++++++++++++++++++++++++++++++++++++
                        
            $this->_action        = $this->_action_object->getAction();
            $this->_method        = $this->_action_object->getMethod();
            $this->_action_type   = $this->_action_object->getActionType();

            // +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

            $this->getApplicationKernel()->get('@session')->addIndex("action",$this->_action);
            $this->getApplicationKernel()->get('@session')->addIndex("method",$this->_method);
            $this->getApplicationKernel()->get('@session')->addIndex("actiontype",$this->_action_type); 

            return $this;
       }
       
       if($this->_main_action && !$this->getKernelDebugActive())
       {
          return  $this->throwNewExceptionPageNotFound();
       }
       
       return $this->throwNewException(263491841968538438,"Impossibile generate un Action Object valido per l'action: ".print_r($action,true));
    }
    
    
    /**
     * Restituisce l'Application_ActionResponse elaborato
     * 
     * @return \Controllers_ActionController
     */
    protected function doActionObjectProcess()
    {    
         $this->_action_exception = null;
         
         try
         {
            $actionRequestData = $this->getActionRequestData($this->_action_parameters);

            $actionResponse  = false;
            
            $this->getApplicationServices()->registerService('controller.action', $this->_action_object);
            
            /**
             * Controllo Stato della sessione, se l'ActionObject lo prevede ed è attiva una sessione utente
             */
            if($this->_action_object->getCheckSession()!==false)
            {            
               $sessionClosure = $this->_action_object->getCheckSessionClosure();

               if(!($sessionClosure instanceof Closure))
               {
                   return $this->throwNewException(903284238492349930, 'Non è possibile controllare la sessione poichè la Closure function non è specificata dall\'ActionObject "'.$this->_action_object_name.'" in '.$this->_action_object->getAbsolutePath());
               }

               $sessionClosure = $sessionClosure->bindTo($this->_action_object,$this->_action_object);

               if(!$sessionClosure(self::getApplicationHttpRequest()->getSession()->getAll()))
               {
                 $actionLogoutSessionExpired = $this->_action_object->getActionSessionExpire();
                 $package                    = $this->_action_object->getPackage();
                 $lang                       = $this->_lang;

                 $url                        = $this->generateUrl($actionLogoutSessionExpired,Array('package'=>$package,'lang'=>$lang));

                 //[Application_Hooks::HOOK_TYPE_SESSION_EXPIRE] ++++++++++++++++++++++++++++++++++++++++++++++++
                 $url      = $this->processHooks(self::HOOK_TYPE_SESSION_EXPIRE,$url)->getData();
                 //[Application_Hooks::HOOK_TYPE_SESSION_EXPIRE] ++++++++++++++++++++++++++++++++++++++++++++++++

                 if($url)
                 {
                    return $this->redirect($url);
                 }

                 return $this->throwNewException(89273486236478, 'Redirect url non indicato! L\'hookType HOOK_TYPE_SESSION_EXPIRE deve restituire sempre un url valido ');
               }
            }

            
            /**
             * 
             * In base al Valore del metodo Processo il metodo di default di ogni ActionObject
             *  
             *   ->doProcessMe()
             * 
             * Verifico che l'ActionObject abbia il metodo costruito attraverso il valore della variabile in GET "method".
             * 
             *   -><prefix><method>()
             * 
             * Cosi facendo posso sia sfruttare la logica di:
             * 
             *   -  ACTION             -> ActionObject->doProcessMe()
             *   -  ACTION::SUBACTION  -> ActionObject-><prefix><method>()
             * 
             * Rendendo ogni ActionObject un vero e proprio controller per l'ACTION specificata.
             * 
             */        
            
            $methodName = $this->_action_object->getMethodName();
            
            if(strlen($methodName) > 0)
            {
               if(!$this->_isMethodExistsForActionObject($this->_action_object,$methodName)!==false)
               {
                  $methodNameFallback = $this->_action_object->getFallbackMethodName();
                  
                  if(!$methodNameFallback)
                  {
                     if($this->getKernelDebugActive())
                     {
                        return $this->throwNewException(2034818172662746,'Questo ActionObject "'.$this->_action_object_name.'" non ha il metodo '.$methodName.'() richiesto per la rotta: '.$this->getApplicationRoutingCurrentRouteData()->getRouteName());
                     }
                     else
                     {
                        return $this->throwNewExceptionPageNotFound();
                     }
                  }
                  else
                  {
                     $methodName = $methodNameFallback;
                  }
               }
            }

            $this->_action_request_data = $actionRequestData;

            $this->_action_object->__doManipulateActionRequestData($actionRequestData);

            $this->_action_object->__doOnPreProcess($actionRequestData);

            $actionResponse   = false;
            $actionException  = false;
                            
            $methodParameters = $this->getObjectMethodParameters($this->_action_object,$methodName);
           
            $data = $this->processHooks(self::HOOK_TYPE_PRE_ACTION_METHOD,array(
                                           'methodName'        => $methodName,
                                           'actionRequestData' => $actionRequestData,
                                           'methodParameters'  => $methodParameters
                    ))->getData();

            $methodName         = $data['methodName'];
            $actionRequestData  = $data['actionRequestData'];
            $methodParameters   = $data['methodParameters'];

            if(!$methodParameters)
            {
                $methodParameters = array($this->getActionRequestData());
            }
            
            $actionResponse = call_user_func_array(array($this->_action_object,$methodName),$methodParameters);   //Processo ActionObject Metodo Specifico            
            
            $this->_action_processed_number++;
            
            if($actionResponse instanceof $this->_action_object)
            {
                $actionResponse = $this->_action_object->getResponse();
            }
            
            if(empty($actionResponse))
            {
                return self::throwNewException(2387982374982670,'Questo action object "'.$this->_action_object.'" restituisce una response vuota!');
            }
            
            if(!$actionResponse instanceof \Application_ControllerResponseData) //Response non valida per il kernel
            {                
                if(!($actionResponse instanceof $this->_action_object))
                {
                   $actionResponse = $this->_action_object->setResponse(array($actionResponse))
                                                          ->getResponse();
                }
                
                $this->_action_object->setResponse($actionResponse,false);

                $actionResponse = $this->_action_object->getResponse(true);

                $actionResponse = $this->_action_object->__doOnPostProcess($actionResponse);
                
                if($actionResponse === false)
                {
                   return self::throwNewException(90238281384814934, 'il metodo '.$this->_action_object_name.'::__doOnPostProcess() deve restituire una response valida');
                }
                
                $actionResponse = $this->generateActionResponseData($this->_action_object);

                $actionResponse->setActionType($this->_action_object->getActionType())
                               ->setResponse($this->_action_object->getResponse());
                     
                //[Application_Hooks::HOOK_TYPE_POST_ACTION] ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
                $actionResponse = $this->processHooks(self::HOOK_TYPE_POST_ACTION,$actionResponse)->getData();
                //[Application_Hooks::HOOK_TYPE_POST_ACTION] ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
           
                /**
                 * Scrivo la response su file in caso di debug
                 */
                if($this->getKernelDebugActive() && $this->_action_object->isLoggable())
                {
                   $logMessage = " \nUrl:      ".self::getApplicationHttpRequest()->getServer()->getIndex("REQUEST_URI")."\n".
                                 " Action:     ".get_class($this->_action_object)."->".$methodName."()\n\n".
                                 " MainAction: ".($this->_main_action ? 'true' : 'false').'\n'.
                                 " Response:   ".print_r(json_encode($actionResponse->getResponse()->getArrayCopy()),true);

                   self::writeLog($logMessage,'actionresponse','a+',false);
                }
            }
            else //Response valida per il kernel
            {
                //[Application_Hooks::HOOK_TYPE_POST_ACTION] ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
                $actionResponse = $this->processHooks(self::HOOK_TYPE_POST_ACTION,$actionResponse)->getData();
                //[Application_Hooks::HOOK_TYPE_POST_ACTION] ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
                
                /**
                 * Scrivo la response su file in caso di debug
                 */
                if($this->getKernelDebugActive() && $this->_action_object->isLoggable())
                {
                   $logMessage = " \nUrl:      ".self::getApplicationHttpRequest()->getServer()->getIndex("REQUEST_URI")."\n".
                                 " Action:     ".get_class($this->_action_object)."->".$methodName."()\n\n".
                                 " MainAction: ".($this->_main_action ? 'true' : 'false').'\n'.
                                 " Response: ".  get_class($actionResponse);

                   self::writeLog($logMessage,'actionresponse','a+',false);
                }
            }
         }
         catch (\Exception $e)
         {
             $actionException = $e;
             $this->_action_exception = $actionException;
         }
        
         if(!$actionResponse)
         {
            $actionResponse = $this->generateActionResponseData($this->_action_object);
         }
         
         if($actionException)
         {
            if($this->_main_action)
            {
               throw $actionException; //Propago l'eccezione verso il kernel
            }
            else
            {
               if($actionException instanceof \Exception_RedirectException)
               {
                   throw $actionException;
               }
               else
               {
                    $actionResponse->setResponse(array(
                        'exception' => $actionException  //Restituisco tale eccezione come response
                    ));                    
               }
            }
         }
         
         $this->_action_response = $actionResponse;
         
         return $this;
    }
    
    /**
     * Elabora l'actionObject restituendo i dati elaborati, questo metodo non prenderà in considerazione l'elaborazione totale dell'action (compreso quindi di render view etc)
     * 
     * @return Application_ActionResponseData
     */
    public function doActionResponse()
    {
        return $this->doActionObjectProcess()->getActionResponse();
    }
    
    /**
     * Restituisce la response elaborata dal controller relativa all'actionObject preparato in precedenza
     * 
     * @return \Application_ControllerResponseData
     * 
     * @throws \Exception
     */
    public function doActionProcess()
    {
        return $this->generateControllerResponse('<!-- NO BODY RESPONSE -->');
    }
    
   /**
    * Inoltra la richiesta all'actionController attualmente instanziato, che invocherò il relativo actionObject indicato, restituendo la response del controller utile al Kernel.
    * 
    * Questo metodo va invocato qualora si voglia sfruttare l'output di un ActionObjet specifico per incapsularlo nella response dell'actionObject attualmente elaborata.    
    * 
    * @param Mixed       $action                String actionObject,callable, nome di una function es: "Action_index" oppure "webDefault\Action_index::sayHello"
    * @param array       $parameters            [OPZIONALE] parametri opzionali che verranno caricati nella request passata all'actionObject elaborato
    * @param String      $actionControllerType  [OPZIONALE] Tipologia di controller da invocare
    * 
    * @return \Application_ControllerResponseData
    */
    public function forwardActionControllerResponse($action,array $parameters = null,$actionControllerType = null)
    {
       $controllerResponse = null;
       
       try
       {           
          if(!$this->getApplicationServices()->hasService('controller'))
          {
             $this->getApplicationServices()->registerService('controller', $this);
          }
                    
          $mainAction = $this->isMainAction(); 
           
          $actionController   = $actionControllerType ? $this->getApplicationKernel()->getBuildActionController($actionControllerType) : $this->getApplicationKernel()->get('controller');
          
          $actionRequestData  = $this->getActionRequestData();
          
          if($parameters)
          {
             $actionRequestData->getActionParameters()->merge($parameters);
          }
                    
          $controllerResponse = $actionController->setMainAction(false)
                                                 ->doActionPrepare($action,null,$actionRequestData->getActionParameters())
                                                 ->doActionProcess();
          
          $actionController->setMainAction($mainAction);
       }
       catch(\Exception $e)
       {
           if($e instanceof \Exception_RedirectException)
           {
              $controllerResponse = $this->generateControllerResponse((string) $e);
           }
           else
           {
              throw $e;   
           }
       }
       
       return $controllerResponse;
    }
    
   /**
    * Inoltra la richiesta all'actionController, che invocherò il relativo actionObject della rotta specificata, restituendo la response del controller utile al Kernel.
    * 
    * @param String      $routeName             Nome della rotta
    * @param array       $parameters            [OPZIONALE] Dati per la request, default generati dall'action controller
    * @param String      $actionControllerType  [OPZIONALE] Tipologia di controller da invocare
    * 
    * @return \Application_ControllerResponseData
    */
    public function forwardActionControllerResponseByRoute($routeName,array $parameters = null,$actionControllerType = null)
    {
       try
       {           
            $currentRoutingData = clone $this->getApplicationRoutingCurrentRouteData();

            $appRoutingData     = $this->getApplicationRoutingCurrentRouteData();

            $routeData          = $this->getApplicationRouting()->getRouteInfo($routeName,true);

            $routeActionInfo    = $this->getApplicationRouting()->resolveRouteAction($routeName,$appRoutingData->getParams()->getArrayCopy());

            $appRoutingData->initMe($routeData);

            $actionControllerResponse =  $this->forwardActionControllerResponse(array($routeActionInfo['action'],$routeActionInfo['method']),$parameters,$actionControllerType);  
            
            $this->getApplicationRouting()->setApplicationRoutingData($currentRoutingData);
            
            return $actionControllerResponse;
       }
       catch(\Exception $e)
       {
           if($e instanceof \Exception_RedirectException)
           {
              $controllerResponse = $this->generateControllerResponse((string) $e);
           }
           else
           {
              throw $e;   
           }
       }
       
       return $controllerResponse;
    }
    
   /**
    * Inoltra la richiesta a questo controller o ad uno indicato, che invocherò il relativo actionObject, restituendo ne i dati elaborati. 
    * Questi dati sono utili per essere impostati come response di questo ActionObject, o per avare a disposizione dati da utilizzare, centralizzando cosi una serie di elaborazione in un ActionObject specifico
    * 
    * @param Mixed           $action                String actionObject,callable o int es: "Action_index" oppure "webDefault\Action_index::sayHello"
    * @param $parameters     $parameters            [OPZIONALE] Dati per la request, default generati dall'action controller
    * @param String          $actionControllerType  [OPZIONALE] Tipologia di controller da invocare
    * 
    * @return \Application_ActionResponse
    */
    public function forwardActionResponseData($action,array $parameters = null,$controllerType = null)
    {
       $actionResponse = null;

       try
       {      
          if(!$this->getApplicationServices()->hasService('controller'))
          {
             $this->getApplicationServices()->registerService('controller', $this);
          }
          
          $actionController  = $controllerType ? $this->getApplicationKernel()->getBuildActionController($controllerType) : clone $this->getApplicationKernel()->get('controller');
          
          $actionRequestData  = $this->getActionRequestData();
          
          if($parameters)
          {
             $actionRequestData->getActionParameters()->merge($parameters);
          }
          
          $isMainAction = $this->isMainAction(); 
                  
          $actionResponse = $actionController->doActionPrepare($action,null,$actionRequestData->getActionParameters())
                                             ->doActionResponse();
          
          $actionController->setMainAction($isMainAction);
       }
       catch(\Exception_RedirectException $e)
       {
           $actionResponse = $e;
       }
       
       return $actionResponse;
    }
    
    /**
     * Genera un instanza di un ActionObject valido da essere processato dal controller
     * 
     * @param Mixed  $action        Action string / callable / Closure, className
     * @param String $method     [OPZIONALE] Method
     * 
     * @return \Abstract_ActionObject
     * 
     */
    public function generateActionObject($action,$method = null)
    {
       $actionObject = null;
       
       if(is_numeric($action))   //In caso di $action intera, instanzio un ActionObjectHttpStatus per processare un httpStatus redirect
       {
          $actionObject = $this->_generateActionObjectHttpStatus($action);
       }
       else if($this->isActionCallable($action,$method))   //Instanzio un ActionObject Callable che ingabbia la callable in un ActionObject callable
       {
          $actionObject = $this->_generateActionObjectCallable($action,$method);
       }
       else //Genero un actionObject tramite il nome della classe, o semplicemente tramite il suffix del nome della classe, es: "Action_index" oppure "index"
       {
            $actionObject =  $this->_generateActionObject($action);
       }
               
       /**
        * Setto il templateEngine di default dell'actionObject
        */
       if($actionObject instanceof \Abstract_ActionObject)
       {           
            if(strlen($actionObject->getTemplateEngine()) == 0)
            {
               $actionObject->setTemplateEngine($this->getApplicationTemplating()->getTemplateEngine());
            }

            if(strlen($actionObject->getTemplateFileExtension()) == 0)
            {
               $actionObject->setTemplateFileExtension($this->getApplicationTemplating()->getTemplateFileExtension());
            }
       }
       
       if($actionObject && !$actionObject->getMethodName())
       {
          $actionObject->setMethod($method);
       }
       
       return $actionObject;
    }
    
    /**
     * Restituisce la response elaborata dall'actionObject
     * 
     * @param Interface_ActionObject $actionObject ActionObject
     * 
     * @return \Application_ActionResponse
     */
    public function generateActionResponseData(Interface_ActionObject $actionObject)
    {
        return new Application_ActionResponse($actionObject->getActionType(), $actionObject->getResponse(true));
    }
    
    /**
     * Restituisce il path assoluto del file Action Object da caricare.
     * 
     * @param String   $action        Azione, senza prefisso
     * @param String   $package       [OPZIONALE] Package, default quello utilizzato dal viewController
     * @param Boolean  $complete      [OPZIONALE] Indica se restituire anche il nome del file ActionObject, default true
     * 
     * @return String Path Assoluto
     */
    public function getActionObjectFilePath($action,$package = null,$complete = true)
    {
       $actionObjectFileClassName = $this->_getActionObjectFileName($action);
       
       $package = is_null($package)  ? $this->getApplicationTemplating()->getPackage() : $package;
       
       $actionObjectFilePath = false;
       
       if(strstr($action,"\\")!==false)
       {
           $actionObjectFilePath =  $this->_root_path.DIRECTORY_SEPARATOR.$this->getApplicationTemplating()->getUserDirectoryName();
       }
       else
       {
           $actionObjectFilePath = $this->_root_path. DIRECTORY_SEPARATOR .$this->getApplicationTemplating()->getUserDirectoryName().DIRECTORY_SEPARATOR. $package.DIRECTORY_SEPARATOR.self::$_action_object_directory_name;
       }
       
       if($complete)
       {
           $actionObjectFilePath.= DIRECTORY_SEPARATOR.$actionObjectFileClassName;
       }
       
       $actionObjectFilePath = str_replace("\\",DIRECTORY_SEPARATOR,$actionObjectFilePath);
       $actionObjectFilePath = str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR,DIRECTORY_SEPARATOR, $actionObjectFilePath);
       
       return $actionObjectFilePath;
    }
    
    
    /**
     * Restituisce il nome del file da caricare per l'action specificata
     * @param String $action Action
     * @return String
     */
    protected function _getActionObjectFileName($action)
    {
        if(strstr($action,"\\")!==false)
        {
            
            return $action.".act.php";
        }
        
        return self::$_action_object_name_prefix.strtolower($action).".act.php";
    }
    
    /**
     * Restituisce la lista dei parametri da passare all'actionObject, indipendentemente dal loro posto nella dichiarazione del metodo dell'actionObject
     * 
     * @param Mixed      $object  
     * @param string     $methodName    Metodo invocato
     * 
     * @return array
     */
    public function getObjectMethodParameters($object,$methodName)
    {
        $reflectionParameters = null;
        $reflectionClass      = null;
        $methodParameters     = array();
        
        /**
         * Se sto processando un actionObject callable, ricerco i metodi della callable o dell'actionObject reale processato (quello wrappato dall'actionObjectCallable)
         */
        if($object instanceof \Basic_ActionObjectCallable)
        {
            if($object->getObject())
            {
                $reflectionClass = new ReflectionClass($object->getObject());
                $reflectionParameters = $reflectionClass->getMethod($object->getMethodName())->getParameters();
            }
            else if($object->getCallable())
            {
                if(!($object->getCallable() instanceof \Closure))
                {
                    $reflectionClass  = new ReflectionClass($object->getCallable());
                    $reflectionParameters = $reflectionClass->getMethod('__invoke')->getParameters();
                }
                else
                {
                    $reflectionFunction   = new ReflectionFunction($object->getCallable());
                    $reflectionParameters = $reflectionFunction->getParameters();
                }
            }
            else
            {
                $reflectionClass = new ReflectionClass($object->getCallable());
            }
            
            $methodName = $object->getMethod(); //Il metodo diventa quello dell'oggetto gestito dall'actionObject callable
        }
        else
        {
            $reflectionClass = new ReflectionClass($object);
            $reflectionParameters = $reflectionClass->getMethod($methodName)->getParameters();
        }
        
        if(!$reflectionParameters)
        {
            return false;
        }
        
        if(!$reflectionClass)
        {
            return self::throwNewException(90237587257239052, 'Non è possibile generare il reflectionObject per l\'actionObject: '.$object);
        }
        
                
        if($reflectionClass && $reflectionClass->getMethods())
        {
            if($methodName && $reflectionClass && !$reflectionParameters)
            {
                $reflectionMethod     = $reflectionClass->getMethod($methodName);
                $reflectionParameters = $reflectionMethod->getParameters();
            }
        }
        
        if($reflectionParameters)
        {
            $requestData = $this->getActionRequestData();

            $methodParameters = array();

            if($reflectionParameters && is_array($reflectionParameters) && count($reflectionParameters) > 0)
            {   
               foreach($reflectionParameters as $reflectionParameter)/*@var $reflectionParameter \ReflectionParameter*/
               {                   
                   $serviceInstance = false;

                   try
                   {
                       $serviceInstance = $this->getApplicationServices()->getService($reflectionParameter->getName());
                       
                       if(!$serviceInstance)
                       {
                           $serviceName     = $this->getApplicationServices()->getServiceNameByVariable($reflectionParameter->getName());
                           $serviceInstance =  $this->getApplicationServices()->getService($serviceName);
                       }
                   }
                   catch(\Exception $e)
                   {
                       $serviceInstance = false;
                   }

                   if($serviceInstance)
                   {
                       $methodParameters[] = $serviceInstance;
                   }
                   else
                   {
                       if(!$reflectionParameter->isOptional())
                       {
                           if($requestData->get($reflectionParameter->getName()) !== false)
                           {
                               $methodParameters[] = $requestData->get($reflectionParameter->getName());
                           }
                           else if($this->getApplicationRoutingCurrentRouteData()->getDefaults()->offsetExists($reflectionParameter->getName()))
                           {
                                $methodParameters[] = $this->getApplicationRoutingCurrentRouteData()->getDefaults()->offsetGet($reflectionParameter->getName());
                           }
                           else
                           {
                               return self::throwNewException(283482599745020023482835, 'Questo actionObject "'.$object.'" per il  metodo "'.$methodName.'" non prevede un valore di default per il parametro "'.$reflectionParameter->getName().'" , che non è ne un service registrato, ne un valore presente nella httprequest attualmente elaborata');
                           }
                       }
                       else
                       {   
                            if($requestData->get($reflectionParameter->getName()) !== false)
                            {
                                $methodParameters[] = $requestData->get($reflectionParameter->getName());
                            }
                            else if($this->getApplicationRoutingCurrentRouteData()->getDefaults()->offsetExists($reflectionParameter->getName()))
                            {
                                $methodParameters[] = $this->getApplicationRoutingCurrentRouteData()->getDefaults()->offsetGet($reflectionParameter->getName());
                            }
                            else
                            {
                                $methodParameters[] = $reflectionParameter->getDefaultValue();
                            }
                       }
                   }
               }
            }
        }
        
        return $methodParameters;
    }
    
    /**
     * Restituisce il nome del metodo da invocare
     * 
     * @param String $method Metodo da invocare sull'ActionObject
     * 
     * @return String
     */
    public function getActionObjectMethodName($method)
    {
       if(strlen($method) == 0)
       {
           return self::ACTION_OBJET_DEFAULT_METHOD_NAME;
       }
       
       $method       = str_replace("-","_",$method);
       $methodPrefix = self::$_action_object_method_method_prefix;
       
       /**
        * Verifico che il method abbia il prefisso
        */
       if(substr($method,0,strlen(self::$_action_object_method_method_prefix)) == self::$_action_object_method_method_prefix)
       {
           $method  = substr($method,strlen(self::$_action_object_method_method_prefix),strlen($method)); //Rimuovo da nome del method il prefisso
       }
       
       $method = $this->getUtility()->String_StringToCamelcase($method);
               
       if(strlen($methodPrefix) > 0)
       {
            $method = ucfirst($method);
       }
       
       return $methodPrefix . $method;
    }
    
    /**
     * Restituisce il nome della classe da caricare per l'action specificata
     * 
     * @param String $action Action
     * 
     * @return String
     */
    protected function _getActionObjectClassName($action)
    {
        if(!is_string($action))
        {
            return false;
        }
        
        if(class_exists($action))
        {
           return $action;
        }
        else
        {
           $actionObjectClassName = self::$_action_object_name_prefix.$action;

           if(class_exists($actionObjectClassName))
           {
                return $actionObjectClassName;
           }
        }
        
        return false;
    }
   
    
    /**
     * Restituisce l'array che rapprenta la callable per l'action e method attualmente gestite
     * 
     * @return Array
     */
    protected function _buildCallableAction($action,$method = null)
    {
       $callableAction = null;
       
       if(is_array($action) && count($action) == 2)
       {
           if(($action[0] instanceof \Closure) && $action[1] == '')
           { 
              $callableAction = $action[0];
           }
           else if($action[1] != '')
           {
              $callableAction = $action;
           }
       }
       else if(is_callable($action)) //Closure, array callable
       {
           $callableAction = $action;
       }
       else if(is_string($action) && preg_match(self::ACTION_CALLABLE_STRING_REGEXP,$action))   //<class>::<method>
       {
           list($className,$method) = explode("::",$action);
           
           $callableAction =  array($className,$method);
       }
       else if($action && !empty($method))
       {
           $callableAction =  array($action,$method);
       }
       
       if(!$callableAction)
       {
          return self::throwNewException(092572834629423255706, 'Non è possibile generare una callable valida per l\'action: '.print_r($action,true). (!empty($method)>0 ? ', metodo: '.$method : ''));
       }
       
       if(is_array($action) && count($action) == 2)
       {
            if($action[1]!= '')
            {
                $methodName = $action[1];

                if(!$this->_method && isset($action[1]) && method_exists($action[0],$methodName))
                {
                     $this->_method = $methodName;
                }
            }
       }
           
       return $callableAction;
    }
    
    
    /**
     * Controlla che l'ActionObject Invocata abbia il metodo specificato
     * 
     * @param Abstract_ActionObject $ActionObject  ActionObject da elaborare
     * @param String                $methodName    Nome del metodo
     * 
     * @return Boolean
     */
    protected  function _isMethodExistsForActionObject($ActionObject,$methodName){
       return method_exists($ActionObject,$methodName)!==false ? true : false;
    }
    
    /**
     * Inizializza ed instanzia l'oggetto Action che provvederà a processare internamente tutte le operazioni necessarie
     * 
     * @param String $action Nome action,classe Action da instanziare
     * 
     * @return Boolean TRUE se oggeto instanziato, FALSE altrimenti
     */
    protected function _generateActionObject($action)
    {
       $actionObject  = null;
       
       if(is_array($action))
       {
           $actionInfo = $action;
           $action     = $actionInfo[0];
           $method     = $actionInfo[1];
       }
       
       if(is_string($action) && strlen($action) == 0)
       {
          return $actionObject;
       }
       
       $actionObjectPath      = $this->getActionObjectFilePath($action);    
       $actionObjectClassName = $this->_getActionObjectClassName($action);

       /**
        * Esiste un file specifico per l'action indicata
        */
       if(file_exists($actionObjectPath))
       {                
          require_once $actionObjectPath;
          
          if(!class_exists($actionObjectClassName))
          {
             return self::throwNewException(26349184196853168,"Impossibile trovare Action Object '".$actionObjectClassName."' nel file ".$actionObjectPath);
          }
          
          /**
           * Ricerco il metodo singleton "getInstance" se presente, altrimenti invoco normalmente l'actionObject
           */
          $actionObject = method_exists($actionObjectClassName,'getInstance') ? call_user_func(array($actionObjectClassName,'getInstance')) : new $actionObjectClassName();
       }
       if(class_exists($action))   //$action è un nome di una classe valida
       {
          $actionObject  = method_exists($action,'getInstance') ? call_user_func(array($action,'getInstance')) : new $action();
       }
       else if(class_exists($actionObjectClassName))
       {
           $actionObject  = method_exists($actionObjectClassName,'getInstance') ? call_user_func(array($actionObjectClassName,'getInstance')) : new $actionObjectClassName();
       }
       /**
        * Elaboro un actionObject basic anche in base al nome del singolo template per il package attualmente processato dal routing
        */
       else if($action == self::$_action_object_basic_name || $this->getService('templating')->isTemplateExists($this->_action)) 
       {
           $actionObject = $this->_generateActionObjectBasic();
       }
       
       if($actionObject && isset($method))
       {
           $actionObject->setMethod($method);
       }
       
       return $actionObject;
    }
    
    
    /**
     * Inizializza l'actionObject attraverso una callable
     *  
     * @param callable $action Action callable, array, functionName
     * 
     * @return \Abstract_ActionObject
     */
    protected function _generateActionObjectCallable($action,$method = null)
    {
        $actionCallable = $this->_buildCallableAction($action,$method); 
        return new Basic_ActionObjectCallable($this,$actionCallable);
    }
    
    /**
     * Genera un ActionObject utile per processare HTTP Status forniti dalle rotte
     * 
     * @return \Abstract_ActionObject
     */
    protected function _generateActionObjectHttpStatus($action)
    {
        return new Basic_ActionObjectHttpStatus($action);   
    }
    
    
    /**
     * Inizializza un Basic_ActionObject 
     * 
     * @return Basic_ActionObject
     */
    protected function _generateActionObjectBasic()
    {
        $actionObjectClassName = self::$_action_object_classname_def;
        
        $ActionObject          = call_user_func(array($actionObjectClassName,'getInstance'));
        
        $ActionObject->initMe($this->_action,$ActionObject::ACTION_TYPE_EXT,false);
        
        return $ActionObject;
    }
    
    /**
     * Inizializza ed elabora gli ActionParameters passati dalle rotte ed utili per eventuali cambiamenti di dati nel controller e per essere inviati agli ActionObject in REQUEST
     * 
     * @param ArrayObject $actionParameter Parametri
     * 
     * @return Controllers_ActionController
     */
    protected function _initActionParameters(ArrayObject $actionParameter = null)
    {          
       if($actionParameter instanceof ArrayObject)
       {
          $actionParametersArray = $actionParameter->getArrayCopy();

          $this->_action_parameters  = new Application_ArrayBag($actionParametersArray);
                    
          foreach($actionParametersArray as $key => $value)
          {
               $this->getApplicationHttpRequest()->getGet()->exchangeArray($actionParameter->getArrayCopy());
          }
                          
          if($this->_action_parameters->offsetExists('lang'))
          {
             $this->initPortalLanguage($this->_action_parameters->offsetGet('lang'));
          }
       }
       
       return $this;
    }
    
    
    /**
     * Controlla l'integrità dell'actionObject invocata
     * 
     * @param ActionObject $ActionObject
     * 
     * @return Boolean
     */
    protected function _checkActionObject($actionObject)
    {
       if(!is_object($actionObject))
       {
          return false;
       }
       else if(!is_subclass_of($actionObject,self::$_action_object_abstract))
       {
          return false;
       }
       
       return true;
    }
    
    /**
     * Indica se l'action ed il metodo formano una callable valida, escludendo gli ActionObject Nativi
     * 
     * @param Mixed  $action action
     * @param String $method [OPZIONALE] metodo, default NULL
     * 
     * @return Boolean
     */
    protected function isActionCallable($action,$method = null,$excludeNative = true)
    {        
        try
        {            
            if($excludeNative)
            {                
                $actionName = is_array($action) ? $action[0] : $action;

                $actionObjectClass = $this->_getActionObjectClassName($actionName);
                
                if($actionObjectClass && is_subclass_of($actionObjectClass,'\Interface_ActionObject'))
                {
                    return false;
                }
            }

            if(is_object($action) && ($action instanceof \Closure)) //$action è una callable valida
            {                
                return true;
            }
            else if(is_callable($action)) //$action è una callable valida
            {
                return true;
            }
            else if(is_array($action) && is_callable($action[0]))  // $action è già una callable valida
            {
                return true;
            }
            else if(is_callable(array($action,$method))) //$action e $method sono rispettivamente oggetto / metodo
            {
                return true;
            }
            else if(function_exists($action)) //Nome di una function esistente
            {
                return true;
            }
            else if(preg_match(self::ACTION_CALLABLE_STRING_REGEXP,$action)) //<object>::<method>
            {
                return true;
            }
            else if(class_exists($action) && !is_subclass_of($action, self::$_action_object_abstract))
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
}
