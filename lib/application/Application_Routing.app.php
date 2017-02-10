<?php

/**
 * Questa classe si occupa di gestire il routing per la request richiesta smistandola al relativo  ActionController e ActionObject 
 */
class Application_Routing implements Interface_ApplicationRouting
{
   
   use Trait_ObjectUtilities,Trait_Singleton;
   
   use Trait_ApplicationKernel,
           
       Trait_ApplicationConfigs,
           
       Trait_ApplicationHttpRequest;
   
   /**
    * Parametri utili per costrurire i routing nella costante APPLICATION_ROUTING
    * 
    * @var Array
    */
   private  $_ROUTING_MATCH_SHORTCUT = Array(
       
                              /** Route Value **/
       
                              '(:any)'                       => '(?<any>.*?)',
                              '(:action)'                    => '(?<action>[A-z]{2,})[\/]{0,1}',
                              '(:method)'                    => '(?<method>[A-z0-9-\_]{3,})[\/]{0,1}',
                              '(:package)'                   => '(?<package>web-[a-z\-]{3,})[\/]{0,1}',
                              '(:qsa)'                       => '\?(?<qsa>.*)',                        //QueryStringALL

                              /** Parameter Validators **/

                              '(:[string])'                  => '([A-z0-9\-\_]+)',
                              '(:[string-lower])'            => '([A-z0-9\-\_]+)',
                              '(:[string-upper])'            => '([A-z0-9\-\_]+)',
                              '(:[any])'                     => '([.*?])',
                              '(:[numeric])'                 => '([0-9]+)',
                              '(:[chars])'                   => '([A-z\-\_]+)',
                              '(:[chars-lower])'             => '([a-z\-\_]+)',
                              '(:[chars-upper])'             => '([A-Z\-\_]+)',
   );
   
   
   /**
    * Routing non compilato in chiaro dell'applicazione
    * 
    * @var Array
    */
   private  $_ROUTING_MAPS = Array(
       
       '_action'              => Array(
                                       'path'      => '(:action)',
                                       'action'    => '{action}'
                                 ),
       
       '_action_method'    => Array(
                                       'path'       => '(:action)/(:method)',
                                       'action'     => '{action}',
                                       'method'  => '{method}'
                                 ),
       
       '_action_html'         => Array(
                                       'path'      => '(:action).html',
                                       'action'    => '{action}',
                                 )
   );
   
   /**
    * Contiene le rotte compilate
    * @var Array 
    */
   protected $_ROUTING_COMPILED_MAPS = Array();
   
   
   /**
    * Parametri Attuali server
    * @var ArrayObject
    */
   protected $_server = null;
   
   /**
    * Parametri Attuali request
    * @var ArrayObject
    */
   protected $_request = null;
   
   /**
    * ArrayObject delle rotte definite
    * @var Application_RoutingData
    */
   protected $_route_data     = null;
   
   
   /**
    * Indica se il sistema è in debug, default FALSE
    * @var type 
    */
   protected $_debug = false;

   
   /**
    * Nome rotta attualmente processata
    * @var String
    */
   protected $_current_route_name = null;
   
   /**
    * Base URL
    * @var String
    */
   protected $_base_url = null;
   
   /**
    * Path Info
    * @var String
    */
   protected $_path_info = null;
   
   /**
    * Questa classe si occupa di gestire il routing per la request richiesta smistandola al relativo  ActionController e ActionObject 
    * 
    * @return Boolean
    */
   public function __construct() 
   {
       $debug = $this->getApplicationKernel()->isDebugActive();
       
       $this->setDebug($debug)
            ->initShortcuts()
            ->initRoutingMaps()
            ->registerHooks();       
   }
   
   /**
    * Restituisce le "shortcut" da utilizzare nelle rotte
    * 
    * @return Array
    */
   public function getRoutingShortcut()
   {
       return $this->_ROUTING_MATCH_SHORTCUT;
   }
   
   /**
    * Restituisce il nome dell'action di default configurata
    * 
    * @return String
    * 
    * @thrown Exception_HttpStatusException
    */
   public function getDefaultActionName()
   {
      $indexActionDefault =  $this->getConfigValue("ACTION_CNT_ACTION_DEFAULT");
      
      if(!$indexActionDefault)
      {
          $this->throwNewExceptionInternalServerError('Impossibile determinare l\'action di default, costante ACTION_CNT_ACTION_DEFAULT non definita!');
      }
      
      return $indexActionDefault;
   }
   
   /**
    * Restituisce il package di default se definito
    *
    * @return String
    * 
    * @thrown Exception_HttpStatusException 
    */
   public function getDefaultPackage()
   {
      $defaultPackage = $this->getConfigValue('APPLICATION_PACKAGE_DEFAULT'); 
//      
//      if(!$defaultPackage)
//      {
//          return self::throwNewExceptionInternalServerError('Impossibile determinare il package di default, costante APPLICATION_PACKAGE_DEFAULT non definita!');
//      }
      
      return $defaultPackage;
   }
   
   
   /**
    * Restituisce la lingua in formato small{2}
    * 
    * @return String{2}
    */
   public function getDefaultLanguage()
   {
      return $this->getApplicationKernel()->getApplicationLanguages()->getFallbackLanguage();
   }
   
   /**
    * Restituisce il dominio di default dell'applicazione
    * @return String , es: www.site.com
    */
   public function getDefaultHostName()
   {
      $domainDefault    = defined("SITE_DOMAIN")             ? SITE_DOMAIN            : self::throwNewExceptionInternalServerError('Impossibile determinare il site domain di default, costante SITE_DOMAIN non definita');
      $subdomainDefault = defined("SITE_SUBDOMAIN_DEFAULT")  ? SITE_SUBDOMAIN_DEFAULT : self::throwNewExceptionInternalServerError('Impossibile determinare il site subdomain di default, costante SITE_SUBDOMAIN_DEFAULT non definita');
      
      if(preg_match('/^'.$subdomainDefault.'/',$domainDefault))
      {
          return $domainDefault;
      }
      
      if(strlen($subdomainDefault) > 0)
      {
         return $subdomainDefault.".".$domainDefault;
      }
      
      return $domainDefault;      
   }
   
   /**
    * Restituisce l'attuale SCRIPT_NAME ottenuto dal $_SERVER
    * 
    * @return String
    */
   public function getScriptName()
   {
      $scriptName = $this->_server->offsetExists("SCRIPT_NAME") ? $this->_server->offsetGet("SCRIPT_NAME") : "/";
      return $scriptName;
   }
   
   
   /**
    * Restituisce l'arrayObject dei dati di Enviroment
    * 
    * @return ArrayObject
    */
   public function getServer()
   {
      return $this->_server;
   }
   
   
   /**
    * Imposta l'attuale $_SERVER convertendolo un arrayObject
    * 
    * @param array $server Attuale array che contiene i parametri server
    * 
    * @return \Application_Routing
    */
   public function setServer(array $server)
   {
      $this->_server    = new ArrayObject($server,ArrayObject::ARRAY_AS_PROPS);
      
      $this->_base_url  = $this->prepareBaseUrl();
      $this->_server->offsetSet('BASE_URL',$this->_base_url);

      $this->_path_info = $this->preparePathInfo();
      $this->_server->offsetSet('PATH_INFO',$this->_path_info);
      
      return $this;
   }
   
   
   public function getArgs()
   {
      return $GLOBALS['argv'];
   }
   
   /**
    * Restituisce l'arrayObject della request
    * 
    * @return ArrayObject
    */
   public function getRequest()
   {
      return $this->_request;
   }
   
