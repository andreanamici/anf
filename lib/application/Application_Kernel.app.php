<?php

/**
 * anframework
 * 
 * @author Andrea Namici 
 * 
 * @mailto: andrea.namici@gmail.com
 * 
 */

/**
 * Questa classe è il core di tutta l'applicazione.
 * 
 * E' possibile accedere ai services sfruttando le proprietà non definite, le quali verranno ricercate nei services definiti.
 *
 * @method Application_Kernel getInstance Restituisce l'instanza del kernel
 * 
 * @property \Application_Hooks       $hooks        [CORE] Service del gestore degli hooks 
 * @property \Application_Languages   $translate    [CORE] Service del gestore delle traduzioni
 * @property \Application_Commands    $commands     [CORE] Service del gestore dei commandi
 * @property \Application_Routing     $routing      [CORE] Service del gestore del routing
 * @property \Application_Plugins     $plugins      [CORE] Service del gestore dei plugins
 * @property \Application_Services    $services     [CORE] Service del gestore dei servizi
 * @property \Application_Autoload    $autoload     [CORE] Service del gestore dell'autoload
 * @property \Application_HttpRequest $httprequest  [CORE] Service del gestore della request HTTP
 * @property \Application_Configs     $config       [CORE] Service del getore delle configurazioni
 * @property \Application_Templating  $templating   [CORE] Service del gestore dei template
 * 
 * @property \Application_CookieManager     $cookie     [CORE] Service del gestore dei cookie
 * @property \Application_SessionManager    $session    [CORE] Service del gestore della session
 * @property \DAO_CacheManager              $cache      [CORE] Service del gestore del layer di caching
 * @property \DAO_DBManager                 $database   [CORE] Service del gestore del DBMS
 * 
 * @property \Controllers_ActionController $controller          [CORE] Service in cui è presente l'action controller instanziato dal Kernel
 * @property \TemplateEngine_RainTpl       $templating.rain     [PLUGIN] Service del template engine RainTPL
 * @property \TemplateEngine_Smarty        $templating.smarty   [PLUGIN] Service del template engine RainTPL
 * @property \TemplateEngine_Twig          $templating.twig     [PLUGIN] Service del template engine RainTPL
 * 
 * @property \Swift_Mailer            $mailer         [PLUGIN]  Gestore dell'invio di posta elettronica tramite swift di symfony
 * @property \Utility_CommonFunction  $utility        [UTILITY] Service della classe delle utility
 * @property \Utility_Upload          $utility.upload [UTILITY] Service del gestore dell'upload dei file
 * 
 * @property \plugins\FormValidationEngine\Form\FormValidationEngine $form_validation [PLUGIN] Gestore della validazione dei forms
 * 
 */
class Application_Kernel
{
   use Trait_ObjectUtilities,
           
       Trait_Singleton;
   
   /**
    * Controller che generate output HTML dalle response degli ActionObject
    */
   const CONTROLLER_TYPE_HTML      = 'html';
   
   /**
    * Controller che genera JSON, JSONP o HTML
    */
   const CONTROLLER_TYPE_AJAX      = 'ajax';
   
   /**
    * Nome del file di log generato dal kernel per lo stacktrace
    */
   const LOG_FILE_NAME             = 'stacktrace';
   
   /**
    * Enviroment di default del Kernel
    */
   const DEFAULT_ENVIRONMENT       = 'dev';

   /**
    * Status di debug di default
    */
   const DEFAULT_DEBUG             = false;
   
   
   const KERNEL_EVENT_INIT   = 'init';
   const KERNEL_EVENT_START  = 'start';
   const KERNEL_EVENT_BOOT   = 'boot';
   const KERNEL_EVENT_LOADED = 'loaded';
   
   
   
   /**
    * Indica il comportamento di lanciare un eccezione (valore di default da gestire)
    * @var String
    */
   const DEFAULT_EXCEPTION_BEHAVIOUR = '__EXCEPTION__';

   /**
    * Microseconds di inizio del kernel
    * @var Float
    */
   protected $_kernel_start_time = 0;
   
   /**
    * Microseconds di fine del kernel
    * @var Float
    */
   protected $_kernel_end_time   = 0;
   
   /**
    * Gestore dei Services dell'applicazione
    * 
    * @var Application_Services
    */
   protected $_app_services        = null;
   
   /**
    * Action Controller attualmente gestito
    * 
    * @var Controllers_ActionController
    */
   protected $_app_controller   = null;
   
   
   /**
    * Indica se si è in Debug Mode
    * 
    * @var Boolean default FALSE
    */
   protected $_debug              = false;
   
   /**
    * Indica l'environment del kernel
    * 
    * @var String
    */
   protected $_environment        = null;
   
   
   /**
    * Contiene tutte le classi dei packages definiti
    * 
    * @var ArrayIterator
    */
   protected $_packagesIterator = null;
   
   
   /**
    * Controller utilizzato di default
    * @var String
    */
   protected $_controller_default        = null;
   
   /**
    * Dati elaborati dall'Application Routing
    * 
    * @var Application_RoutingData
    */
   protected $_application_route_data    = false;
   

   protected $_kernel_stack_callbacks = array();
   
   /**
    * Lista dei servizi obbligatori che devono essere inizializzati per il funzionamento del Kernel 
    * 
    * @var array 
    */
   protected $_kernel_required_services = array(
            'kernel',
            'commands',
            'routing',
            'plugins',
            'services',
            'hooks',
            'autoload',
            'translate',
            'config',
            'logger',
            'httprequest',
            'templating',
            'session',
            'cookie',
   );
   
   
   /**
    * Contiene l'array dei servizi di base da caricare nel kernel
    * 
    * @var array
    */
   protected $_kernel_services = array();
   
   /**
    * Contiene l'array dei servizi caricati dal kernel
    * 
    * @var array
    */
   protected $_kernel_services_loaded = array();
   
   /**
    * Eccezione cattuarata dal Kernel che deve essere gestita
    * 
    * @var \Exception
    */
   protected $_exception = false; 
   
   /**
    * Indica se la Cli è abilitata, default false
    * @var Boolean
    */
   protected $_is_cli_enabled = false;
   
   /**
    * Restituisce il nome dell' ActionController  creato in base alla configurazione della rotta che indica la tipologia di controller da utilizzare
    * Di default verranno ricercati i controller già presenti nel framework, così da poter utilizzare direttamente gli ActionObject.
    * 
    * @return string
    */
   protected function getActionControllerClassName($controllerType)
   {
       return 'Controllers_ActionController'.strtoupper($controllerType);
   }
   
   /**
    * Restituisce il nome della classe che si occupa del caricamento dei servizi
    * 
    * @return String
    */
   protected function getApplicationServicesClass()
   {
       return 'Application_Services';
   }
   
   /**
    * Restistuisce l'array dei servizi core in cui vengono mappati gli oggetti richiesti dal kernel durante il bootstrap
    * Ogni elemento sarà composto da "<serviceName>" => configurazione "<class>/<plugin>" etc..
    * 
    * @return array
    */
   protected function getKernelServices()
   {
        return array(
            
            'kernel'                => array(
                                           'class' => $this
                                       ),
            'autoload'              => array(
                                           'class'    => 'Application_Autoload',
                                           'lazy'     => false   
                                       ),
            'utility'               => array(
                                            'class'   => 'Utility_CommonFunction',
                                            'lazy'    => false
                                       ),
            'utility.upload'        => array(
                                            'class'   => 'Utility_Upload',
                                            'lazy'    => false
                                       ),
            'config'                => array(
                                           'class' => 'Application_Configs'
                                       ),
            'logger'                => array(
                                           'class' => 'Application_LogWriter'
                                       ),
            'commands'              => array(
                                           'class' => 'Application_Commands'
                                       ),
            'routing'               => array(
                                           'class' => 'Application_Routing'
                                       ),
            'plugins'               => array(
                                           'class' => 'Application_Plugins'
                                       ),
            'services'              => array(
                                            'service' => '@kernel.getApplicationServices'
                                       ),
            'hooks'                 => array(
                                           'class' => 'Application_Hooks'
                                       ),
            'translate'             => array(
                                           'class' => 'Application_Languages'
                                       ),
            'httprequest'           => array(
                                           'class'    => 'Application_HttpRequest',
                                           'lazy'     => true,
                                           'callback' => function($service){ return $service::createFromGlobals(); }
                                       ),
            'templating'            => array(
                                           'class' => 'Application_Templating'
                                       ),
            'session'               => array(
                                           'class' => 'Application_SessionManager'
                                       ),
            'cookie'                => array(
                                           'class' => 'Application_CookieManager',
                                       ),
            'requestData'           => array(
                                           'service'  => '@kernel.controller.getActionRequestData'
                                       ),                 
            'templating'            => array(
                                           'class' => 'Application_Templating'
                                       ),
            'cache'                 => array(
                                            'class'   =>'DAO_CacheManager'
                                       ),
            'database'              => array(
                                            'class'   => 'DAO_DBManager'
                                       ),
            'templating'            => array(
                                            'class'   => 'Application_Templating'
                                       ),
            'templating.rain'       => array(
                                           'plugin'    => 'TemplateEngine/RainTpl'                               
                                       ),
            'templating.smarty'     => array(
                                           'plugin'   => 'TemplateEngine/Smarty'
                                       ),
            'templating.twig'       => array(
                                            'plugin'   => 'TemplateEngine/Twig'
                                       ),
            'form_validation'        => array(
                                            'plugin'      => 'FormValidationEngine',
                                            'lazy'        => false,
                                            'includeonly' => true
                                       ),
            'mailer'                => array(
                                            'plugin'      => 'Symfony.swiftmailer',
                                            'lazy'        => false,
                                            'includeonly' => true
                                       ),

            'mobile_detector'       => array(
                                            'class' => 'Mobile_Detect',
                                            'lazy'  => true
                                       )
           );
   }
   
