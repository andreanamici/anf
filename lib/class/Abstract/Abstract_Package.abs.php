<?php

/**
 * Classe astratta che gestisce i package
 */
abstract class Abstract_Package implements Interface_Package
{
   use Trait_Singleton,Trait_ObjectUtilities;
   
   use Trait_ApplicationKernel, 
           
       Trait_ApplicationConfigs, 
           
       Trait_ApplicationHooks, 
       
       Trait_ApplicationCommands,    
           
       Trait_ApplicationRouting,
           
       Trait_ApplicationPlugins,
   
       Trait_ApplicationServices;
   
   /**
    * Nome reale del package
    * @var String
    */
   protected $_name          = null;
   
   /**
    * Nome camelCase
    * @var String
    */
   protected $_nameCamelCase = null;
   
   /**
    * Indica se abilitare il kernel alla registrazione di questo package
    * @var Boolean
    */
   protected $_isEnable      = true;
   
   /**
    * Environment di default del Package
    * @var String
    */
   protected $_defaultConfigsEnvironment = \Application_Kernel::DEFAULT_ENVIRONMENT;
   
   /**
    * Indica nome dell'estensione dei file di configurazione di questo package
    * @var String
    */
   protected $_configsFileExtensions     = self::CONFIGS_FILE_EXTENSION_DEFAULT;
   
   
   /**
    * Indica se è abilitato il package, implementare per eventuali logiche specifiche
    * 
    * @return boolean
    */
   public function isEnable() 
   {
      return $this->_isEnable;
   }
   
   /**
    * Restitusce l'environment di default di questo package
    * @return String
    */
   public function getConfigsDefaultEnvironment()
   {
        return $this->_defaultConfigsEnvironment;
   }
   
   /**
    * Imposta l'environment di default dei file di configurazione
    * 
    * @param String $env
    * 
    * @return \Abstract_Package
    */
   public function setDefaultConfigsEnvironment($env)
   {
       $this->_defaultConfigsEnvironment = $env;
       return $this;
   }
   
   
   /**
    * Imposta se il package è abilitato a registrarsi
    * 
    * @param Boolean $isEnable TRUE/FALSE
    * 
    * @return \Abstract_Package
    */
   public function setIsEnable($isEnable)
   {
       $this->_isEnable = $isEnable;
       return $this;
   }
   
   /**
    * Questo metodo viene invocato al termine del caricamento del package dal Kernel
    * 
    * @return boolean
    */
   public function onLoad() 
   {
       $targetLink = $this->getAbsolutePath().'/resources/public';
       $link       = ROOT_PATH.'/web/assets/'.$this->getName();
       
       if(!is_dir($link) && is_dir($targetLink))
       {
           symlink($targetLink, $link);
       }
       
       return true;
   }
   
   /**
    * Questo metodo viene invocato prima del caricamento del package dal Kernel
    * 
    * @return boolean
    */
   public function onBeforeLoad()
   {
       return true;
   }
   
   /**
    * Restituisce il nome del package attuale
    * 
    * @param Boolean $camelCase [OPZIONALE] indica se restituire il nome del package originale o nel camel-case mode, default FALSE originale
    * 
    * @return string
    */
   public function getName($camelCase = false)
   {
      return $camelCase ? $this->_nameCamelCase : $this->_name;
   }
   
   /**
    * Imposta il nome del templateName
    * 
    * @param String $packageName Nome del package
    * 
    * @return \Abstract_Package
    */
   private function setPackageName($packageName)
   {
      $this->_name              = $packageName;
      $this->_nameCamelCase     = $this->getUtility()->String_StringToCamelcase($packageName); 
      
      return $this;
   }
   
   
   /**
    * Path assoluto del package instanziato
    * 
    * @return String
    */
   public function getAbsolutePath()
   {
      $reflectionClass = new ReflectionClass($this);
      return realpath(dirname($reflectionClass->getFileName()));
   }
   
   /**
    * Restituisce il path assoluto della directory che contiene tutti gli Action Objects
    * 
    * @return String
    */
   public function getActionObjectAbsolutePath()
   {
       return $this->getAbsolutePath() . DIRECTORY_SEPARATOR . $this->getApplicationConfigs()->getConfigsValue('ACTION_CNT_ACTION_OBJECT_FOLDER_NAME');
   }
   
   
   /**
    * Restituisce il path assoluto della directory dei file di configurazione
    * 
    * @param String $environment [OPZIONALE] Nome della cartella delle configurazioni (Nome dell'environment del Kernel), default NULL
    * @param String $default     [OPZIONALE] Path alternativo restituito qualora l'environment del Kernel non fosse valido (non esiste la cartella del nome dell'environemnt), default FALSE
    * 
    * @return String|boolean
    */
   public function getConfigsDirectoryPath($environment = null,$default = false)
   {
      $path =  $this->getAbsolutePath() . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . ($environment == null ? $this->getApplicationKernel()->getEnvironment() : $environment);
      
      if(file_exists($path))
      {
          return $path;
      }
      
      
      return $default;
   }
   