   /**
    * Imposta l'attuale $_REQUEST convertendolo un arrayObject
    * 
    * @param array $request Attuale array che contiene i parametri request
    * 
    * @return \Application_Routing
    */
   public function setRequest(array $request)
   {
      $this->_request = new ArrayObject($request,ArrayObject::ARRAY_AS_PROPS);
      return $this;
   }
   
   
   /**
    * Imposta la modalita di Debug
    * 
    * @param Boolean $debug Indica il debug
    * 
    * @return \Application_Routing
    */
   public function setDebug($debug)
   {
      $this->_debug = $debug;
      return $this;
   }
   
   
   /**
    * Verifica che una rotta si esistente
    * 
    * @param type $routeName Nome della rotta. es: _any
    * 
    * @return Boolean
    */
   public function isRouteExists($routeName)
   {
      return isset($this->_ROUTING_MAPS[$routeName]);
   }

   
   /**
    * Restituisce le informazioni della rotta specificata
    * 
    * @param  String $routeName Nome rotta
    * 
    * @throws Exception
    * 
    * @return Array
    */
   public function getRouteInfo($routeName)
   {
      if(isset($this->_ROUTING_MAPS[$routeName]))
      {
         return $this->_ROUTING_MAPS[$routeName];
      }
      
      return self::throwNewException(293489273492374920,"Rotta non trovata: ".$routeName);
   }
   
   
   /**
    * Restituisce le informazioni della rotta compilata specificata
    * 
    * @param  String $routeName Nome rotta
    * 
    * @throws Exception
    * 
    * @return Array
    */
   public function getRouteCompiledInfo($routeName)
   {
      if(isset($this->_ROUTING_COMPILED_MAPS[$routeName]))
      {
         return $this->_ROUTING_COMPILED_MAPS[$routeName];
      }
      
      return self::throwNewException(902374092734892743,"Rotta compilata non trovata: ".$routeName);
   }
   
   
   /**
    * Restituisce il base url della request
    * 
    * @return String
    */
   public function getBaseUrl()
   {
       if($this->_base_url !== null)
       {
           return $this->_base_url;
       }
       
       $this->_base_url  =  $this->prepareBaseUrl();
       $this->_server->offsetSet('BASE_URL',$this->_base_url);
       
       return $this->getBaseUrl();
   }
   
   public function getCurrentUrl()
   {
       return $this->_server->offsetGet('REQUEST_URI');
   }
    
   /**
    * Restituisce il pathInfo della Request
    * 
    * @return String
    */
   public function getPathInfo()
   {
         if($this->_path_info)
        {
            return $this->_path_info;
        }
        
        $this->_path_info = $this->preparePathInfo();
        $this->_server->offsetSet('PATH_INFO',$this->_path_info);
        
        return $this->getPathInfo();
   }
   
   /**
    * Restituisce l'attuale request uri processato utile alle rotte senza QueryString
    * 
    * @return String
    */
   public function getRequestPathInfo()
   {
      return $this->getPathInfo();   
   }
   
   /**
    * Restituisce l'host name del Server
    * 
    * @return String
    */
   public function getHostName()
   {
      $serverName  =  $this->_server->offsetGet('SERVER_NAME');
      return $serverName;
   }
   
   
   /**
    * Restituisce l'host name del Server
    * 
    * @return String
    */
   public function getHttpMethod()
   {
      $httpMethod  =  $this->getApplicationKernel()->httprequest->getMethod();
      return $httpMethod;
   }
   
   /**
    * Restitusce la mappatura attualmente utilizzata
    * @return Array
    */
   public function getRoutingMaps()
   {
      return $this->_ROUTING_MAPS;
   }
   
   /**
    * Restitusce la mappatura compilata attualmente utilizzata
    * 
    * @return Array
    */
   public function getRoutingMapsCompiled()
   {
      return $this->_ROUTING_COMPILED_MAPS;
   }
   
   /**
    * Rimuove una rotta tra quelle mappate
    * 
    * @param String   $routeName Rotta
    * @param Boolean  $store     [OPZIONALE] Indica se storare il cambiamento, defaull FALSE
    * 
    * @return \Application_Routing
    */
   public function unregisterRoute($routeName,$store = false)
   {
       if(isset($this->_ROUTING_MAPS[$routeName]))
       {
          unset($this->_ROUTING_MAPS[$routeName]);
          unset($this->_ROUTING_COMPILED_MAPS[$routeName]);
       }
       
       if($store)
       {
           $this->storeCurrentRoutingCompiled()
                ->storeCurrentRoutingMaps();
       }
       
       return $this;
   }
   
   /**
    * Deregistra tutte le rotte presenti 
    * 
    * @param Boolean  $store     [OPZIONALE] Indica se storare il cambiamento, defaull FALSE
    * 
    * @return \Application_Routing
    */
   public function unregisterAllRoutes($store = false)
   {
       $this->_ROUTING_MAPS          = Array();
       $this->_ROUTING_COMPILED_MAPS = Array();
       
       if($store)
       {
           $this->storeCurrentRoutingCompiled()
                ->storeCurrentRoutingMaps();
       }
       
       return $this;
   }
   
   /**
    * Restituisce i dati dalla rotta attualmente elaborata
    * @return Application_RoutingData
    */
   public function getApplicationRoutingData()
   {
      return $this->_route_data;
   }
   
   /**
    * Setta il routing Data attuale
    * 
    * @param \Application_RoutingData $routingData data
    * 
    * @return \Application_Routing
    */
   public function setApplicationRoutingData(\Application_RoutingData $routingData)
   {
       $this->_route_data = $routingData;
       return $this;
   }

   
   /**
    * Aggiunge una mappatura alle rotte attualmente processate
    * 
    * <br>
    * <b>NB: Quando il portale è in debug le rotte non vengono salvate e quindi non si può fare redirect su una rotta creata in runtime, 
    *        a meno che questa nn sia una rotta base dichiarata nell'application-routing.php dell'applicazione </b>
    * 
    * @param String $routeName  Nome rotta
    * @param array  $routeInfo  Informazioni:
    *                               <ul>
    *                                     <li>'path'       => pseudopath o regolar expressione per matchare la rotta</li>
    *                                     <li>'action'     => valore action, può essere anche un valore matchato ( es: {action} )
    *                                     <li>'method'  => [OPZIONALE] valore method da processare, può essere anche un valore matchato ( es: {method} )
    *                                     <li>'controller' => [OPZIONALE] controller da utilizzare, es: html, ajax
    *                                     <li>'host'       => [OPZIONALE] valore host per la rotta
    *                               </ul>
    * @param Boolean $prepend   [OPZIONALE] Indica se appendere in testa o appendere la rotta alla lista delle rotte presenti, default TRUE (prepende)
    * @param Boolean $store     [OPZIONALE] Indica se le rotte sono da storare in cache
    * 
    * @throws Exception
    * 
    * @return \Application_Routing
    */
   public function addRoutingMap($routeName,array $routeInfo,$prepend = true,$store = false)
   {
      if($prepend)
      {
         if(isset($this->_ROUTING_MAPS[$routeName]))
         {
             $this->_ROUTING_MAPS[$routeName] = $routeInfo;
         }
         
         $this->_ROUTING_MAPS = array_merge(array($routeName => $routeInfo),$this->_ROUTING_MAPS);
      }
      else
      {
         $this->_ROUTING_MAPS = array_merge($this->_ROUTING_MAPS,array($routeName => $routeInfo));
      }

      if(!$this->validateRoute($routeName))
      {
         return $this->throwNewException(234234290529, 'La rotta '.$routeName.' non è valida!');
      }
      
      if(!$this->compileRoute($routeName,$prepend))
      {
         return $this->throwNewException(9861461269814, 'Non è possibile compilare la rotta: '.$routeName);
      }
      
      if($store)
      {
         $this->storeCurrentRoutingMaps()
              ->storeCurrentRoutingCompiled();
      }
      
      return $this;
   }
   
   
   /**
    * Registra una lista di rotte
    * 
    * @param Array   $routingMaps Rotte
    * @param Boolean $prepend     Indica se appendere/prependere le rotte, default TRUE (prepende)
    * @param Boolean $storeCache  Indica se storare il cache le configurazioni, default TRUE
    * 
    * @return \Application_Routing
    */
   public function addRoutingMaps(array $routingMaps,$prepend = true,$storeCache = false)
   {
       if(count($routingMaps) > 0)
       {
           
          foreach($routingMaps as $routeName => $routeInfo)
          {
             $package = isset($routeInfo['package']) ? $routeInfo['package'] : $this->getDefaultPackage();
             $routeInfo["package"] = $package;
             $this->addRoutingMap($routeName, $routeInfo,$prepend,$storeCache);
          }
       }
       
       return $this;
   }
   
   
   /**
    * Registra le rotte di un package ricercando le configurazioni su file indicati dallo stesso package
    * 
    * @param Abstract_Package $package Instanza di un package
    * 
    * @return boolean
    */
   public function addRoutingMapForPackage(Abstract_Package $package)
   {     
      if($this->isRoutingChange($package->getName()))
      {  
         $routes   = $this->getApplicationConfigs()->getParseConfigsForPackage(Application_Routing::ROUTING_CONFIG_MAP_FILE_NAME,$package->getName(),$package->getConfigsFileExtension());
         
         $routes   = array_reverse($routes);
                  
         foreach($routes as $routeName => $routeInfo)
         {
             
            if(isset($routeInfo["package"]) && strtolower($package->getName()) != strtolower($routeInfo["package"]))
            {
                return $this->throwNewException(90850927239083248, 'La rotta '.$routeName.' presenta un package non valido "'.$routeInfo["package"].'" perchè la rotta è definita nel package "'.$package->getName().'" ');
            }
            
            $routeInfo["package"] = $package->getName();
            $this->addRoutingMap($routeName, $routeInfo,true,true);
         }
                  
         return true;
      }
       
      return false;   
   }
   