   /**
    * Restituisce i ms di inizio kernel
    * @return Float
    */
   public function getKernelStartTime()
   {
      return $this->_kernel_start_time;
   }
   
   
   /**
    * Restituisce i ms di fine kernel
    * @return Float
    */
   public function getKernelEndTime()
   {
      return $this->_kernel_end_time;
   }
   
   /**
    * Restituisce il gestore del routing
    * 
    * @return Application_Routing
    */
   public function getApplicationRouting()
   {
      if(!isset($this->routing))
      {
          $this->routing = $this->routing;
          
          if($this->_debug)
          {
             self::writeLog('Servizio "routing" caricato nel kernel',self::LOG_FILE_NAME);
          }
      }
      
      return $this->routing;
   }
   
   /**
    * Restituisce il gestore delle traduzioni dei locales yml/php etc..
    * 
    * @return Application_Languages
    */
   public function getApplicationLanguages()
   {
      if(!isset($this->translate))
      {
         $this->translate = $this->translate;
          
         if($this->_debug)
         {
            self::writeLog('Servizio "translate" caricato nel kernel',self::LOG_FILE_NAME);
         }
      }
      
      return $this->translate;
   }
   
   /**
    * Restitusce l'autoload dell'applicazione
    * 
    * @return Application_Autoload
    */
   public function getApplicationAutoload()
   {
      if(!isset($this->autoload))
      {
          $this->autoload = $this->__get('autoload');
          
         if($this->_debug)
         {
            self::writeLog('Servizio "autoload" caricato nel kernel',self::LOG_FILE_NAME);
         }
      }
      
      return $this->autoload;
   }
   
   
   /**
    * Restituisce la request HTTP
    * 
    * @return \Application_HttpRequest
    */
   public function getApplicationHttpRequest()
   {
      if(!isset($this->httprequest))
      {
          $this->httprequest = $this->__get('httprequest');
          
         if($this->_debug)
         {
            self::writeLog('Servizio "httprequest" caricato nel kernel',self::LOG_FILE_NAME);
         }
      }
      
      return $this->httprequest;
   }
   
   
   /**
    * Restituisce il gestore degli hooks
    * 
    * @return \Application_Hooks
    */
   public function getApplicationHooks()
   {
      if(!isset($this->hooks))
      {
          $this->hooks = $this->__get('hooks');
          
          if($this->_debug)
          {
             self::writeLog('Servizio "hooks" caricato nel kernel',self::LOG_FILE_NAME);
          }
       }
      
       return $this->hooks;
    }
   
   /**
    * Restituisce il gestore dei commands
    * 
    * @return Application_Commands
    */
   public function getApplicationCommands()
   {
      if(!isset($this->commands))
      {
          $this->commands = $this->__get('commands');
          
          if($this->_debug)
          {
             self::writeLog('Servizio "commands" caricato nel kernel',self::LOG_FILE_NAME);
          }
       }
      
       return $this->commands;
   }
   
   /**
    * Restituisce il gestore delle configurazioni
    * 
    * @return Application_Configs
    */
   public function getApplicationConfigs()
   {
      if(!isset($this->config))
      {
          $this->config =  $this->__get('config');
          
          if($this->_debug)
          {
             self::writeLog('Servizio "config" caricato nel kernel',self::LOG_FILE_NAME);
          }
       }
      
       return $this->config;
   }
   
   
   /**
    * Restituisce il gestore dei plugin
    * 
    * @return Application_Plugins
    */
   public function getApplicationPlugins()
   { 
      if(!isset($this->plugins))
      {
          $this->plugins = $this->__get('plugins');
          
          if($this->_debug)
          {
             self::writeLog('Servizio "plugins" caricato nel kernel',self::LOG_FILE_NAME);
          }
       }
      
       return $this->plugins;
   }
   
   
   /**
    * Restituisce l'actionController attulamente instanziato
    * 
    * @return Controllers_ActionController
    */
   public function getApplicationActionController()
   {
      return $this->_app_controller;
   }
   
   
   /**
    * Restituisce il gestore dei services
    * 
    * @return Application_Services
    */
   public function getApplicationServices()
   {
       return $this->_app_services;
   }
      
   
   /**
    * Indica se l'attuale Server API è la console (cli)
    * 
    * @return Boolean
    */
   public static function isServerApiCLI()
   {
      return php_sapi_name() == PHP_SAPI && empty($_SERVER['REMOTE_ADDR']);
   }
   
     
   /**
    * Costruttore del Kernel vuoto, invocare subito il metodo initMe
    * 
    * @see initMe()
    * 
    * @return boolean
    */
   public function __construct()
   { 
      $this->autoloadRegister(__DIR__,'')    
           ->autoloadRegister(__DIR__,'app')
           ->_packagesIterator = new ArrayIterator();
   }

   
   /**
    * Imposta il debug
    * 
    * @param Boolean $debug TRUE/FALSE
    * 
    * @return \Application_Kernel
    */
   public function setDebug($debug)
   {
      $this->_debug = $debug;
      
      if($debug)
      {
         ini_set('display_errors',1);
      }
      else
      {
         ini_set('display_errors',0);
      }
      
      ini_set('error_reporting',E_ALL);
      
      return $this;
   }
   
   /**
    * Indica se è attivo il debug
    * 
    * @return Boolean
    */
   public function isDebugActive()
   {
      return $this->_debug;
   }
   
   /**
    * Imposta l'environment da utilizzare
    * 
    * @param String $env Environment
    * 
    * @return \Application_Kernel
    */
   public function setEnvironment($env)
   {
      $this->_environment = $env;
      
      if(property_exists($this,'config'))
      {
          $this->config->initMe();
      }
      
      return $this;
   }
   
   
   /**
    * Restituisce l'environment da utilizzare
    * 
    * @return String
    */
   public function getEnvironment()
   {
      return $this->_environment;
   }
   
   
   /**
    * Configura se la cli è abilitata o meno
    * 
    * @param Boolean $isCliEnable  status
    * 
    * @return \Application_Kernel
    */
   public function setCliEnable($isCliEnable)
   {
       $this->_is_cli_enabled = $isCliEnable;
       return $this;
   }
   
   /**
    * Indica se la console è abilitata, default false
    * 
    * @return Boolean
    */
   public function isCliEnable()
   {
       return $this->_is_cli_enabled;
   }
   
   
   /**
    * Restituisce un array iterator di packages registrati
    * Ogni elemento dell'iteratore è un Abstract_Package
    * 
    * @return ArrayIterator
    */
   public function getPackagesRegistered()
   {
      $this->_packagesIterator->rewind();
      return $this->_packagesIterator;
   }
   
   
   /**
    * Restituisce l'instanza del package registrato
    * 
    * <br>
    * <b>Questo metodo lancia un eccezione qualora sia richiesto un package inesistente!</b>
    * 
    * @throws Exception_PortalErrorException
    * 
    * @return Abstract_Package
    */
   public function getPackageInstance($package)
   {
      
      if($this->_packagesIterator instanceof ArrayIterator)
      {
         if($this->_packagesIterator->count() > 0)
         {
            if($this->_packagesIterator->offsetExists($package))
            {
               return $this->_packagesIterator->offsetGet($package);
            }

            foreach($this->_packagesIterator as $key => $packageInstance)
            {
               if($packageInstance->getName()  == $package || get_class($packageInstance) == $package)
               {
                  return $packageInstance;
               }
            }
         }
      }
      
      return self::throwNewException(983247327238883773,'Questo package richiesto: '.$package.' non è stato mai registrato nel kernel!');
   }
   