   /**
    * Restituisce il path delle configurazioni di default
    * 
    * @return String
    */
   public function getConfigsDirectoryPathDefault()
   {
       return $this->getAbsolutePath(). DIRECTORY_SEPARATOR . 'conf' . DIRECTORY_SEPARATOR . $this->getConfigsDefaultEnvironment();
   }
   
   
   /**
    * Restituisce il path assoluto della directory dei comands di questo package
    * 
    * @return String
    */
   public function getCommandsPath()
   {
      return $this->getAbsolutePath() . DIRECTORY_SEPARATOR . 'commands';
   }
   
   /**
    * Restituisce il path assoluto della directory dei comands di questo package
    * 
    * @return String
    */
   public function getHooksPath()
   {
      return $this->getAbsolutePath() . DIRECTORY_SEPARATOR . 'hooks';
   }
   
   
   /**
    * Restituisce il path assoluto in cui sono presenti le cartelle dei plugins
    * 
    * @return String
    */
   public function getPluginsPath()
   {
      return $this->getAbsolutePath() . DIRECTORY_SEPARATOR . 'plugins';
   }
   
   
   /**
    * Restituisce il path assoluto delle librerie del package
    * 
    * @return String
    */
   public function getLibrariesPath()
   {
      return $this->getAbsolutePath() . DIRECTORY_SEPARATOR . 'lib';
   }
   
   /**
    * Restituisce il path assoluto della directory in cui sono contenuti i file di locale delle traduzioni
    * 
    * @return String
    */
   public function getLocalesPath()
   {
      return $this->getAbsolutePath() . DIRECTORY_SEPARATOR . 'locale';
   }
   
   /**
    * Restituisce il path delle viste per il package
    * 
    * @return String
    */
   public function getViewsPath()
   {
       return $this->getAbsolutePath(). DIRECTORY_SEPARATOR . APPLICATION_TEMPLATING_TPL_PATH;
   }
   
   /**
    * Restituisce l'url assoluto / relativo della directory degli assets del package
    * 
    * @param Boolean $absolute
    * 
    * @return String
    */
   public function getResourceUrl($absolute = false)
   {
       $httpRequest = $this->getApplicationKernel()->httprequest;
       return ($absolute ? $httpRequest->getBaseUrl() : $httpRequest->getPath()). APPLICATION_RESOURCES_ASSETS_RELATIVE_URL . DIRECTORY_SEPARATOR .  $this->getName(); 
   }
   
   /**
    * Restituisce il path assoluto della directory delle risorse del package
    * 
    * @return String
    */
   public function getResourcePath()
   {
       return $this->getAbsolutePath() . DIRECTORY_SEPARATOR .  APPLICATION_TEMPLATING_ASSETS_PATH; 
   }
   
   /**
    * Restituisce il path delle viste degli errori per il package
    * 
    * @param String $error Nome della vista da restituire , es: 'error' / 404 / 400
    * 
    * @return String
    */
   public function getViewsErrorPath($error = null)
   {
       $viewErrorPath = $this->getAbsolutePath() . DIRECTORY_SEPARATOR . APPLICATION_TEMPLATING_TPL_DIR_ERROR;
       
       if(!is_null($error) && strlen($error) > 0)
       {
           $viewErrorPath.= DIRECTORY_SEPARATOR . $error . '.php';
       }
       
       return $viewErrorPath;
   }
   
   
   /**
    * Restituisce il formato di estensione dei file di configurazione presente nel package
    * 
    * Sovrascrivibile dal package figlio
    * 
    * @return String
    */
   public function getConfigsFileExtension()
   {
      return $this->_configsFileExtensions;
   }
   
   
   /**
    * Restituisce TRUE se gli hooks si possono autoregistrare senza un file di configurazione nel package
    * 
    * Sovrascrivibile dal package figlio
    * 
    * @return Boolean
    */
   public function getHooksAutoregister()
   {
      return defined("APPLICATION_HOOKS_REGISTER_WITHOUT_FILE") && APPLICATION_HOOKS_REGISTER_WITHOUT_FILE;
   }
   
   public function __construct($packageName = null)
   {
       $this->setPackageName(is_null($packageName) ? get_called_class() : $packageName);
   }
   
   /**
    * Inizializza il package instanziato
    * 
    * @param String $tplName nome del package
    * 
    * @return \Abstract_package
    */
   public function initMe($packageName = null)
   {
      $packageName = is_null($packageName) ? $this->getName() : $packageName;
      
      $this->setPackageName($packageName)
           ->registerFunctions()
           ->registerConfigs()     
           ->registerActions()
           ->registerControllers()
           ->registerPlugins()
           ->registerLibraries()
           ->registerHooks()
           ->registerRoutes()
           ->registerCommands()
           ->registerLocales()
           ->registerServices();
      
      return $this;
   }
   