   /**
    * Aggiunge un nuovo shortcut alla lista degli shortcuts disponibili da usare nei path delle rotte
    * 
    * @param String $shortcutValue     identificativo shortcut, es: (:myshortcut)
    * @param String $shortcurRegexp    regolar expression con la quale verra usato questo shortcut
    * 
    * @return Boolean
    * 
    * @throws Exception
    */
   public  function addRoutingShortcut($shortcutValue,$shortcurRegexp)
   {   
      if(!is_string($shortcutValue) || strlen($shortcutValue)  == 0){
         return self::throwNewException(982384682364864,"Il nome dello shortcut fornito non è valido");
      }
      
      if(@preg_match($shortcurRegexp,self::ROUTING_TEST_REGEXPR_STRING)===false){
         return self::throwNewException(82382934923002340,"Shortcut Regexpr ".$shortcurRegexp." non valida, error: ".preg_last_error());
      }
      
      return $this->_ROUTING_MATCH_SHORTCUT[$shortcutValue] = $shortcurRegexp;
   }
   
   
   /**
    * Elabora la request attuale ed elabora un Application_RoutingData
    * 
    * @param \Application_Routing
    */
   public function elaborateRequestRouting()
   {
      $routeData = new Application_RoutingData();
      
      if(is_array($this->getRoutingMapsCompiled()) && count($this->getRoutingMapsCompiled()) > 0 )
      {                  
         $routeFind        = false;
         
         $currentPathInfo   = $this->getPathInfo();
         $currentHost       = $this->getHostName();
         
         /**
          * Rotte compilate che sono di interesse per il dominio attuale visitato
          */
         $routeCompiledByDomain = array_filter($this->getRoutingMapsCompiled(),function($v) use($currentHost){ 
              $hostPattern = isset($v['host']) ? $v['host'] : null;
              if($hostPattern)
              {
                 if($hostPattern == $currentHost)
                 {
                     return true;
                 }
              }
              return false;
         });
         
         /**
          * Rotte non di interesse del dominio, sono applicabili a tutti gli hosts
          */
         $routeCompiledNotForDomain = array_filter($this->getRoutingMapsCompiled(),function($v) use($currentHost){ 
              $hostPattern = isset($v['host']) ? $v['host'] : null;
              if(!$hostPattern || !preg_match($hostPattern,$currentHost,$matchesHost))
              { 
                 return true;
              }
              return false;
         });
                  
         $routingMapsCompiledSorted = array_merge($routeCompiledByDomain,$this->getRoutingMapsCompiled(),$routeCompiledNotForDomain);
         
         foreach($routingMapsCompiledSorted as $routeName => $routeInfo)
         {
            $hostMatched      = true;
            
            $matchRouteInfo   = $this->_current_route_name ? $this->getRouteInfo($this->_current_route_name) : null;
            
            if(!$routeFind)
            {
               $matches      = Array();
               $matchesHost  = Array();
               
               $routePattern = $routeInfo["path"];
               $hostPattern  = isset($routeInfo["host"])       ? $routeInfo["host"]    : false;
               $params       = isset($routeInfo["params"])     ? $routeInfo["params"]  : Array();           
               
               if($hostPattern)
               {
                  if(preg_match($hostPattern,$currentHost,$matchesHost))
                  {
                     $hostMatched  = true;
                  }
                  else
                  {
                     $hostMatched  = false;
                  }
               }
               else
               {
                  $hostMatched     = true;
               }
                  
               $httpMethod = $this->getApplicationKernel()->httprequest->getMethod();

               if($hostMatched && preg_match($routePattern,$currentPathInfo,$matches) && (!isset($routeInfo['httpMethod']) || $routeInfo['httpMethod'] == $httpMethod))
               {
                  $controllerType    = isset($routeInfo["controller"])     ? $routeInfo["controller"]    : Application_Kernel::CONTROLLER_TYPE_HTML;
                  $defaults          = isset($routeInfo["defaults"])       ? $this->getAdaptRouteDefaults($routeInfo["defaults"])     : array();
                  $action            = isset($routeInfo["action"])         ? $routeInfo["action"]        : false;
                  $method            = isset($routeInfo["method"])         ? $routeInfo["method"]        : false;
                  $package           = isset($routeInfo["package"])        ? $routeInfo["package"]       : null;
                  $allowFrom         = isset($routeInfo["allow_from"])     ? $routeInfo["allow_from"]    : "*";
                  $debug             = isset($routeInfo["debug"])          ? $routeInfo["debug"]         : null;  

                  $this->_current_route_name = $routeName;
                  $routeInfo["name"]         = $routeName;
                  
                  $isKernelDebug     = $this->getApplicationKernel()->isDebugActive();
                  
                  /**
                   * Questa rotta è abilitata all'attuale ambiente di lavoro
                   */
                  if(is_null($debug) || $isKernelDebug == $debug)
                  {  
                     $routeParameters   = $matches;
                     $hostParameters    = $matchesHost;
                     
                     $matchesParameters = array_merge($routeParameters,$hostParameters);
                     
                     /**
                      * Controllo accesso alla rotta tramite IP
                      */
                     if($allowFrom)
                     {
                        if($allowFrom != "*" && $allowFrom != $this->getUtility()->getIP())
                        {
                           return self::throwNewExceptionHttpStatus(403);
                        }
                     }
                     
                     /**
                      * Elaboro il package, potrebbe essere parametrizzato o statico definito nella rotta
                      */
                     if($package)
                     {
                        $package     = $this->getRouteParameterValue("package",$routeInfo,$matchesParameters);
                     }
                     else if($package!==false)
                     {
                        $package     = $this->getDefaultPackage();
                     }
                     
                     $controllerType      = $this->getRouteParameterValue("controller",$routeInfo,$matchesParameters,$controllerType);

                     if(is_array($defaults) && count($defaults) > 0)
                     {
                        $matchesParameters = array_extend($defaults,$matchesParameters);
                        
                        foreach($defaults as $key => $value)
                        {
                            if($value == self::ROUTE_DEFAULT_PARAMETER_EMPTY)
                            {
                                $defaults[$key] = null;
                            }
                        }
                        
                        foreach($matchesParameters as $key => $value)
                        {
                            if(empty($value) && isset($defaults[$key]))
                            {
                                $value = $defaults[$key];
                            }
                            
                            $matchesParameters[$key] = $value;
                        }
                     }
                     
                     if(is_array($params) && count($params) > 0)
                     {
                        foreach($params as $key => $value)
                        {
                           $params[$key]  = $this->getRouteParameterValue($key,$params,$matchesParameters);
                        }
                     }
                     
                     /**
                      * Elaboro l'action e la method da processare
                      */
                     $routeActionInfo = $this->resolveRouteAction($routeName,$matchesParameters);
                     $action          = $routeActionInfo["action"];
                     $method          = $routeActionInfo["method"];
                                          
                     if(is_numeric($action) && $action == self::HTTP_ERROR_REDIRECT)
                     {
                        if(!isset($routeInfo["where"]))
                        {
                           if($this->getApplicationKernel()->isDebugActive())
                           {
                               return self::throwNewException(932209835789236034,'Impossibile elaborare la rotta: '.$routeName.', questa rotta tenta di effettuare il redirect ma manca il parametro "where" ');
                           }
                           else
                           {
                               return self::throwNewExceptionInternalServerError();
                           }
                        } 

                        $params["url"] = $this->generateUrl($routeInfo["where"]);
                     }
                         
                     $routeData->setRouteName($routeName)
                               ->setAction($action)
                               ->setMethod($method)
                               ->setParams($params)
                               ->setDefaults($defaults)
                               ->setPackage($package);
                                          
                     if($controllerType)
                     {
                        $routeData->setControllerType($controllerType);
                     }

                     $routeFind     = true;
                  }
               }
            }            
        }
      }
      
      if(!$routeData->getRouteName(false))
      {
         $routeData->setAction(self::HTTP_ERROR_PAGE_NOT_FOUND);
      }

      $this->_route_data = $routeData;
      
      return $this;
   }
   