   /**
    * Registra un servizio nel kernel
    * 
    * @param \Application_ServicesInstance $serviceInstance
    */
   public function _onServiceRegistered(\Application_ServicesInstance $serviceInstance)
   {
       $serviceName          = $serviceInstance->getName();
       $service              = $serviceInstance->getServiceInstance();
       $this->{$serviceName} = $service;
              
       switch($serviceName)
       {
           case 'autoload':
           case 'config':
                
                $this->autoload->initMe()->register();
               
                $this->loadAllConfigurations()      //Carico tutte le configurazioni principali per l'environment
                     ->loadFunctions();             //Includo le function                               
           break;
       
           case 'routing':
               
               $this->routing->setServer($_SERVER)
                             ->setRequest($_REQUEST);
                   
           break;           
       }
   }
   
   /**
    * Application Kernel
    * 
    * Classe base per il funzionamento del portale, si occupa di inizializzare l'enviroment, gli ActionController l'autoload e tutti i componenti del framework
    * 
    * @param String   $environment     Environment da utilizzare, default self::DEFAULT_ENVIRONMENT
    * @param Boolean  $debug           Indica se il sistema è in debug (crea i log), default  self::DEFAULT_DEBUG
    * 
    * @return Application_Kernel
    */
   public function initMe($environment = self::DEFAULT_ENVIRONMENT, $debug = self::DEFAULT_DEBUG)
   {    
         $this->onKernelInit($environment,$debug)
                       
              ->onKernelStart()              //Il Kernel è partito
                 
              ->loadCallbacks()              //Carico le callback di errore e di eccezione
              
              ->prepareKernelServices()
              ->registerAppServices()        //Registra i Services

              ->onKernelBoot()

              ->registerAppPackages()           //Registro i package di terze parti
              ->setOutputBuffering(true)        //Abilito l'outputBuffering
                 
              ->onKernelLoaded();               //Conclude il caricamento del Kernel
         
         return $this;
   }   
   
   /**
    * Inizializza il Kernel con le sole funzioni da server API CLI, in modo da utilizzare in framework da console
    * 
    * @param String   $environment     Environment da utilizzare, default self::DEFAULT_ENVIRONMENT
    * @param Boolean  $debug           Indica se il sistema è in debug (crea i log), default  self::DEFAULT_DEBUG
    * 
    * @return \Application_Kernel
    */
   public function initMeCLI($environment = self::DEFAULT_ENVIRONMENT, $debug = self::DEFAULT_DEBUG)
   {     
         $this->setCliEnable(false);
         
         $this->onKernelInit($environment,$debug)
       
              ->onKernelStart()              //Il Kernel è partito
                 
              ->loadCallbacks()              //Carico le callback di errore e di eccezione
              
              ->prepareKernelServices()
              ->registerAppServices()        //Registra i Services

              ->onKernelBoot()

              ->registerAppPackages()           //Registro i package di terze parti
              ->setOutputBuffering(true)        //Abilito l'outputBuffering
                 
              ->onKernelLoaded();               //Conclude il caricamento del Kernel
      
          return $this;
   }
   
   protected function onKernelInit($environment,$debug)
   {
         if($this->_kernel_start_time > 0)
         {
             return self::throwNewException(2093742934720, 'Non è possibile reinizializzare il Kernel nuovamente!');
         }
       
         if(!defined("APPLICATION_KERNEL_ENVIRONMENT"))
         {
             define("APPLICATION_KERNEL_ENVIRONMENT",$environment);
         }
         
         if(!defined("APPLICATION_KERNEL_DEBUG"))
         {
            define("APPLICATION_KERNEL_DEBUG",$debug);
         }
         
         if(strlen($environment) == 0)
         {
             return $this->flushErrorContent("Il Kernel deve avere un environment!",500,true);
         }

         /**
          * Inizializzazione dell'applicazione globale
          */
         $this->setEnvironment($environment) //Inizializzo l'ambiente dell'applicazione
              ->setDebug($debug);            //Setto il debug mode
         
               
         $this->_onKernel(self::KERNEL_EVENT_INIT);
         
         return $this;
   }
   
   /**
    * Registra una tipologia di callback per tutto lo stack di attivazione del kernel
    * 
    * @param String   $type         Tipologia di callback
    * @param Callback $callable     Callback, verra passato il kernel
    * 
    * @return \Application_Kernel
    */
   public function registerCallback($type,$callable)
   {
       $this->_kernel_stack_callbacks[$type][] = $callable;
       return $this;
   }
   
   /**
    * Esegue lo stack di eventi registrati per il kernel
    * 
    * @param String $type Tipologia di evento
    * 
    * @return \Application_Kernel
    */
   protected function _onKernel($type)
   {
      /**
       * Chiamo lo stack di callback del kernel al start
       */
      if(!empty($this->_kernel_stack_callbacks[$type]))
      {
          foreach($this->_kernel_stack_callbacks[$type] as $callback)
          {
              if(is_callable($callback))
              {
                  call_user_func_array($callback,array($this));
              }
          }
      }
      
      return $this;
      
   }
   
   /**
    * Gestisce l'avvio del kernel, preparando i services necessari al funzionamento del kernel
    * 
    * @return \Application_Kernel
    */
   protected function onKernelStart()
   {
      /**
       * Traccio la partenza del kernel
       */
      $this->_kernel_start_time = microtime();
         
      $this->_onKernel(self::KERNEL_EVENT_START);
      
      return $this;
   }
   
   
   /**
    * Gestisce l'inizializzazione di base del kernel
    * 
    * Questo metodo precede l'avvio e la registrazione dei componenti essenziali del Kernel
    * 
    * @return \Application_Kernel
    */
   protected function onKernelBoot()
   {
       $this->_onKernel(self::KERNEL_EVENT_BOOT);

       if($this->_app_services)
       {
          foreach($this->_kernel_services_loaded as $serviceName => $serviceInstance)
          {
              $this->_app_services->registerService($serviceName,$serviceInstance);
          }
       }
              
       return $this;
   }


   /**
    * Gestisce l'evento di caricamento del Kernel completata correttamente
    * 
    * @return Application_Kernel
    */
   protected function onKernelLoaded()
   {
      $this->_onKernel(self::KERNEL_EVENT_LOADED);
      
      //[\Interface_HooksType::HOOK_TYPE_KERNEL_LOAD] ++++++++++++++++++++++++++++++++++++++
      $this->processHooks(\Interface_HooksType::HOOK_TYPE_KERNEL_LOAD,$this);
      //[\Interface_HooksType::HOOK_TYPE_KERNEL_LOAD]+++++++++++++++++++++++++++++++++++++++
      
      return $this;
   }
   
   /**
    * Imposta l'output buffering
    * 
    * @param Boolean $outputBuffering     Indica se il sistema utilizzerà l'output buffering
    * @param String  $outputCallback      [OPZIONALE] output buffering callback, default ob_gzhandler
    * 
    * @return \Application_Kernel
    */
   public function setOutputBuffering($outputBuffering,$outputCallback = null)
   {
      if(is_null($outputCallback) && !defined("APPLICATION_OUTPUT_BUFFERING_DEFAULT"))
      {
         return self::throwNewException(12738394042043834, 'Non è possibile abilitare l\' output buffering: il parametro $outputCallback è NULL è la costante APPLICATION_OUTPUT_BUFFERING_DEFAULT non è definita');
      }
      
      $outputCallback = is_null($outputCallback) ? APPLICATION_OUTPUT_BUFFERING_DEFAULT : $outputCallback;
      
      if($outputBuffering)
      {
         if(!ob_get_level())
         {
            ob_start($outputCallback);
         }
      }
      else
      {
         if(ob_get_level())
         {
            ob_end_clean();
         }
      }
      
      return $this;
   }
   
   /**
    * Registra il gestore dei services
    * 
    * @return \Application_Kernel
    */
   protected function registerAppServices()
   {
      if(!($this->_app_services instanceof \Application_Services))
      {
         $this->_app_services = call_user_func(array($this->getApplicationServicesClass(),  Application_Autoload::SINGLETON_METHOD_NAME));         
         
         $this->_app_services->registerServices($this->_kernel_services);
         
         $this->_app_services->registerDefaultServices();
         
         if($this->_debug)
         {
            self::writeLog('Servizi registrati',self::LOG_FILE_NAME);
         }
      }
      
      return $this;
   }
   