   /**
    * Registra le functions globali del package
    * 
    * @return \Abstract_Package
    */
   protected function registerFunctions()
   {
       $functionsPaths = array(
           $this->getAbsolutePath(). DIRECTORY_SEPARATOR . 'functions',
           $this->getAbsolutePath(). DIRECTORY_SEPARATOR . 'helpers' 
       );
       
       foreach($functionsPaths as $path)
       {
           $files = $this->getUtility()->File_getFilesInDirectory($path);
           
           if(is_array($files) && count($files) > 0)
           {
               foreach($files as $file)
               {
                   require_once $path . DIRECTORY_SEPARATOR . $file;
               }
           }
       }
       
       return $this;
   }
   
   
   /**
    * Registra dei parametri personalizzate di questo template-mode che possono essere sovrascritti da altri package
    * 
    * @return \Abstract_Package
    */
   protected function registerConfigs()
   {
      $this->getApplicationConfigs()->loadAllConfigsForPackage($this);
      return $this;
   }
   
   /**
    * Registra le action cosi da essere utilizzate dall'autoload
    */
   protected function registerActions()
   {
      $this->getApplicationAutoload()->addAutoloadPath('Action', array(
                                                       'path'       =>  $this->getActionObjectAbsolutePath(),
                                                       'extension'  =>  array(
                                                          'act.php',
                                                          'php'
                                                       )
      ));
      
      return $this;
   }
   
   /**
    * Registra le action cosi da essere utilizzate dall'autoload
    */
   protected function registerControllers()
   {
      $this->getApplicationAutoload()->addAutoloadPath('Controllers', array(
                                                      'path'       =>  $this->getAbsolutePath().'/controllers',
                                                      'extension'  =>  'cnt.php'
      ));
      
      $this->getApplicationAutoload()->addAutoloadPath('', array(
                                                      'path'       =>  $this->getAbsolutePath().'/controllers',
                                                      'extension'  =>  'php'
      ));
      
      return $this;
   }
   
   /**
    * Registra il caricamento delle librerie specifiche per questo package
    * 
    * @return \Abstract_Package
    */
   protected function registerLibraries()
   {
      $this->getApplicationAutoload()
              
        ->addAutoloadPath('Abstract', array(
                                              'path'       =>  $this->getLibrariesPath().'/Abstract',
                                              'extension'  =>  'abs.php'
      ))->addAutoloadPath('Interface', array(
                                              'path'       =>  $this->getLibrariesPath().'/Interface',
                                              'extension'  =>  'int.php'
      ))->addAutoloadPath('Trait', array(
                                              'path'       =>  $this->getLibrariesPath().'/Trait',
                                              'extension'  =>  'trait.php'
      ))->addAutoloadPath('Utility', array(
                                              'path'       =>  $this->getLibrariesPath().'/Utility',
                                              'extension'  =>  'class.php'        
      ))->addAutoloadPath('EntitiesManager', array(
                                              'path'       =>  $this->getLibrariesPath().'/EntitiesManager',
                                              'extension'  =>  'man.php'
      ))->addAutoloadPath('Entities', array(
                                              'path'       =>  $this->getLibrariesPath().'/Entities',
                                              'extension'  =>  'ent.php'
      ))
      ->addAutoloadPath('Facade', array(
                                              'path'       =>  $this->getLibrariesPath().'/Facade',
                                              'extension'  =>  'class.php'
      ));
      
      return $this;
   }
   
   
   /**
    * Registra i comandi del package, utilizzabili ovunque
    * 
    * @return \Abstract_Package
    */
   protected function registerCommands()
   {  
      $this->getApplicationCommands()->registerCommandsForPackage($this);
      return $this;
   }
   
   /**
    * Carica le rotte personalizzate di questo package
    * 
    * @return \Abstract_Package
    */
   protected function registerRoutes()
   {
      $this->getApplicationRouting()->addRoutingMapForPackage($this);
      return $this;
   }
   
   
   /**
    * Registra tutti gli hooks presenti in questo package
    * 
    * @return Abstract_Package
    */
   protected function registerHooks()
   {
      $hooksRegistered = $this->getApplicationHooks()->registerHooksForPackage($this); 
      return $this;
   }
   
   
   /**
    * Registra dei files di locale a quelli attualmente registrati, facendo override su base dominio delle stringhe
    * 
    * @return Abstract_Package
    */
   protected function registerLocales()
   {
      $appLanguages  = $this->getApplicationKernel()->getApplicationLanguages();
      
      $appLanguages->loadAllLocalesForPackage($this);
      
      return $this;
   }
   
   /**
    * Registra i plugin per questo package
    * 
    * @return \Abstract_Package
    */
   protected function registerPlugins()
   {
      $appPlugins = $this->getApplicationPlugins()->registerPluginsForPackage($this);
      return $this;
   }
   
   
   /**
    * Registra i servizi configurati per questo package
    * 
    * @return \Abstract_Package
    */
   protected function registerServices()
   {
       $appServices = $this->getApplicationServices()->registerServicesForPackage($this);
       
       $this->getApplicationServices()->registerService($this->_nameCamelCase, $this);
       
       return $this;
   }
   
   
   public function __toString()
   {
       return $this->getName();
   }
   
}