   /**
    * Genera l'url sfruttando il routing o il pseudoRoute
    * 
    * @param  String                   $where      Nome della rotta, pseudo-route o url
    *                                              <ul>
    *                                                <li>Route:           _example_route         </li>
    *                                                <li>PseudoRoute:     action/method       </li>
    *                                                <li>Url:             http://....            </li>
    *                                             </ul>
    * 
    * @param  Array                    $routeData  [OPZIONALE] Array contenente le informazioni utili per generare la rotta, passato al costruttore dell'oggetto Application_RoutingData, passati eventualmente in queryString
    * @param  Boolean                  $absolute   [OPZIONALE] Indica se l'url dovrà essere assoluto, default FALSE
    * @return String
    * 
    * @throws Exception
    */
   public function generateUrl($where,array $routeData = Array(),$absolute = false)
   {
       if(strlen(trim($where)) == 0)
       {
          return self::throwNewExceptionInternalServerError('Impossibile generare un url, Rotta non valida!');
       }
              
       $appRequest         = $this->getApplicationHttpRequest();
       $protocol           = $appRequest->getProtocol();
       $host               = $appRequest->getHost();
       $path               = $appRequest->getBaseUrl();
       $queryParameters    = $appRequest->getGet()->getArrayCopy();
       $defaults = $params = array();
             
       $url                = false;
       $chekUrlGenerate    = false;
       $redirect           = false;

       $routeName          = $where;
       
       $routeInfo          = new Application_RoutingData($routeData);
       $routeInfo->setParams($routeData);
          
       if($this->isValidRouteName($where))           //Rotta + parametri
       {
          $routeName   = $where;
       }
       else if($this->isValidPseudoRoute($where))   //Pseudorotta "<action>/<method>"
       {
          $routeTransformData = $this->getTransformPseudoRouteToRouteInfo($where);
          
          $routeName          = $routeTransformData["routeName"];
          $routeInfo->setAction(isset($routeTransformData["action"])       ? $routeTransformData["action"]    : "") 
                    ->setMethod(isset($routeTransformData["method"]) ? $routeTransformData["method"] : "");
          
          $chekUrlGenerate = true;
       }
       else
       {
           $chekUrlGenerate = false;
           $url             = $where;
       }
         
       
       if(!$url)
       {
            $routeDefaultInfo       = $this->getRouteInfo($routeName);
            
            if(!$routeDefaultInfo)
            {
               return self::throwNewExceptionInternalServerError("Impossibile determinare l\'url per la rotta: ".$routeName);
            }
            
            $this->_current_route_name = $routeName;
            
            $path        = $routeDefaultInfo["path"];
            $protocol    = $routeInfo->offsetExists("protocol")   ? $routeInfo->offsetGet("protocol")    : (isset($routeDefaultInfo["protocol"])  ? $routeDefaultInfo["protocol"]  : $protocol);
            $host        = $routeInfo->offsetExists("host")       ? $routeInfo->offsetGet("host")        : (isset($routeDefaultInfo["host"])      ? $routeDefaultInfo["host"]      : $this->getDefaultHostName());
            $package     = $routeInfo->offsetExists("package")    ? $routeInfo->offsetGet("package")     : (isset($routeDefaultInfo["package"])   ? $routeDefaultInfo["package"]   : $this->getDefaultPackage());
            $action      = $routeInfo->offsetExists("action")     ? $routeInfo->offsetGet("action")      : (isset($routeDefaultInfo["action"])    ? $routeDefaultInfo["action"]    : $this->getDefaultActionName());
            $method      = $routeInfo->offsetExists("method")     ? $routeInfo->offsetGet("method")      : (isset($routeDefaultInfo["method"]) ? $routeDefaultInfo["method"] : "");
            $params      = $routeInfo->offsetExists("params")     ? $routeInfo->offsetGet("params")      : (isset($routeDefaultInfo["params"])    ? $routeDefaultInfo["params"]    : Array());

            $redirect    = empty($routeDefaultInfo["where"])   ? false  : true;
            
            //Parametri configurati nelle rotte
            $routeParams = isset($routeDefaultInfo["params"]) && is_array($routeDefaultInfo["params"]) ? $routeDefaultInfo["params"]   : false;
            
            //Valori di default ai parametri delle rotte
            $defaults    = isset($routeDefaultInfo["defaults"])                                        ? $this->getAdaptRouteDefaults($routeDefaultInfo["defaults"]) : false;
            
            if($this->getDefaultActionName() == $action)
            {
               $action = "";
            }

            /**
             * Pulisco i parameter e quelli di default da tutti gli attributi già sostiuti nella rotta
             */
            foreach($this->_ROUTING_MATCH_SHORTCUT as $key => $value)
            {
               $shortcutName       = preg_replace("/\(\:([a-z\_-]+)\)/","$1",$key);
               $variable           = isset($$shortcutName) ? $shortcutName : false;
              
               if($variable!==false && isset($$variable))
               {
                  if(preg_match("/".$variable."/",$path) || preg_match("/".$variable."/",$host))
                  {
                     $path        = str_replace($key,$$variable,$path);
                     $host        = str_replace($key,$$variable,$host);
                     
                     if(isset($params[$variable]))
                     {
                        unset($params[$variable]);
                     }
                     
                     if(isset($routeParams[$variable]))
                     {
                        unset($routeParams[$variable]);
                     }   
                  }
               }
            }
 
            $queryParameters    = Array();
            
            $params             = ($params instanceof ArrayObject) ? $params->getArrayCopy() : $params;

            if($defaults)
            {
               if($routeParams)
               {
                    foreach($defaults as $key => $value)
                    {
                        if(array_key_exists($key, $routeParams) && empty($params[$key]))
                        {
                            $params[$key] = $defaults[$key] == self::ROUTE_DEFAULT_PARAMETER_EMPTY ? null : $defaults[$key];
                        }
                    }
               }
            }
            
            
            if($routeParams !== false)
            {               
               foreach($params as $key => $value)
               {
                  $paramFind = false;
                  
                  /**
                   * I dati passati alla rotta hanno il parametro richiesto che è uno shortcut valido, quindi deve essere elaborato
                   */
                  if(isset($routeParams[$key]))
                  {
                     $paramFind = true;
                     unset($routeParams[$key]);
                  }

                  if(preg_match("/\{".$key."\}/",$path,$matches))
                  {
                     $paramFind = true;
                     $path      = preg_replace("/\{".$key."\}/",$value,$path,1);
                  }
                  
                  if(preg_match("/\{".$key."\}/",$host,$matches))
                  {
                     $paramFind = true;
                     $host      = preg_replace("/\{".$key."\}/",$value,$host,1);
                  }
                  
                  /**
                   * Questo parametro è in più, Appendo come queryString
                   */
                  if(!$paramFind)
                  {  
                     $queryParameters[$key]=$value;
                  }
               }
            }
            else
            {
               $queryParameters = $params;
            }

            /**
             * Questa rotta aspettava un parametro con uno shortcut associato, quindi se non è stato eliminato dall'array temporaneo significa che i dati
             * passati per generare l'url non sono sufficenti, e quindi lo comunico con una eccezione
             * 
             */
            if(is_array($routeParams) && count($routeParams) > 0)
            {
               return self::throwNewException(12831823981992,'La rotta '.$routeName.' richiede i seguenti parametri che non sono stati forniti: '.print_r($routeParams,true));
            }
            
            $queryParameters    = count($queryParameters)>0 ? "?".http_build_query($queryParameters) : "";
            
            $baseUrl = $this->getBaseUrl();
            
            if($path[0] != "/")
            {
                $path = "/".$path;
            }
            
            if($path != '/')
            {
                $lastChar = $path[strlen($path)-1];
                if($lastChar == '/'){
                    $path = substr($path,0,strlen($path)-1);
                }
            }
                                    
            $url   = $baseUrl . $path . $queryParameters;
       }
              
       if($url && $absolute)
       {
          $baseUrl = $this->getBaseUrl();
          
          if($path[0] != "/")
          {
                $path = "/".$path;
          }
          
          $url = $protocol. '://' . $host . $baseUrl . $path . $queryParameters;
       }
            
       $appHooks = $this->getApplicationKernel()->getApplicationHooks();
       
       if($appHooks->isEnable())
       {
           $urlData = $appHooks->processAll(\Interface_HooksType::HOOK_TYPE_ROUTING_POST_URL,array(
               
               'url'             => $url,
               'protocol'        => $protocol,
               'host'            => $host,
               'path'            => $path,
               'query'           => $queryParameters,
               'params'          => $params,
               'defaults'        => $defaults,
               'route'           => $this->getRouteInfo($this->_current_route_name),               
               'chekUrlGenerate' => $chekUrlGenerate,
               
           ))->getResponseData()->getData();
           
           
           $chekUrlGenerate = $urlData['chekUrlGenerate'];
           $url             = $urlData['url'];
       }
       
       
       if(!$url)
       {
          return self::throwNewException(891287419295293959235,'Impossibile generare un url valido per: '.$where);
       }
              
       if(!$redirect)
       {
          /**
           * Controllo che l'url generato matchi almeno una rotta
           */
          if($chekUrlGenerate && !$this->isValidUrl($url))
          {
             return self::throwNewException(234802342093438383,'Il parametro $where fornito al metodo ha generato un url invalido: '.$url);
          }
       }
       
       return $url;
   }
   
      
   /**
    * Indica se l'url è valido, andando a testare l'url fornito con le rotte compilate
    * <br>
    * <b>Attenzione! viene esclusa dal controllo la rotta "_any"</b>
    * 
    * @param String $url Url
    * 
    * @return Boolean
    */
   public function isValidUrl($requestUri)
   {
      $parseUrl   = parse_url($requestUri);
      
      if($parseUrl)
      {         
         $path   = isset($parseUrl["path"])   ? $parseUrl["path"]   : "/";
         $host   = isset($parseUrl["host"])   ? $parseUrl["host"]   : false;
         $schema = isset($parseUrl["schema"]) ? $parseUrl["schema"] : false;
         
         $allRouteCompiled = $this->_ROUTING_COMPILED_MAPS;
         
         if(is_array($allRouteCompiled) && count($allRouteCompiled) > 0)
         {
            foreach($allRouteCompiled as $routeName => $routeInfo)
            {
               if($routeName != "_any")
               {
                  $routePath = $routeInfo["path"];
                  $routeHost = isset($routeInfo["host"]) ? $routeInfo["host"] : false;
                  $path      = str_replace($this->getBaseUrl(),"",$path);
                  
                  if($routePath && preg_match($routePath,$path))
                  {
                     if($host && $routeHost)
                     {
                        if(preg_match($routeHost, $host)){
                           return true;
                        }
                     }
                     else
                     {
                        return true;
                     }
                  }
                  
                  if(isset($routeInfo["redirect"]) && $routeInfo["redirect"] == $requestUri)
                  {
                     return true;
                  }
                  
               }
            }
         }
      }
      
      return false;
   }
   