   /**
    * Registra i packages al kernel
    * 
    * Ogni classe, presente nella root della directory principale del package, ha il compito di definire il caricamento delle classi attraverso l'autoload,
    * di aggiungere eventuali rotte all'applicazione, di definire le ActionObject sfruttate dagli ActionControllers e di gestire le viste e le risorse statiche.
    * 
    * @return \Application_Kernel
    */
   protected function registerAppPackages()
   {
      
      if(!defined("APPLICATION_PACKAGE"))
      {
         return self::throwNewException(9982389834297722, 'Costante "APPLICATION_PACKAGE" necessia per il funzionamento del Kernel, determina come caricare i package!');
      }
      
      /**
       * Includo i file di configurazione dei packages
       */
      $packagesActive = is_bool(APPLICATION_PACKAGE) ? APPLICATION_PACKAGE : unserialize(APPLICATION_PACKAGE);
            
      if(is_bool($packagesActive) && $packagesActive)
      {
         $packagesActive = $this->getAllPackagesDirectories();
      }
            
      $packagesIterator = new ArrayIterator();

      foreach($packagesActive as $packageName)
      {
          $packageInfo     = $this->getPackageInfo($packageName);
          
          if($packageInfo)
          {
             $packagesIterator->append($packageInfo);
          }
      }
            
      //[\Interface_HooksType::HOOK_TYPE_PRE_PACKAGE] +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
      $packagesIterator = $this->processHooks(\Interface_HooksType::HOOK_TYPE_PRE_PACKAGE,$packagesIterator)->getData();
      //[\Interface_HooksType::HOOK_TYPE_PRE_PACKAGE] +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
            
      if($packagesIterator && $packagesIterator->count() > 0)
      {
            /**
             * Includo ed inizializzo le classi dei packages nell'ordine definito
             */
            foreach($packagesIterator as $packageInfo)
            {
               $packageInstance = $packageInfo->offsetGet("instance");               
               $this->registerPackage($packageInstance);
            }
      }
      
      //[\Interface_HooksType::HOOK_TYPE_POST_PACKAGE] +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
      $this->processHooks(\Interface_HooksType::HOOK_TYPE_POST_PACKAGE,$this->_packagesIterator);
      //[\Interface_HooksType::HOOK_TYPE_POST_PACKAGE] +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
      
      return $this;
   }
   
   /**
    * Registra un package
    * 
    * @param Mixed      $package       Instanza del package / Nome string del package (nome cartella che contiene il file php con la classe)
    * @param Boolean    $forceEnable   Indica se bypassare il valore isEnable() dello stesso package
    * 
    * @return \Application_Kernel
    */
   public function registerPackage($package,$forceEnable = false)
   {   
       if(is_string($package) && strlen($package) > 0)
       {
           $packageInfo     = $this->getPackageInfo($package);
           $package    = $packageInfo->offsetGet('instance');
       }
      
       if(!($package instanceof \Abstract_Package))
       {
           return $this->throwNewException(90485230234, 'Non è possibile registrare questo package nel Kernel: '.$package);
       }
       
       $this->_packagesIterator->offsetSet($package->getName(), $package);

       if($forceEnable || $package->isEnable())
       {
          $package->onBeforeLoad();

          $package->initMe();

          $package->onLoad();  

       }
       else
       {
            $this->_packagesIterator->offsetUnset($package->getName());
       }
       
       return $this;
   }

   
   /**
    * Processa il Routing ed ottiene un Application_RoutingData
    * 
    * @return Application_Kernel
    */
   public function resolveRequest()
   {  
      //[\Interface_HooksType::HOOK_TYPE_PRE_ROUTING] ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
      $this->processHooks(\Interface_HooksType::HOOK_TYPE_PRE_ROUTING,$this->routing);
      //[\Interface_HooksType::HOOK_TYPE_PRE_ROUTING] ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ 
       
      //Routing
      $this->_application_route_data = $this->routing->elaborateRequestRouting()->getApplicationRoutingData();
      
      if($this->_debug)
      {
         self::writeLog('Rotta trovata: '.$this->_application_route_data->getRouteName(),self::LOG_FILE_NAME);
      }

      //[\Interface_HooksType::HOOK_TYPE_POST_ROUTING] +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
      $this->_application_route_data = $this->processHooks(\Interface_HooksType::HOOK_TYPE_POST_ROUTING,$this->_application_route_data)->getApplicationRouteData();
      //[\Interface_HooksType::HOOK_TYPE_POST_ROUTING] +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
      
      return $this;
   }
   
   
   /**
    * Processa la richiesta sfruttando l'ActionController gestito dall'attuale routing.
    * 
    * @return Application_Kernel
    */
   public function process()
   {      
      $action                       = $this->_application_route_data->getAction(Controllers_ActionController::DEFAULT_ACTION);
      $method                       = $this->_application_route_data->getMethod(Controllers_ActionController::DEFAULT_SUBACTION);
      $controllerType               = $this->_application_route_data->getControllerType(self::CONTROLLER_TYPE_HTML);
      $package                      = $this->_application_route_data->getPackage();
      $actionParameters             = $this->_application_route_data->getParams();         
      
//      if($this->_debug)
//      {
//         $actionString = is_callable($action) ?  "anonymous function()" : $action;
//         self::writeLog('Preparazione elaborazione Action: '.$actionString.', method: '.$method.', controller: '.$actionControllerName.", parameters: ".print_r($actionParameters,true),self::LOG_FILE_NAME);
//      }
      
      $this->_app_controller = $this->getBuildActionController($controllerType);
      
      /**
       * Inizializza il Package dell'action
       */
      if($package)
      {
         $this->templating->setPackage($package);
      }
      
      /**
       * Registra il controller attuale come servizio
       */
      if($this->_app_services)
      {
         $this->_app_services->registerService('controller', $this->_app_controller);
      }
      
      /**
       * Inizializzo e preparo l'actionObject da elaborare
       */
      if($this->_app_controller->doActionPrepare($action,$method,$actionParameters)!==false)
      {  
         if($this->_debug)
         {
             self::writeLog('Inizio elaborazione action',self::LOG_FILE_NAME);
         }

         /**
          * Processo l'actionObject grazie al controller indicato ed ottengo un oggetto \Application_ControllerResponse che verrà processata dal Kernel
          */
         $controllerResponse = $this->_app_controller->doActionProcess();
         
         if($this->_debug)
         {
            self::writeLog('Fine elaborazione action',self::LOG_FILE_NAME);
         }
            
         $this->viewControllerResponse($controllerResponse);
      }
      
      return $this;
   }
   
   /**
    * Main applicazione
    * 
    * @param Boolean $close [OPZIONALE] Indica se chiudere il kernel (exit dello script), default TRUE
    * 
    * @return \Application_Kernel
    * 
    */
   public function run($close = true)
   {
       if($this->isServerApiCLI() && !$this->isCliEnable())
       {
           return $this->throwNewException(983459834598345, 'Non è possibile proseguire con l\'esecuzione dello stack di attivazione del kernel. La proprietà del kernel $_is_api_cli_enabled risulta uguale a FALSE');
       }
       
       $this->resolveRequest()
            ->process();
       
       $this->closeKernel($close);

       return $this;
   }
   
   /**
    * Processa gli hook in base al tipo specificati
    * 
    * @param String   $hookType      Tipologia di hook
    * @param Mixed    $hookData      Mixed data opzionale da passare all'hookManager
    *      
    * @return Application_HooksResponseData
    */
   public function processHooks($hookType,$hookData = null)
   {  
      /**
       * Non posso processare gli hooks se non ho un autoload di default
       */
      if(!($this->autoload instanceof Application_Autoload))
      {
          return $this->throwNewException(3094308398938493430394,'Impossibile processare gli hooks se non è registrato l\'autoload!');
      }
      
      if(!$this->hooks)
      {
          $this->registerAppHooks();
          
          if(!$this->hooks)
          {
             return $this->throwNewException(298346286502365,'Impossibile processare gli hooks se non è registrato il gestore dei services!'); 
          }
      }
      
      /**
       * Registro gli hooks qualora vengano chiamati senza essere stati registrati
       */
      if(!($this->hooks instanceof \Application_Hooks))
      {
          return $this->throwNewException(235228348293492350,'Impossibile processare gli hooks se non è registrato il gestore degli hooks!');
      }
      
      $applicationHookData = new \Application_HooksData();
      $applicationHookData->setData($hookData);
      $applicationHookData->setKernel($this);
      
      /**
       * Verifico che sia registrato il routing cosi da inizializzare i dati dentro l'applicationHooksData()
       */
      if($this->routing instanceof Application_Routing)
      {
         /**
          * Setto i dati dell'applicationRouteData qualora fossero presenti
          */
         if($this->routing->getApplicationRoutingData() instanceof Application_RoutingData)
         {
            $applicationHookData->setApplicationRouteData($this->routing->getApplicationRoutingData());
         }
      }
      
      $actionController = null;
      
      if($this->_app_services)
      {
        try 
        {
           $actionController = $this->_app_services->getService('controller');
        }
        catch (\Exception $ex) 
        {
           $actionController = $this->_app_controller;
        }
      }
      
      /**
       * Se il controller è stato registrato, lo passo agli hooks cosi da rendere disponibile Controller e ActionObject elaborati
       */
      if($actionController instanceof Controllers_ActionController)
      {
         $applicationHookData->setController($actionController);

         if($actionController->getActionObject() instanceof Abstract_ActionObject)
         {
            $applicationHookData->setActionObject($actionController->getActionObject());
         }
      }
      
      if($this->_debug)
      {
          if($this->hooks->getHooksStackIteratorByType($hookType)->count() > 0 )
          {
             self::writeLog('Hooks ['.$hookType.']  elaborati: '.$this->hooks->getHooksStackIteratorToString($hookType),self::LOG_FILE_NAME);
          }
      }
      
      /**
       * Processo in cascata tutti gli Hook registrati e restituisco la response elaborata da tutto lo stack registrato
       */
      $hooksResponseData =  $this->hooks->processAll($hookType,$hookData,$applicationHookData)->getResponseData();
      
      return $hooksResponseData;
   }
   