   /**
    * Verifica che la rotta abbia un nome valido
    * 
    * @param String $name Nome da validare
    * 
    * @return Boolean
    */
   public static function isValidRouteName($name)
   {
      return preg_match(self::ROUTING_NAME_PATTERN,$name);
   }
   
   /**
    * Verifica che la pseudo rotta si valida
    * 
    * @param String $pseudoRoute  Pseudorotta, es: action/method
    * 
    * @return Boolean
    */
   public function isValidPseudoRoute($pseudoRoute)
   {
      return $this->getTransformPseudoRouteToRouteInfo($pseudoRoute) ? true : false;
   }
   
   /**
    * Controlla che non sia cambiato nessun file di routing presente nelle configurazioni o nei singoli package
    * 
    * <br>
    * <b>In debug restituisce sempre TRUE</b>
    * 
    * @param String $package Package Indica per quale package controllare i cambiamenti delle rotte, default NULL (tutti)
    * 
    * @return boolean
    */
   public function isRoutingChange($package = null)
   {
      if(strlen($package) == 0)
      {
         return $this->getApplicationConfigs()->isConfigsChangeForAlmostOnePackage(self::ROUTING_CONFIG_MAP_FILE_NAME,true);
      }
      
      return $this->getApplicationConfigs()->isConfigsChangeForPackage(self::ROUTING_CONFIG_MAP_FILE_NAME,$package,true);
   }
   
   /**
    * Restituisce i dati matchati dalle pseudorotte, convertendola in una rotta _action o _action_method, con 
    * la quale è possibile scrivere velocemente degli url, senza dover creare una vera e propria rotta dedicata.
    * 
    * 
    * @param String $pseudoRoute pseudorotta
    * 
    * @return Array
    */
   public function getTransformPseudoRouteToRouteInfo($pseudoRoute)
   {
      $routeTransformData = false;
      
      try
      {
         if(preg_match("/^([a-z0-9_-]+)\/([a-z0-9_-]+)\/{0,}$/",$pseudoRoute,$matches))     //questa pseudorotta è <action>/<method>
         { 
            $routeTransformData              = $this->getRouteInfo('_action_method');
            $routeTransformData["routeName"] = "_action_method";
            $routeTransformData["action"]    = $matches[1];
            $routeTransformData["method"] = $matches[2];
         }
         else if(preg_match("/^([a-z0-9_-]+)$/",$pseudoRoute,$matches))          //questa pseudorotta è <action>
         {
            $routeTransformData              = $this->getRouteInfo('_action');
            $routeTransformData["routeName"] = "_action";
            $routeTransformData["action"]    = $matches[1];
         }      
      }
      catch(Exception $e)
      {
         return false;
      }
      
      return $routeTransformData;
   }
   

   /**
    * Elabora il nome dell'Action da elaborare da passare all'ActionController in base alla rotta indicata
    * 
    * @param String $routeName          Nome della rotta
    * @param array  $routeParameters    Parametri da sostituire per costruire l'action da invocare
    * 
    * @return Array  Array associativo "action" e "method"
    * 
    * @throws \Exception Se action invalida
    */
   public function resolveRouteAction($routeName,array $routeParameters = array())
   {
      $routeInfo   = $this->getRouteInfo($routeName);
      
      $routeAction = $routeInfo['action'];
      
      $action    = null;    //Action ottenuta dal routing
      $method    = null;    //Method ottenuta dal routing
      
      //Sposta il resolve dell'aciion nell'actionController!
      
      /**
       * Callable
       */
      if(is_array($routeAction))
      {
         if(count($routeAction) > 0)
         {
            if(isset($routeAction[0]) && isset($routeAction[1]))
            {
                $action = $routeAction[0];
                $method = $routeAction[1]; 
            }
         }
         else
         {
            return  $this->throwNewException(3928902736506895903, 'Questa rotta: '.$routeName.' fornisce un valore di "action" non valido: '.print_r($routeAction,true));
         }
      }
      else if(is_numeric($routeAction))      //Http Status
      {
          if($routeAction > 0 )
          {
             $action = $routeAction;
          }
          else
          {
             return  $this->throwNewException(289374824358982734, 'Questa rotta: '.$routeName.' fornisce un valore di "action" che non è uno status HTTP valido:'.$routeAction);
          }
      }
      else if(is_string($routeAction) && preg_match(self::ACTION_CALLABLE_STRING_PATTERN,$routeAction,$matches)) //Action_test::doHello
      {
          $action    = $this->replaceStringValues($matches[1], $routeParameters);
          $method = $this->replaceStringValues($matches[2], $routeParameters);
      }
      else if(is_string($routeAction))
      {
          $action    = $this->replaceStringValues($routeInfo["action"], $routeParameters);
          
          if(isset($routeInfo["method"]))
          {
             $method = $this->replaceStringValues($routeInfo["method"], $routeParameters);
          }
      }      
      else
      {
          $action = $routeAction;
      }
      
      return array(
                   "action"  => $action,
                   "method"  => $method
             );
   }
   
   
   /**
    * Prepares the base URL.
    *
    * @return string
    */
   protected function prepareBaseUrl()
   {       
        if($this->getApplicationKernel()->isServerApiCLI()){
            return '';
        }
        
        $filename = $this->_server->offsetExists('SCRIPT_FILENAME') ? basename($this->_server->offsetGet('SCRIPT_FILENAME')) : false;
        if ($filename && basename($this->_server->offsetGet('SCRIPT_NAME')) === $filename) {
            $baseUrl = $this->_server->offsetGet('SCRIPT_NAME');
        } elseif ($filename && basename($this->_server->offsetGet('PHP_SELF')) === $filename) {
            $baseUrl = $this->_server->offsetGet('PHP_SELF');
        } elseif ($filename && basename($this->_server->offsetGet('ORIG_SCRIPT_NAME')) === $filename) {
            $baseUrl = $this->_server->offsetGet('ORIG_SCRIPT_NAME'); // 1and1 shared hosting compatibility
        } else {
            // Backtrack up the script_filename to find the portion matching
            // php_self
            $path = $this->_server->offsetGet('PHP_SELF');
            $file = $this->_server->offsetGet('SCRIPT_FILENAME');
            $segs = explode('/', trim($file, '/'));
            $segs = array_reverse($segs);
            $index = 0;
            $last = count($segs);
            $baseUrl = '';
            do {
                $seg = $segs[$index];
                $baseUrl = '/'.$seg.$baseUrl;
                ++$index;
            } while ($last > $index && (false !== $pos = strpos($path, $baseUrl)) && 0 != $pos);
        }
        // Does the baseUrl have anything in common with the request_uri?
        $requestUri = $this->_server->offsetExists('REQUEST_URI') ? $this->_server->offsetGet('REQUEST_URI') : false;
        
        if(!$requestUri)
        {
            return '/';
        }
        
        if ($baseUrl && false !== $prefix = $this->getUrlencodedPrefix($requestUri, $baseUrl)) {
            // full $baseUrl matches
            return $prefix;
        }
       
        if ($baseUrl && false !== $prefix = $this->getUrlencodedPrefix($requestUri, dirname($baseUrl).'/')) {
            // directory portion of $baseUrl matches
            return rtrim($prefix, '/');
        }
        $truncatedRequestUri = $requestUri;
        if (false !== $pos = strpos($requestUri, '?')) {
            $truncatedRequestUri = substr($requestUri, 0, $pos);
        }
        $basename = basename($baseUrl);
        if (empty($basename) || !strpos(rawurldecode($truncatedRequestUri), $basename)) {
            // no match whatsoever; set it blank
            return '';
        }
        // If using mod_rewrite or ISAPI_Rewrite strip the script filename
        // out of baseUrl. $pos !== 0 makes sure it is not matching a value
        // from PATH_INFO or QUERY_STRING
        if (strlen($requestUri) >= strlen($baseUrl) && (false !== $pos = strpos($requestUri, $baseUrl)) && $pos !== 0) {
            $baseUrl = substr($requestUri, 0, $pos + strlen($baseUrl));
        }
        return rtrim($baseUrl, '/');
    }
    
   /**
    * Returns the prefix as encoded in the string when the string starts with
    * the given prefix, false otherwise.
    *
    * @param string $string The urlencoded string
    * @param string $prefix The prefix not encoded
    *
    * @return string|false The prefix as it is encoded in $string, or false
    */
    protected function getUrlencodedPrefix($string, $prefix)
    {
         if (0 !== strpos(rawurldecode($string), $prefix)) {
            return false;
          }

          $len = strlen($prefix);

          if (preg_match(sprintf('#^(%%[[:xdigit:]]{2}|.){%d}#', $len), $string, $match)) {
            return $match[0];
          }

          return false;
    }
    
   /**
    * Prepara il pathInfo
    * 
    * @return string
    */
   protected function preparePathInfo()
   {
        $baseUrl = $this->getBaseUrl();
        
        if(!$this->_server->offsetExists('REQUEST_URI'))
        {
            return '/';
        }
        
        if (null === ($requestUri = $this->_server->offsetGet('REQUEST_URI'))) {
            return '/';
        }
        
        $pathInfo = '/';
        // Remove the query string from REQUEST_URI
        if ($pos = strpos($requestUri, '?')) {
            $requestUri = substr($requestUri, 0, $pos);
        }
        if (null !== $baseUrl && false === $pathInfo = substr($requestUri, strlen($baseUrl))) {
            // If substr() returns false then PATH_INFO is set to an empty string
            return '/';
        } elseif (null === $baseUrl) {
            return $requestUri;
        }
        return (string) $pathInfo;
   }
    
    
   /**
    * Ricerca il parametro della rotta specifico
    * 
    * @param String $parameter         Parametro da cercare
    * @param array  $routeInfo         Parametri forniti dalla configurazione delle rotte compilate
    * @param array  $routeParameters   Parametri elaborati dalla rotta
    * @param Mixed  $defaultValue      [OPZIONALE] Valore restituito di default, default self::ROUTE_DEFAULT_PARAMETER_EMPTY
    * @return String
    * 
    * @throws Exception
    */
   private function getRouteParameterValue($parameter,array $routeInfo,array $routeParameters,$defaultValue = self::ROUTE_DEFAULT_PARAMETER_EMPTY)
   { 
      if(!isset($routeInfo[$parameter]))
      {
         if(!$defaultValue)
         {
            return self::throwNewException(368723790346890453,"Imposibile determinare il parametro cercato nella rotta ".$routeInfo["name"].", il parametro ".$parameter." non è disponibile. Parametri disponibili ".print_r($routeInfo,true));
         }
         
         return $defaultValue;
      }
      
      $parameterValue = false;

      if(!isset($routeParameters[$parameter]) && isset($routeParameters[$parameter]))
      {
          $parameterValue = $defaultValue;
      }
      
      if(strstr($routeInfo[$parameter],"{{$parameter}}") !== false)
      {
           $parameterValue = str_replace("{{$parameter}}",$routeParameters[$parameter],$routeInfo[$parameter]);
      }
      else if(is_array($routeParameters) && count($routeParameters) > 0 )
      {
            foreach($routeInfo as $name => $value)
            {
                if(is_string($name))
                {
                   if($parameter == $name)
                   {
                      if(preg_match("/\{[a-z\_]+\}/",$value,$matches) > 0)
                      {
                         $slugName = $matches[1];
                         if(isset($routeParameters[$slugName]))
                         {
                            $parameterValue = $routeParameters[$slugName];
                         }
                      }
                      else if(isset($routeParameters[$name]))
                      {
                          $parameterValue = $routeParameters[$name];
                      }
                   }
                }
            }
      }
      
      if($parameterValue == self::ROUTE_DEFAULT_PARAMETER_EMPTY)
      {
          $parameterValue = null;
      }
      else if($parameterValue === false)
      {   
         if(isset($routeInfo[$parameter]))
         {
             $parameterValue = $routeInfo[$parameter];
         }
          
         if(!$parameterValue)
         {
             return $this->throwNewException(4093475903475,'Questa rotta '.$this->_current_route_name." ricerca il parametro '".$parameter."' che non è presente nella configurazione e in nessun parameters");   
         }
      }
      
      return urldecode($parameterValue);
   }
   
   /**
    * Sostituisce i valori "slug" {...} in RouteString in base ai parameters
    * 
    * @param String $routeString RouteString
    * @param array  $parameters  Parametri matchati dal routing
    * 
    * @return String
    */
   private function replaceStringValues($routeString,array $parameters)
   {
        $routeString = preg_replace_callback('/\{(.*)\}/',function($matches) use ($parameters){
            
            $routeString = "";
            
            foreach($matches as $key => $value)
            {
                if(isset($parameters[$value]))
                {
                    $routeString = str_replace($value,$parameters[$value],$value);
                }
            }
            
            return $routeString;
            
        },$routeString);
        
        return $routeString;
   }
   
   
   /**
    * Inizializzazione routing shortuct validator
    * 
    * @return Application_Routing
    */
   private function initShortcuts()
   {
      $routingShortcuts = defined("APPLICATION_ROUTING_SHORTCUTS") ? unserialize(APPLICATION_ROUTING_SHORTCUTS) : Array();
      $this->_ROUTING_MATCH_SHORTCUT = array_merge($this->_ROUTING_MATCH_SHORTCUT,$routingShortcuts);
            
      return $this;
   }
   
   
   /**
    * Inizializza il routing controllando l'esistenza delle rotte in cache, se non trovate carica le rotte di default nella costante  <APPLICATION_ROUTING>
    * 
    * @return Application_Routing
    */
   private function initRoutingMaps()
   {
      $cachedRoutes = $this->getRoutesMapsFromCache();
      
      /**
       * Mappo le rotte orgininali e le storo in cache
       */
      $this->_ROUTING_MAPS  =  $cachedRoutes !== false ? $cachedRoutes : (defined("APPLICATION_ROUTING") ? unserialize(APPLICATION_ROUTING) : $this->_ROUTING_MAPS);

      /**
       * Ricerco le rotte compilate, se non esistono, le compilo e le metto in cache su file
       */
      if(!$this->getRoutesCompiledFromCache())
      {          
         if($this->validateAllRoutes())
         {
            if($this->compileAllRoutes())
            {
               $this->getApplicationConfigs()->storeConfigsCache(self::ROUTING_CONFIG_COMPILED_FILE_NAME,$this->getRoutingMapsCompiled());
            }
            else
            {
               return self::throwNewException(234092359767456,"Errore durante la compilazione delle rotte!");
            }
         }
         else
         {
            return self::throwNewException(445455432,"Errore durante la validazione delle rotte!");
         }
      }
      
      if(!$cachedRoutes)
      {
         $this->getApplicationConfigs()->storeConfigsCache(self::ROUTING_CONFIG_MAP_FILE_NAME,$this->_ROUTING_MAPS);
      }
      
      $routingCompiledCache = $this->getRoutesCompiledFromCache();
      
      if(is_array($routingCompiledCache) && count($routingCompiledCache) > 0)
      {
         $this->_ROUTING_COMPILED_MAPS = $routingCompiledCache;
      }
      
      return $this;
   }
   