   /**
    * Genera un action controller per processare gli ActionObject
    * 
    * @param String   $controller       Tipologia / classe ActionController, es: html,xml,ajax o classe
    * @param Boolean  $processHooks     [OPZIONALE] Indica se processare gli hooks pre e post controller, default TRUE
    * 
    * @return \Controllers_ActionController
    */
   public function getBuildActionController($controller,$processHooks = true)
   {
      $actionControllerName         = !class_exists($controller) ? $this->getActionControllerClassName($controller) : $controller; //Determino il nome del controller da invocare
      
      if($processHooks)
      {
         //[\Interface_HooksType::HOOK_TYPE_PRE_CONTROLLER] ++++++++++++++++++++++++++++++++++++++
         $actionControllerName = $this->processHooks(\Interface_HooksType::HOOK_TYPE_PRE_CONTROLLER, $actionControllerName)->getData();
         //[\Interface_HooksType::HOOK_TYPE_PRE_CONTROLLER] ++++++++++++++++++++++++++++++++++++++
      }  
      
      if(!class_exists($actionControllerName))
      {
         return self::throwNewException(9919828372377772,'L\'actionController richiesto non esite: '.$actionControllerName.', sicuramente hai fornito un controller nel routing non valido: "'.$controller.'", oppure è registrato un hook "'.\Interface_HooksType::HOOK_TYPE_PRE_CONTROLLER.'" che modifca il nome dell\'actionController da invocare.');
      }
            
      $actionController      = $actionControllerName::getInstance();
           
      $this->getApplicationServices()->registerService('controller', $actionController);
      
      if($processHooks)
      {
         //[\Interface_HooksType::HOOK_TYPE_POST_CONTROLLER] ++++++++++++++++++++++++++++++++++++++
         $actionController = $this->processHooks(\Interface_HooksType::HOOK_TYPE_POST_CONTROLLER,$actionController)->getData();
         //[\Interface_HooksType::HOOK_TYPE_POST_CONTROLLER] ++++++++++++++++++++++++++++++++++++++
      }
      
      
      return $actionController;
   }
   
   
   /**
    * Chiusura del Kernel
    * 
    * @param Boolean $exit          [OPZIONALE] Indica se effettuare l'exit dell'applicazione, default TRUE
    * @param Boolean $hook          [OPZIONALE] Indica se eseguire l'hook di chiusura del kernel, default TRUE
    * @param Int     $exitStatus    [OPZIONALE] Status exit, default 0
    * 
    * @return \Application_Kernel|Void
    */
   public function closeKernel($exit = true,$processHook = true,$exitStatus = 0)
   {
      if($this->_debug)
      {
         self::writeLog('Kernel in chiusura',self::LOG_FILE_NAME);
      }

      if($processHook)
      {
         //[\Interface_HooksType::HOOK_TYPE_KERNEL_END] ++++++++++++++++++++++++++++++++++++++++++++++
         $this->processHooks(\Interface_HooksType::HOOK_TYPE_KERNEL_END,$this);
         //[\Interface_HooksType::HOOK_TYPE_KERNEL_END] ++++++++++++++++++++++++++++++++++++++++++++++
      }
      
      if($this->_debug)
      {
         self::writeLog('Kernel chiuso',self::LOG_FILE_NAME);
      }
      
      $this->_kernel_end_time = microtime();
      
      if($exit)
      {
         exit($exitStatus);
      }
      
      return $this;
   }

   
      
   /**
    * Callback invocata se registrata come handler default degli error.
    * 
    * Questo metodo rilancia gli errori come eccezioni, così da essere catturati dal Kernel stesso ed essere processati con gli appositi template
    * 
    * @param Int    $errno    Nr errore
    * @param String $errstr   Messaggio di errore
    * @param String $errfile  File in cui si è verificato l'errore
    * @param Int    $errline  Linea del file
    * 
    * @return void
    * 
    * @throws Exception_PortalErrorException
    * 
    */
   public function _onErrorHandler($errno, $errstr, $errfile, $errline)
   {
       if (!(error_reporting() & $errno)) 
       {
          return false;
       }
       
       switch ($errno)
       {
          case E_ERROR:
          case E_USER_ERROR:      $exceptionMessage = "PHP FATAL ERROR    type: [$errno], $errstr  in file $errfile on line $errline </b>";      break;
          
          case E_WARNING:
          case E_USER_WARNING:    $exceptionMessage = "PHP WARNING        type: [$errno], $errstr  in file $errfile on line $errline <br />\n";  break;
          
          case E_NOTICE:
          case E_USER_NOTICE:     $exceptionMessage = "PHP NOTICE         type: [$errno], $errstr  in file $errfile on line $errline <br />\n";  break;

          case E_DEPRECATED:
          case E_USER_DEPRECATED: $exceptionMessage = "PHP DEPRECATED     type: [$errno], $errstr  in file $errfile on line $errline <br />\n";  break;
          
          default:                $exceptionMessage = "PHP Unknown error  type: [$errno], $errstr  in file $errfile on line $errline <br />\n";  break;
       }
       
       /**
        * Senza autoload non posso gestire gli errori tramite exception, ma carico un template generico di errore
        */
       if(!($this->autoload instanceof \Application_Autoload))
       {
          return $this->flushErrorContent($exceptionMessage,$errno);
       }
       
       return $this->throwNewException($errno,$exceptionMessage,"error");        
   }
   
   /**
    * Callback di chiusura shutdown dello script
    * 
    * @return Application_Kernel
    */
   public function _onShutdown()
   {
      $error = error_get_last();
      
      try
      {
         if(is_array($error) && count($error)>0)
         {
            $error_type      = $error["type"];
            $error_message   = $error["message"]." , file: ".$error["file"].", line: ".$error["line"];
            
            /**
             * Senza autoload non posso gestire gli errori tramite exception, ma carico un template generico di errore
             */
            if(!($this->autoload instanceof Application_Autoload))
            {
               return $this->flushErrorContent($error_message,9394);
            }
            else
            {
               return self::throwNewException($error_type, $error_message);
            }   
            
         }
      }
      catch(Exception $e)
      {
         return $this->_onException($e,false);
      }
      
      return $this;
   }
   