   /**
    * Valida tutte le rotte attualmente caricate
    * 
    * @return Boolean
    * 
    * @throws Exception
    */
   private function validateAllRoutes()
   {
      $routingMaps = $this->getRoutingMaps();
      
      if(is_array($routingMaps) && count($routingMaps) > 0)
      {
         foreach($routingMaps as $routeName => $routeInfo)
         {
            if(!$this->validateRoute($routeName)){
               return false;
            }
         }
         
         return true;
      }
      
      return self::throwNewException(923849283498,"Nessuna rotta definita! dove andrà a finire questa chiamata??? :)");
   }
   
   /**
    * Valida la rotta
    * 
    * @param String $routeName Nome della rotta
    * 
    * @return Boolean
    * 
    * @throws Exception
    */
   private function validateRoute($routeName)
   {
      $routeInfo = $this->getRouteInfo($routeName);
      
      if(!isset($routeInfo["action"])){
         return self::throwNewException(30299923402302,"Rotta invalida: '".$routeName."', il parametro 'action' non è definito");
      }
      
      if(!isset($routeInfo["path"])){
         return self::throwNewException(30299923402303,"Rotta invalida '".$routeName."', il parametro 'path' non è definito");
      }
      
      if(@preg_match("/\\|\$|\^|(\[A-z])|(\[0-9])|(\[a-z])|(\[a-z0-9])|(\[A-Z0-9])|(\[A-z0-9])/",$routeInfo["path"]) != false){
         return self::throwNewException(30299923402301,"Rotta invalida '".$routeName."',  il parametro path contiente dei caratteri non consentiti! Le rotte non devono essere espressioni regolari ma pseudo-path, es: (:action)/(:method)/(:any) ");
      }
      
      return true;
   }
   
   /**
    * Compila tutte le rotte attualmente caricate
    * 
    * @return boolean
    * 
    * @throws Exception
    */
   private function compileAllRoutes()
   {
        $routingMaps = $this->getRoutingMaps();
      
        if(is_array($routingMaps) && count($routingMaps) > 0)
        {
           foreach($routingMaps as $routeName => $routeInfo)
           {
              if(!$this->compileRoute($routeName)){
                 return false;
              }
           }
           
           return true;
        }

        return self::throwNewException(30299923402304,"Nessuna rotta definita! dove andrà a finire questa chiamata??? :)");
   }
   
   /**
    * Compila una rotta specifica
    * 
    * @param String $routeName Nome della rotta
    * 
    * @return Boolean
    */
   private function compileRoute($routeName,$prepend = false)
   {
      $routeInfo = $this->getRouteInfo($routeName);
      
      if(isset($routeInfo["compiled"]))
      {
         return true;
      }
      
      /**
       * Valido il nome della rotta
       */
      if(!$this->isValidRouteName($routeName))
      {
         return $this->throwNewException(90634523984628934, 'Il nome di questa rotta "'.$routeName.'" non è valido, deve soddisfare il formato: '.self::ROUTING_NAME_PATTERN);
      }
      
      $routeParams         = isset($routeInfo["params"])   ? $routeInfo["params"]   : array();
      $routeDefaults       = isset($routeInfo["defaults"]) ? $routeInfo["defaults"] : array();
      
      $routeCompileInfo    = $this->compileRouteInfo($routeName,$routeInfo["path"],self::ROUTE_PART_PATH,$routeParams,$routeDefaults);
      
      $routeInfo["path"]   = $routeCompileInfo["compiledPath"];
      $routeInfo["params"] = $routeCompileInfo["compiledParams"];

      if(isset($routeInfo["host"]) && strlen($routeInfo["host"]) > 0)
      {
         $routeCompileInfoHost   = $this->compileRouteInfo($routeName,$routeInfo["host"],self::ROUTE_PART_HOST,$routeInfo["params"],$routeDefaults);
         $routeInfo["host"]      = $routeCompileInfoHost["compiledPath"];
         $routeInfo["params"]    = array_merge($routeInfo["params"],$routeCompileInfoHost["compiledParams"]);
      }
                  
      $this->_ROUTING_MAPS[$routeName]["compiled"] = 1;
      
      if(!$prepend)
      {
         $this->_ROUTING_COMPILED_MAPS[$routeName] = $routeInfo;
      }
      else
      {
         if(isset($this->_ROUTING_COMPILED_MAPS[$routeName]))
         {
             $this->_ROUTING_COMPILED_MAPS[$routeName] = $routeInfo;
         }
         
         $this->_ROUTING_COMPILED_MAPS = array_merge(array($routeName => $routeInfo),$this->_ROUTING_COMPILED_MAPS);
      }
            
      return true;
   }
   
   /**
    * Registra gli eventi necessari al routing
    * 
    * @return \Application_Routing
    */
   public function registerHooks()
   {
       $this->getApplicationKernel()
            ->get('@hooks')
               
            //Registra hooks di ordinamento di tutte le rotte
               
            ->registerHook(function(\Application_HooksData $hookData){ 
                $hookData->getKernel()->get('@routing')->sortRoutingMaps(); 
            },Interface_ApplicationHooks::HOOK_TYPE_PRE_ROUTING)->setHookDescription('Sort route after kernel is full loaded');
       
       return $this;
   }
   
   /**
    * Ordina le mappe delle rotte in base al parametro position
    * 
    * @return \Application_Routing
    */
   public function sortRoutingMaps()
   {
       $index = 0;
       
       foreach($this->_ROUTING_MAPS as $routeName => $routeInfo)
       {
            if(empty($routeInfo['position']))
            {
               $this->_ROUTING_COMPILED_MAPS[$routeName]['position']  = $index;
            }
            
            if(empty($routeInfo['position']))
            {
               $this->_ROUTING_MAPS[$routeName]['position']           = $index;
            }
            
            $index++;
       }
      
       
       uasort($this->_ROUTING_MAPS,function($a,$b){
           return $a['position'] > $b['position'] ? 1 : -1;
       });

       uasort($this->_ROUTING_COMPILED_MAPS,function($a,$b){
           return $a['position'] > $b['position'] ? 1 : -1;
       });
              
       return $this;
   }
      
   /**
    * Compila il path, l'host  e i parametri di una determinata rotta
    * 
    * @param String $routeName     Nome della rotta
    * @param String $routePath     Path da compilare in una regexpr valida
    * @param String $routePart     Indica la parte della rotta, es: host, path etc..
    * @param Array  $routeParams   Parametri da passare alla rotta
    * @param Array  $routeDefaults Parametri di defaults da passare alla rotta 
    * 
    * @return Array("compiledPath","compiledParams")
    */
   private function compileRouteInfo($routeName,$routePath,$routePart,array $routeParams = array(),array $routeDefaults = array())
   {
      $pathRegExpr = $routePath;
      
      $pathRegExpr = str_replace("?","\?",$pathRegExpr);
      
      /**
       * Ricerco i parametri nel path per effettuare la sostituzione,es: url/{hash}
       * Questa sostituizione andrà a sostituire anche i valori dei relativi parametri mettendoci il relativo ${i-esimo} per il futuro replace all'esecuzione
       */
      if(preg_match_all('/\{([A-z\_\-]+)\}/', $pathRegExpr, $matches)!==false)
      {
         $pathParameters       = $matches[0];
         $pathParametersValues = $matches[1];
         
         $i = 0;
         
         foreach($pathParametersValues as $value)
         {
            $pathParameter   = $pathParameters[$i];
            
            if(!isset($routeParams[$value]))
            {
               return self::throwNewException(93492388234,"Rotta Invalida: ".$routeName.", la rotta presenta il parametro slug \"".$pathParameter."\" che non è presente nella lista dei parametri. La rotta deve definire l'attributo 'params', con l'associazione slug => shortcut / regexp ");
            }
            
            $parameterShortcut   = $routeParams[$value];
            $parameterValue      = "";
            
            if($this->isShortcutValid($parameterShortcut))
            {
               $shortcutRegExpr = false;
               
               if(isset($this->_ROUTING_MATCH_SHORTCUT[$parameterShortcut]))
               {
                  $shortcutRegExpr  = $this->_ROUTING_MATCH_SHORTCUT[$parameterShortcut];
               }
               else
               {
                  try
                  {
                     if($parameterShortcut[0] == "/"){
                        $parameterShortcut[0] = "";
                     }
                     
                     if($parameterShortcut[strlen($parameterShortcut)-1]=="/"){
                        $parameterShortcut[strlen($parameterShortcut)-1] = "";
                     }

                     preg_match("/".$parameterShortcut."/",self::ROUTING_TEST_REGEXPR_STRING);
                     $shortcutRegExpr = $parameterShortcut;
                  }
                  catch(\Exception $e)
                  {
                     $shortcutRegExpr = false;
                  }
               }
               
               if(!$shortcutRegExpr)
               {
                  return self::throwNewException(23842349234,"Rotta Invalida: ".$routeName.", la rotta presenta il parametro ".$pathParameter." che utilizza uno shortcut/regex non valido: ".$parameterShortcut);
               }
               
               $parameterRegExpr = "(?<".$value.">".$shortcutRegExpr.")%s";
               
               if(array_key_exists($value,$routeDefaults))
               {
                   $defaultOptional       = true;
                   $routeDefaultValueInfo = $routeDefaults[$value];
                   
                   if(is_array($routeDefaultValueInfo))
                   {
                      if(!isset($routeDefaultValueInfo['value']))
                      {
                          return self::throwNewException(23842349234,"Rotta Invalida: ".$routeName.", la rotta presenta il parametro ".$pathParameter." che di default non valido, può essere il valore o un array");
                      }
                         
                      if(isset($routeDefaultValueInfo['required']) && ($routeDefaultValueInfo['required'] == 'true' || (bool) $routeDefaultValueInfo['required']))
                      {
                          $defaultOptional = false;
                      }                      
                   }
                   
                   if($defaultOptional)
                   {
                      $parameterRegExpr = sprintf($parameterRegExpr,'{0,1}');
                   }
                   else
                   {
                      $parameterRegExpr = sprintf($parameterRegExpr,'{1}');
                   }
               }
               else
               {
                   $parameterRegExpr = sprintf($parameterRegExpr,'');
               }
               
               $parameterValue   = '$'.($i+1);
               $parameterValue   = $parameterRegExpr;
            }
            
            /**
             * Questo valore ha un defaults, allora applico all'eventuale "/" che lo precede l'opzionalità, solo se sto gestendo il path della rotta
             */
            if(array_key_exists($value,$routeDefaults) && $routePart == self::ROUTE_PART_PATH) 
            {
                $pos = strpos($pathRegExpr,'{'.$value.'}');
                
                if($pos !== false)
                {
                    $pathRegExpr = substr($pathRegExpr,0,$pos-1).'[\/]{0,1}'.substr($pathRegExpr,$pos,strlen($pathRegExpr));
                }
            }
            
            $pathRegExpr          = preg_replace('/\{'.$value.'\}/',$parameterRegExpr,$pathRegExpr,1);            
            $routeParams[$value]  = $parameterValue;
            $i++;
         }
         
      }
      
      if(is_array($this->_ROUTING_MATCH_SHORTCUT) && count($this->_ROUTING_MATCH_SHORTCUT) > 0)
      {
         foreach($this->_ROUTING_MATCH_SHORTCUT as $shortcut => $pattern)
         {
            if(strstr($pathRegExpr,$shortcut)!==false)
            {
               $pathRegExpr = str_replace($shortcut,$pattern,$pathRegExpr);
            }
         }
      }
      
      //Fix primo "/" delimiter
      if($pathRegExpr[0] != "/"){
         $pathRegExpr = "/^[\/]{0,1}".$pathRegExpr;
      }
      else if($pathRegExpr[0] == "/"){
         $pathRegExpr = "/^\\".$pathRegExpr;
      }      
      

      //Fix escape "/" in regexpr
      for($i=0;$i<strlen($pathRegExpr);$i++)
      {
         if($i>0)
         {
            if($pathRegExpr[$i] == "/" && $pathRegExpr[$i-1]!="\\"){
               $pathRegExpr = substr($pathRegExpr,0,$i)."\\".substr($pathRegExpr,$i,strlen($pathRegExpr));
            }
         }
      }      
           
      if($routePath !="/")
      {
         if($pathRegExpr[strlen($pathRegExpr)-1] != "$"){
//            $pathRegExpr.="[\/]{0,1}$";
             $pathRegExpr.="$";
         }
          
         //Fix last "/" delimiter  
         if($pathRegExpr[strlen($pathRegExpr)-1] != "/"){
            $pathRegExpr.="/";
         }
         else if($pathRegExpr[strlen($pathRegExpr)-1] == "/"){
            $pathRegExpr = substr($pathRegExpr,0,strlen($pathRegExpr)-1)."\//";
         }
      }
      else 
      {
         $pathRegExpr.="$/";
      }
      
      return Array("compiledPath"=>$pathRegExpr,"compiledParams"=>$routeParams);
   }
   
   
   /**
    * Legge le rotte storate in cache se presenti altrimenti restituisce FALSE
    * 
    * @param String $fileRouteConfig         Path assoluto file rotte orginali
    * @param String $fileRouteConfigCache    Path assoluto file rotte cache
    * 
    * @return Mixed
    */
   private function getRouteCache($fileRouteConfig,$fileRouteConfigCache)
   {
      if(!file_exists($fileRouteConfigCache)){
         return false;
      }
      
      if(filemtime($fileRouteConfigCache) < filemtime($fileRouteConfig))
      {
         if(!unlink($fileRouteConfigCache)){
            return self::throwNewException(9283463473648932,"Impossibile eliminare il file di cache per la mappatura delle rotte: ".$fileRouteConfigCache);
         }
         
         return false;
      }
      
      $fileContent = file_get_contents($fileRouteConfigCache);
      
      if($fileContent!==false){
         return unserialize($fileContent);
      }
      
      return false;
   }
   
   /**
    * Verifica che lo shortcut da utilizzare sia valido
    * 
    * @param String $shortcut Shortcut, es: (:any)
    * 
    * @return Boolean
    */
   private function isShortcutValid($shortcut)
   {
      return preg_match('/(\((.*)\))/',$shortcut,$matches)!==false ? true : false;
   }
   
   
   
   /**
    * Restituisce la mappatura completa delle rotte non compilate
    * 
    * @return Array
    */
   private function getRoutesMapsFromCache()
   {
      $cachedRoutes = $this->getApplicationConfigs()->getConfigsFromCache(self::ROUTING_CONFIG_MAP_FILE_NAME);
      return $cachedRoutes ? array_reverse($cachedRoutes) : false;
   }
   
   /**
    * Ricerca le rotte elaborate precedentemente in cache su file verificando che il file di configurazione originale non sia cambiato, altrimenti elimina il file di cache
    * 
    * @return Boolean | Array
    */
   private function getRoutesCompiledFromCache()
   {
      return $this->getApplicationConfigs()->getConfigsFromCache(self::ROUTING_CONFIG_COMPILED_FILE_NAME);
   }
   
   /**
    * Stora in cache l'attuale mappatura delle rotte in chiaro
    * 
    * @return \Application_Routing
    */
   private function storeCurrentRoutingMaps()
   {
      $this->getApplicationConfigs()->storeConfigsCache(self::ROUTING_CONFIG_MAP_FILE_NAME,$this->getRoutingMaps());
      return $this;
   }
   
   /**
    * Stora in cache l'attuale mappatura delle rotte compilate
    * 
    * @return \Application_Routing
    */
   private function storeCurrentRoutingCompiled()
   {
      $this->getApplicationConfigs()->storeConfigsCache(self::ROUTING_CONFIG_COMPILED_FILE_NAME,$this->getRoutingMapsCompiled());
      return $this;
   }
   
   /**
    * Elaboro i parametri di defaults delle rotte
    * 
    * @params Array $defauls Parametri di defaults della rotta
    * 
    * @return Array
    */
   public function getAdaptRouteDefaults(array $defaults = array())
   {
      if(count($defaults) > 0)
      {
         foreach($defaults as $key => $value)
         {
            $defaults[$key] =  $this->getAdaptRouteDefaultsValue($value);
         }     
      }
      
      return $defaults;
   }
   
   
   /**
    * Restituisce i dati di default adattati in base alla "at" notation:
    * 
    * es: @session.usr_id
    * 
    * @param Mixed $value String|Array
    * 
    * @return boolean
    */
   private function getAdaptRouteDefaultsValue($value)
   {      
      $values = is_array($value) ? $value : array($value);
      
      $defaultValue = false;
      
      foreach($values as $currentValue)
      {         
         if(!$defaultValue)
         {
            if(strstr($currentValue,"@")!==false)
            {                
                try
                {
                   $defaultValue = $this->getApplicationKernel()->getApplicationServices()->callServiceString($currentValue);
                } 
                catch (\Exception $e) 
                {
                   $defaultValue = false;
                }
            }
            else 
            {
                $defaultValue = $currentValue;
            }
           
            if(!$defaultValue)
            {
               $defaultValue = self::ROUTE_DEFAULT_PARAMETER_EMPTY;
            }
         }
      }

      return $defaultValue;
   }
}