   /**
    * Gestisce le eccezioni lanciate dai controller, dai manager o dagli oggetti
    * 
    * @param Exception $e              Eccezione da gestire
    * @param Boolean   $processHook    Indica se è possibile processare gli hooks associati alle exception
    * 
    * @return Void
    */
   public function _onException(Exception $e,$processHook = false)
   {
      $tplPath  = "";
      
      try
      {
                    
         /**
          * Mostro l'errore qualora fosse generato prima del completamento del kernel
          */
         if(!class_exists('Exception_ExceptionHandler',false))
         {
            return $this->flushErrorContent($e->getMessage(). " in ".$e->getFile()." on line ".$e->getLine(),$e->getCode());
         }
          
         /**
          * Registro l'autoload
          */
         if($this->autoload instanceof \Application_Autoload)
         {
            $this->autoload->register();
         }
         
         /*
          * Questo flag permette di preventivare eventuali loop scaturiti dagli hook che richiamerebbero a loro volta questo metodo e cosi via
          */
         if($this->_exception instanceof \Exception)
         {   
            return false;
         }
         
         $this->_exception = $e;
         
         $tplPath          = \Exception_ExceptionHandler::getTplPath(); //Path default template di errore;
         
         //[\Interface_HooksType::HOOK_TYPE_EXCEPTION] ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
         $e = $this->processHooks(\Interface_HooksType::HOOK_TYPE_EXCEPTION,$e)->getData();
         //[\Interface_HooksType::HOOK_TYPE_EXCEPTION] ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
         
         /**
          * Gestisco l'exception
          */
         if($e!==false && ($e instanceof \Exception))
         {  
            switch(true)
            {     
               /**
                * Htto Status
                */
               case $e instanceof \Exception_HttpStatusException: 
                                                                     if(!$this->_debug)
                                                                     {
                                                                        $tplPath = \Exception_ExceptionHandler::getTplPath($e->getCode());
                                                                     }
                                                                     
                                                                  break;
               /**
                * Redirect fisico
                */                                                   
               case $e instanceof \Exception_RedirectException:
                                                                     $this->redirect($e->getUrl());
                                                                     return $this;
                                                                     
                                                                  break;
            }            
         }
      }
      catch(\Exception $e)
      {
         $tplPath = Exception_ExceptionHandler::getTplPath();
      }
      
      $controllerResponse   = false;
      $defaultHeaders       = array(Interface_HttpStatus::HTTP_STATUS_OK => 'Ok');
      
      if($e instanceof \Application_ControllerResponseData) //L'eccezione è stata convertita in una response valida
      {
          $controllerResponse = $e;
      }
      else if($e instanceof \Exception_HttpStatusException)  //L'eccezione è di tipo HTTP Status, quindi modifico gli headers
      {
         $content        = $this->getExceptionTemplateContent($e);
         $defaultHeaders = array($e->getStatusCode() => $e->getMessage());
      }
      else if($e instanceof \Exception_PortalErrorException && !$this->isServerApiCLI())        //L'eccezione è un errre del portale
      {
         $content = $this->getExceptionTemplateContent($e);
      } 
      else  //Ogni altra eccezione verrà gestita semplicemente mostrando il codice ed il messaggio
      {
         return $this->flushErrorContent($e->getMessage(), $e->getCode());
      }
      
      /**
       * Preparo la response del controller qualora non fosse stata generata dall'hook exception
       */
      if(!$controllerResponse)
      {
         $controllerResponse  = new \Application_ControllerResponseData($content,$defaultHeaders);
      }
      
      
      return $this->viewControllerResponse($controllerResponse,false);
   }
   
   /**
    * Cattura l'output del template di exception
    * 
    * @param Exception_PortalErrorException $e Eccezione
    * 
    * @return String
    */
   protected function getExceptionTemplateContent(Exception_PortalErrorException $e)
   {
       try
       {           
            $tplPath = $e->getTplErrorPath();
            
            if(!ob_get_status())
            {
                @ob_clean();
            }
            
            @ob_start();
       
            if($hookData = $this->processHooks(\Interface_HooksType::HOOK_TYPE_PRE_TEMPLATE)->getData())
            {
                extract($hookData['params']);
            }

            require_once $tplPath;
            $content   = ob_get_clean();
       }
       catch(\Exception $e)
       {
           $content = $e->getMessage();
       }
       
       return $content;
   }
   
   
   /**
    * Controlla eventuali headers già rilasciati e nel caso lancia un eccezione.
    * Se non sono presenti anomali per la response, la mostra a video
    * 
    * @param Application_ControllerResponseData $controllerResponse Response
    * @parma Boolean                            $processHook        Indica se processare gli hook, default TRUE
    * 
    * @return Application_Kernel
    */
   public function viewControllerResponse(Application_ControllerResponseData $controllerResponse,$processHook = true)
   {
      if($controllerResponse->getStatusCode() != \Interface_HttpStatus::HTTP_STATUS_OK)
      {
         return self::throwNewExceptionHttpStatus($controllerResponse->getStatusCode(),$controllerResponse->getContent());
      }

      $headersSent = headers_sent($file,$line);

      if(!$this->isServerApiCLI())
      {
         if($headersSent)
         {
            return self::throwNewException(912839182398123, 'Header just sent in: '.$file. ' on line: '.$line);
         }
      }
       
      if($processHook)
      {
         //[\Interface_HooksType::HOOK_TYPE_PRE_RESPONSE] ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
         $controllerResponse  = $this->processHooks(\Interface_HooksType::HOOK_TYPE_PRE_RESPONSE,$controllerResponse)->getData();
         //[\Interface_HooksType::HOOK_TYPE_PRE_RESPONSE] ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
      }      
      
      $headers = $controllerResponse->getHeaders()->getIterator();

      if(!$headersSent)
      {
            /**
             * Rilascio gli headers impostati alla Response
             */
            while($headers->valid())
            {
               $httpStatusCode    = is_numeric($headers->key()) ? $headers->key() : null;
               $headerValue       = $headers->key().": ".$headers->current();         
               $headers->next();

               header($headerValue,null,$httpStatusCode);
            }
      }
      
      
      return $this->flushResponse($controllerResponse,$processHook);
   }
   
   /**
    * Mostra a video la response elaborata dal controller
    * 
    * @param Application_ControllerResponseData $controllerResponse Response
    * @parma Boolean                            $processHook        Indica se processare gli hook, default TRUE
    * 
    * @return Application_Kernel
    */
   protected function flushResponse(\Application_ControllerResponseData $controllerResponse)
   {
      /**
       * Mostro la response a video
       * \Application_ControllerResponseData::__toString()
       */
      echo $controllerResponse;
      
      if($this->_debug)
      {
         self::writeLog('Content response elaborato',self::LOG_FILE_NAME);
      }
      
      return $this;
   }
   
   
   /**
    * Elabora una pagina di errore relativa agli errori generati prima che sia caricato l'autoload
    * 
    * <b>Viene automaticamente arrestata l'esecuzione del kernel con exit()</b>
    * 
    * @param String   $errorMessage Messaggio di errore recuperato dal metodo onError()
    * @param Int      $errorCode    Codice errore
    * @parma Boolean  $closeKernel  Indica se chiudere anche il kernel, default TRUE
    * 
    * @return Void
    */
   protected function flushErrorContent($errorMessage,$errorCode,$closeKernel = true)
   {
       $content = $errorMessage.", code: ".$errorCode;
       
       try
       {
            if($this->config)
            {
                $this->config->loadConfigsFile('exception');
                
                @ob_clean();
                @ob_start();

                $e  = $this->_exception;

                if(!$this->isServerApiCLI())
                {
                     $exceptionErrorPage = $this->config->getConfigsValue("EXCEPTION_ERROR_PAGE");
                     
                     if($exceptionErrorPage)
                     {
                        require_once $exceptionErrorPage;
                        $content = ob_get_clean();
                     }
                }

            }
       }
       catch (\Exception $e)
       {
           $content.= ', throw exception during error: ' . $e->getMessage() .' , errorCode: ' . $e->getCode();
       }
       
       echo $content;
       
       if($closeKernel)
       {
          return $this->closeKernel(true, false);
       }
       
       return $this;
   }
   
   
   /**
    * Prepara l'array contenente i servizi basilari del kernel, utilizzando la configurazione del kernel stesso
    * Questo metodo non deve inizializzare le classi ma deve solamente preparare la lista dei servizi che verranno caricati man mano dal kernel
    * 
    * @see \Application_Kernel::getKernelServices()
    * @see \Application_Kernel::loadService()
    * 
    * @return \Application_Kernel
    */
   protected function prepareKernelServices()
   {
      $this->_kernel_services = $this->getKernelServices();
      
      if($this->_kernel_required_services)
      {
         foreach($this->_kernel_required_services as $service)
         {
            if(!isset($this->_kernel_services[$service]))
            {
               return $this->flushErrorContent('Il servizio del kernel richiesto "'.$service.'" non è stato trovato nel kernel '.  get_called_class() . ', indicarlo nel metodo '.  get_called_class().'::getKernelServices()', 783465744);
            }
            
            $this->_kernel_services[$service]['register_callback'] = array($this,'_onServiceRegistered');
         }
      }
            
      return $this;
   }
   
   /**
    * Carica tutte le functions presenti nella cartella di sistema delle function
    * 
    * @return Application_Kernel
    */
   public function loadFunctions()
   {  
      try
      {
         /**
          * Includo prima le functions di app
          */
         $functionsDirectory = APPLICATION_APP_PATH . '/functions'; 
         $files              = $this->getUtility()->File_getFilesInDirectory($functionsDirectory, array('/.*\.php/'), SCANDIR_SORT_ASCENDING,true);
                  
         if($files && count($files) > 0)
         {
             foreach($files as $file)
             {
                 require_once $file;
             }
         }
         
         /**
          * Includo le functions native
          */
         $functionsDirectory = ROOT_PATH . DIRECTORY_SEPARATOR .'lib' .DIRECTORY_SEPARATOR . 'functions'; 
         $files              = $this->getUtility()->File_getFilesInDirectory($functionsDirectory, array('/.*\.php/'), SCANDIR_SORT_ASCENDING,true);
         
         if($files && count($files) > 0)
         {
             foreach($files as $file)
             {
                 require_once $file;
             }
         }
      }
      catch(\Exception $e)
      {
         if($this->_debug)
         {
            $this->writeLog('Non è possibile includere i files nella directory: '.$functionsDirectory,'error');
         }
      }
      
      return $this;
   }
   
   
   /**
    * Carica le function di callback del kernel, error, default exception Handler, etc
    * 
    * @return \Application_Kernel
    */
   protected function loadCallbacks()
   {  
      set_exception_handler(array(&$this,'_onException'));
      set_error_handler(array(&$this,'_onErrorHandler'));
      register_shutdown_function(array(&$this,'_onShutdown'));

      return $this;
   }
   

   /**
    * Include tutti i file di configurazione
    * 
    * @return Application_Kernel
    */
   protected function loadAllConfigurations()
   {  
      $this->config->setConfigsFileExtension(Application_Configs::CONFIGS_FILE_EXTENSION_PHP)   //I file di configurazione dell'applicazione definiscono costanti, quindi sono .php con constanti
                   ->loadAllConfigs();
      
      ini_set('default_charset', strtoupper(SITE_CHARSET));

      
      return $this;
   }
   
   
   /**
    * Restituisce il path del file di configurazione 
    * 
    * @param String  $configName    Nome del file di configurazione
    * @param String  $extension     Estenzione del file, default php
    * @param String  $package  Package. default NULL
    * 
    * @return String
    */
   public function getConfigFilePath($configName,$extension = 'php',$package = null)
   {
      if(strlen($package) == 0)
      {
         return APPLICATION_AUTOLOAD_LIBRARIES_DIRECTORY.DIRECTORY_SEPARATOR. 'conf'. DIRECTORY_SEPARATOR . $this->_environment . DIRECTORY_SEPARATOR . $configName.'.'.$extension;
      }
      else
      {
         if(!$this->isValidPackage($package))
         {
            return self::throwNewException(198291724127462386,'Questo package non è valido: '.$package);
         }
         
         return ROOT_PATH . DIRECTORY_SEPARATOR . APPLICATION_TEMPLATING_PACKAGE_DIRECTORY_NAME . DIRECTORY_SEPARATOR . $package . DIRECTORY_SEPARATOR .'conf'. DIRECTORY_SEPARATOR . $this->_environment . DIRECTORY_SEPARATOR . $configName.'.'.$extension;
      }
   }
   
   /**
    * Restituisce il path della directory di configurazione interna ad un package
    * 
    * @param String $package package
    * 
    * @return String path
    */
   public function getConfigsFilesPathByPackage($package)
   {
      return ROOT_PATH . DIRECTORY_SEPARATOR . APPLICATION_TEMPLATING_PACKAGE_DIRECTORY_NAME. DIRECTORY_SEPARATOR .  $package . DIRECTORY_SEPARATOR . 'conf';
   }

   /**
    * Restituisce il nome della class del package specificato
    * 
    * @param String $package nome del package
    * 
    * @return String
    */
   public function getBuildPackageClassFileName($package)
   {
      return $package;
//      return $this->getUtility()->String_StringToCamelcase($package);
   }
   
   /**
    * Restituisce il path assoluto della classe wrapper del package
    * 
    * @param String $package package
    * 
    * @return String
    */
   public function getBuildPackageClassFilePath($package)
   {
      return $this->getPackagesDirectory() . DIRECTORY_SEPARATOR . $package. DIRECTORY_SEPARATOR . $this->getBuildPackageClassFileName($package). '.php';
   }
   
   /**
    * Restituisce il path assoluto in cui sono presenti i packages
    * 
    * @return String
    */
   public static function getPackagesDirectory()
   {
      return ROOT_PATH . DIRECTORY_SEPARATOR . APPLICATION_TEMPLATING_PACKAGE_DIRECTORY_NAME;
   }
   
   /**
    * Restituisce le informazioni del package
    * 
    * @param String  $package   Nome del package
    * 
    * @return ArrayObject|false
    * 
    * @throws \Exception
    */
   public function getPackageInfo($packageName)
   {
        $packageClassFilePath = $this->getBuildPackageClassFilePath($packageName);
        $packageClassInstance = null;
        
        if(!file_exists($packageClassFilePath))
        {
           if($this->isDebugActive())
           {
               return self::throwNewException(4349450274602375234, 'Non è possibile registrare il package '.$packageName.' poichè non è stato trovato il file php che contiene la classe nel path: '.$packageClassFilePath);
           }
        }
       
        $packageClassName  = $this->autoload->getClassesInFile($packageClassFilePath,'\Abstract_Package',true);
        
        require_once $packageClassFilePath;

        if(!class_exists($packageClassName,false))
        {
           return $this->throwNewException(12028348239428347328, 'Impossibile trovare la classe '.$packageClassName.' per il package richiesta: '.$packageName.' nel file presente ',$packageClassFilePath);
        }
        
        $packageClassInstance = $packageClassName::getInstance($packageName); /*@var $packageClassInstance \Abstract_Package*/
        $packageName          = $packageClassInstance->getName();
        
        return new ArrayObject(array(
            'name'      => $packageName,
            'path'      => $packageClassFilePath,
            'class'     => $packageClassName,
            'instance'  => $packageClassInstance
        ));
   }
   
   
   /**
    * Controlla che il package sia esistente, quindi esista la classe e la directory specificata in base alla configurazione
    * 
    * @param String $package nome del package
    * 
    * @return Boolean
    */
   public function isValidPackage($package)
   {
       try
       {
          $packageInfo = $this->getPackageInfo($package);
          
          if($packageInfo)
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
    * Indica se il package specificato esiste nella lista dei package già caricati dal kernel
    * 
    * @param String $package nome del package
    * 
    * @return Boolean
    */
   public function isRegisteredPackage($package)
   {
       return $this->_packagesIterator->offsetExists($package) ? true : false;
   }
   
   
   /**
    * Effettua il redirect rilasciando l'header di redirect 302
    * 
    * @param String $url        url di redirect, assoluto, relativo
    * @param String $method     Redirect method	 'auto', 'location' or 'refresh'
    * @param Int    $code       HTTP Response status code
    * 
    * @throws Exception Se header già rilasciato
    * 
    * @return boolean
    */
   public function redirect($url,$method = 'auto', $code = null)
   {
        if(headers_sent())
        {
            return self::throwNewException(9234820934820348,'Impossibile effettuare redirect, header già inizializzato!');
        }

        //[\Interface_HooksType::HOOK_TYPE_PRE_CONTROLLER] ++++++++++++++++++++++++++++++++++++++
        $hookData = $this->processHooks(\Interface_HooksType::HOOK_TYPE_PRE_REDIRECT,array('url' => $url,'method' => $method,'code' => $code))->getData();
        //[\Interface_HooksType::HOOK_TYPE_PRE_CONTROLLER] ++++++++++++++++++++++++++++++++++++++
        
        
        $url    = $hookData['url'];
        $method = $hookData['method'];
        $code   = $hookData['code'];
        
        // IIS environment likely? Use 'refresh' for better compatibility
        if ($method === 'auto' && $this->httprequest->get('SERVER_SOFTWARE') && strpos($this->httprequest->get('SERVER_SOFTWARE'), 'Microsoft-IIS') !== FALSE)
        {
            $method = 'refresh';
        }
        elseif ($method !== 'refresh' && (empty($code) OR ! is_numeric($code)))
        {
            if ($this->httprequest->get('SERVER_PROTOCOL') && $this->httprequest->get('SERVER_PROTOCOL') && $this->httprequest->get('SERVER_PROTOCOL') === 'HTTP/1.1')
            {
               $code = ($this->httprequest->get('REQUEST_METHOD')!== 'GET')
                         ? 303	// reference: http://en.wikipedia.org/wiki/Post/Redirect/Get
                         : 307;
            }
            else
            {
                $code = Interface_HttpStatus::HTTP_ERROR_REDIRECT;
            }
        }
        
        switch ($method)
        {
            case 'refresh':
                    header('Refresh:0;url='.$url);
            break;
            default:
                    header('Location: '.$url, true, $code);
            break;
        }
         
        return $this->closeKernel(true);    
   }
   
   /**
    * Ricerca un Service, un Plugin, una Classe, un package, un Command, un Hook o una configurazione , registrato nel Kernel
    * 
    * @param String $something  Qualcosa da ricercare nel Kernel
    *                           <ul>
    *                              <li> L'instanza di un Service con il prefisso "@" es: @serviceName</li>
    *                              <li> L'instanza di un Plugin con il prefisso "*" es: *PluginName</li>
    *                              <li> L'instanza di una Classe con il prefisso "$" es: $className</li>
    *                              <li> L'instanza di un package con il prefisso "+" es: +PackageName</li>
    *                              <li> L'instanza di un Command con il prefisso ">" es: >CommandName</li> 
    *                              <li> L'instanza di un Hook con il prefisso "?" es: ?HookName</li> 
    *                              <li> Il Valore di una configurazione con il prefisso "%" %configName (upper/lowercase)</li>
    *                           </ul>
    * @param array  $params     [OPZIONALE] Parametri aggiuntivi
    * @param Mixed  $default    [OPZIONALE] Valore da restituire di default, qualora non venisse trovato $something, se non viene specificato sarà lanciata un eccezione
    * 
    * @return Mixed
    * 
    * @throws \Exception
    */
   public function get($something,array $params = array(),$default = self::DEFAULT_EXCEPTION_BEHAVIOUR)
   {       
       $serchService       = strstr($something,"@")!== false ? true : false;
       $searchPlugin       = strstr($something,"*")!== false ? true : false;
       $searchClass        = strstr($something,"$")!== false ? true : false;
       $searchPackage      = strstr($something,"+")!== false ? true : false;
       $searchConfig       = strstr($something,"%")!== false ? true : false;
       $searchCommand      = strstr($something,">")!== false ? true : false;
       $searchHook         = strstr($something,"?")!== false ? true : false;
       
       $somethingOriginal  = $something;
       
       $searchAuto    = preg_match('/\@|\+|\$|\%|\>|\?|\*/',$something) ? false : true;
       
       if(!$searchAuto)
       {
          $something = preg_replace('/\@|\+|\$|\%|\>|\?|\*/','',$something);
       }
       
       try
       {
            $somethingFinded = $default;
                        
            /**
             * Ricerco un Service
             */
            if($searchAuto || $serchService)
            {
                 if($this->_app_services)
                 {
                     if($this->_app_services->hasService($something))
                     {
                         $somethingFinded = $this->_app_services->getService($something,$params);
                         $searchAuto      = false;
                     }
                     else if($serchService)
                     {
                         $somethingFinded = $this->_app_services->callServiceString($somethingOriginal,$default);
                         $searchAuto      = false;
                     }
                 }
                 
                 if($somethingFinded == $default)
                 {                             
                    if(isset($this->_kernel_services[$something]))
                    {
                        $this->_app_services->registerService($something, $this->_kernel_services[$something]);
                        return $this->get($somethingOriginal,$params,$default);
                    }
                 }
            }
            
            /**
             * Ricerco un plugin
             */
            if($searchAuto || $searchPlugin)
            {
                if($this->plugins)
                {
                    if($this->plugins->hasPlugin($something))
                    {
                        $somethingFinded  = $this->plugins->getPluginInstance($something,$params);
                        $searchAuto      = false;
                    }
                }
            }

            /**
             * Ricerco una classe specifica
             */
            if($searchAuto || $searchClass)
            {
                try
                {
                    if($this->autoload)
                    {
                        $somethingFinded = $this->autoload->getLoadClassInstance($something,$params,true);
                        $searchAuto      = false;
                    }
                }
                catch(\Exception $e)
                {
                    $searchAuto      = true;
                }
            }

            /**
             * Ricerco una configurazione
             */
            if($searchAuto || $searchConfig)
            {
                 if($this->config)
                 {
                    $somethingFinded =  $this->config->getConfigsValue($something,$somethingFinded);
                    $searchAuto      = false;
                 }                     
            }

            /**
             * Ricerco un package
             */
            if($searchAuto || $searchPackage)
            {
                if($this->_packagesIterator && $this->_packagesIterator->count() > 0)
                {
                    if($this->_packagesIterator->offsetExists($something))
                    {
                       $somethingFinded = $this->getPackageInstance($something);
                       $searchAuto      = false;
                    }
                }
            }
            
            
            if($searchAuto || $searchHook)
            {
                if($this->hooks)
                {
                   if($this->hooks->hasHook($something))
                   {
                      $somethingFinded = $this->hooks->getHook($something);
                      $searchAuto      = false;
                   }
                }
            }
            
            
            if($searchAuto || $searchCommand)
            {
                if($this->commands) 
                {
                    if($this->commands->hasCommand($something))
                    {
                        $somethingFinded = $this->commands->getCommand($something);
                        $searchAuto      = false;
                    }
                }
            }

            if($somethingFinded == $default && $default == self::DEFAULT_EXCEPTION_BEHAVIOUR)
            {
               self::throwNewException(23982003467234, 'Non è possibile trovare il valore ricercato dal kernel: '.$somethingOriginal);
            }
            
       }
       catch (\Exception $e)
       {
           if($default == self::DEFAULT_EXCEPTION_BEHAVIOUR)
           {
               throw $e;
           }
           else
           {
               $somethingFinded = $default;
           }
       }
       
       return $somethingFinded;
   }
   
   /**
    * Verifica che il kernel abbia un Service, un Plugin, una Classe, un package, un Command, un Hook o una configurazione
    * 
    * @param String $something  Qualcosa da ricercare nel Kernel
    *                           <ul>
    *                              <li> L'instanza di un Service con il prefisso "@" es: @serviceName</li>
    *                              <li> L'instanza di un Plugin con il prefisso "*" es: *PluginName</li>
    *                              <li> L'instanza di una Classe con il prefisso "$" es: $className</li>
    *                              <li> L'instanza di un package con il prefisso "+" es: +PackageName</li>
    *                              <li> L'instanza di un Command con il prefisso ">" es: >CommandName</li> 
    *                              <li> L'instanza di un Hook con il prefisso "?" es: ?HookName</li> 
    *                              <li> Il Valore di una configurazione con il prefisso "%" %configName (upper/lowercase)</li>
    *                           </ul>
    * @return Boolean
    */
   public function has($something)
   {
       return $this->get($something,array(),'__SOMETHING_NOT_EXISTS__') !== '__SOMETHING_NOT_EXISTS__';
   }
   
   
   /**
    * Restituisce la lista delle cartelle che contengono i diversi Packages
    * 
    * @return Array
    */
   protected function getAllPackagesDirectories()
   {
      $packagesActive = $this->getUtility()->Directory_getSubdirectoriesList($this->getPackagesDirectory());
      return $packagesActive;
   }
   
   /**
    * Registra il path come autoloading per la ricerca delle classi Application 
    * <b>(Questo metodo è da usare esclusamente per il caricamento automatico delle classi Application*)</b>
    * 
    * @param String $path       Path 
    * @param String $extension  Estenzione dei file php, default 'app'
    * 
    * @return \Application_Kernel
    */
   protected function autoloadRegister($path,$extension = 'app')
   {
       spl_autoload_register(function($appClass) use ($path,$extension){
           
           $appClassPath = $path. DIRECTORY_SEPARATOR . $appClass  . (strlen($extension)>0 ? '.'.$extension : '') .'.php';

           if(file_exists($appClassPath))
           {
               require_once $appClassPath;
           }
           
       },false);
       
       return $this;
   }
   
   /**
    * Sfrutta il magic method per ottenere un service dall'Application Services
    * 
    * @param String $name Nome del servizio
    * 
    * @return Mixed
    */
   public function __get($name) 
   {
       if($this->_app_services)
       {
            if(!$this->_app_services->hasService($name))
            {           
                if(isset($this->_kernel_services[$name]))
                {
                    $this->_app_services->registerService($name, $this->_kernel_services[$name]);
                }
            }
       
            return $this->_app_services->$name;
       }
       
       return self::throwNewException(23482834230023283, 'Non è possibile ottenere il service "'.$name.'"');
   }
   
   /**
    * Verifica che un servizio sia registrato
    * 
    * @param String $name Nome del servizio
    * 
    * @return Boolean
    */
   public function __isset($name) 
   {
       if($this->_app_services)
       {
           return $this->_app_services->hasService($name);
       }
       
       return self::throwNewException(2384823472983472934, 'Non è possibile verificare l\'esistenza del servizio  "'.$name.'" se non è registrato il gestore dei servizi del kernel (services)');
   }
   
   /**
    * Registra un servizio nell'Application Services o imposta un attributo non dichiarato.
    * 
    * @param String $name  Nome del servizio
    * @param Mixed  $value Servizio
    * 
    * @return \Application_Kernel
    */
   public function __set($name,$value)
   {
       $this->{$name} = $value;

       if($this->_app_services)
       {
           $this->_app_services->registerService($name, $value, true);
       }
       
       return $this;
   } 
}
