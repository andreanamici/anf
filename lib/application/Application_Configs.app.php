<?php

require_once __DIR__.'/../class/Interface/Interface_ApplicationConfigs.int.php';

/**
 * Questa classe si occupa di gestire le configurazioni dell'applicazioni, gestendo eventuali 
 * configurazioni in formati php o yml, effettuando il parse qualora i file non siano stati creati in cache
 * o non utilizzando la cache dei file di configurazione se il sistema è in debug.
 * 
 * @method Application_Configs getInstance() Restituisce l'istanza del gestore delle configurazioni
 * 
 */
class Application_Configs implements Interface_ApplicationConfigs
{
   
   use Trait_Singleton,Trait_ObjectUtilities,
           
       Trait_ApplicationKernel, Trait_ApplicationHooks,Trait_ApplicationPlugins;
   
   /**
    * Path in cui sono presenti le configurazioni dell'applicazione
    * @var String
    */
   protected $_configs_dir_path          = null;
    
   /**
    * Path in cui sono storate le configurazioni elaborate e cachate
    * @var String
    */
   protected $_configs_cache_dir_path    = null;
   
   /**
    * Estenzione dei file di configurazione
    * @var String
    */
   protected $_configs_file_extension     = self::CONFIGS_FILE_EXTENSION_DEFAULT;
   
   /**
    * Environment usato dalle configurazioni, può essere diverso da quello dek Kernel (es: all)
    * @var string
    */
   protected $_configs_kernel_environment = null;
   
   /**
    * Indica se è stato caricato il file principale, se esistente
    * 
    * @var Bool
    */
   protected $_main_configs_loaded = false;
   
   /**
    * Array object contente tutte le configurazioni dell'applicazione
    * 
    * @var \ArrayObject
    */
   protected $_CONFIGS_ARRAY_OBJECT       = null;
   
   /**
    * Restitutisce tutte le estenzioni dei file di configurazioni attive e valide 
    * 
    * @return Array
    */
   public static function getAllConfigsExtensions()
   {
      $appConfigs   = new ReflectionClass(__CLASS__);
      $allConstants = $appConfigs->getConstants();
      
      $configsExtensions = array();
      foreach($allConstants as $name => $value)
      {
         if(preg_match("/CONFIGS\_FILE\_EXTENSION/",$name))
         {
            $configsExtensions[] = $value;
         }
      }
      
      return $configsExtensions;
   }
   
   
   /**
    * Questa classe si occupa di gestire le configurazioni dell'applicazioni
    * 
    * @return Application_Configs
    */
   public function __construct()
   {
       $this->_CONFIGS_ARRAY_OBJECT = new ArrayObject(array());
       return $this->initMe();
   }
   
   
   /**
    * Inizializza la configurazioni
    * 
    * @param String   $configsPathDirPath Directory in cui trovare le configurazioni, deault null
    * @param String   $extension          Estensione dei file di configurazione, default self::CONFIGS_FILE_EXTENSION_DEFAULT
    * @param Boolean  $useEnvironment     Indica se ricercare le configurazioni in una sottodirectory con il nome dell'environment del Kernel, default true
    * 
    * @return Application_Configs
    */
   public function initMe($configsPathDirPath = null,$extension = self::CONFIGS_FILE_EXTENSION_DEFAULT,$useEnvironment = true)
   {  
      $this->setConfigsFileExtension($extension);
      
      $kernelEnvironment    = $this->getKernelEnvironment();
      $configsPathDirPath   = $configsPathDirPath ? $configsPathDirPath : $this->getConfigsDefaultPath();
      
      if($configsPathDirPath[strlen($configsPathDirPath)-1] !='/')
      {
          $configsPathDirPath.="/";
      }
            
      if($useEnvironment)
      {
         $configsPathDirPath.= $kernelEnvironment;
      }
      
      /**
       * Se non esiste la directory di configurazione per questo environment, cerco quella di fallback del kernel stesso
       */
      if(!file_exists($configsPathDirPath))
      {
          self::throwNewException(293859027350234, 'La directory utilizzata per le configurazioni non è valida: '.$configsPathDirPath);
      }
      
      
      $this->_configs_kernel_environment = $kernelEnvironment;
      $this->setConfigsDirPath($configsPathDirPath);

      $this->initCache();
      
      $this->loadMainConfigs();
      
      return $this;
   }
   
   /**
    * Inizializza i dati utili per usare il caching delle configurazioni
    * @return \Application_Configs
    */
   protected function initCache()
   {
      if(defined("CACHE_DIRECTORY"))
      {
         $confisCacheDirPath   = CACHE_DIRECTORY.'/configs/'.$this->_configs_kernel_environment;
         $this->setConfigsCacheDirPath($confisCacheDirPath);
      }
   }
   
   /**
    * Restituisce il path in cui sono presenti le configurazioni di default dell'applicazione
    * 
    * @return string
    */
   public function getConfigsDefaultPath()
   {
      return APPLICATION_APP_PATH . DIRECTORY_SEPARATOR . 'configs';
   }
   
   /**
    * Imposta la directory in cui sano presenti le configurazioni
    * 
    * @param String $directory Directory
    * 
    * @return \Application_Configs
    */
   public function setConfigsDirPath($directory)
   {
      $this->_configs_dir_path = $directory;
      return $this;
   }
   

   /**
    * Imposta la directory in cui sano presenti le configurazioni elaborate e cachate
    * 
    * @param String $directory Directory
    * 
    * @return \Application_Configs
    */
   public function setConfigsCacheDirPath($directory)
   {
      $this->_configs_cache_dir_path = $directory;
      return $this;
   }   
   
   
   /**
    * Imposta l'estenzione dei file di configurazione sorgenti
    * <br>
    * <b>l'estenzione viene applicata solamente ai file creati e gestiti dall'utente, non quelli di caching</b>
    * 
    * @param String $extension Estenzione
    * 
    * @return \Application_Configs
    */
   public function setConfigsFileExtension($extension)
   {
      $this->_configs_file_extension = $extension;
      return $this;
   }
   
      
   
   /**
    * Restitusce la directory in cui sono presenti le configurazioni
    * 
    * @return String
    */
   public function getConfigsDirPath(){
      return $this->_configs_dir_path;
   }
      
   /**
    * Restitusce la directory in cui sono presenti le configurazioni cachate
    * 
    * @return String
    */
   public function getConfigsCacheDirPath(){
      return $this->_configs_cache_dir_path;
   }
   
   
   /**
    * Restituisce l'estenzione dei file di configurazione
    * @return String
    */
   public function getConfigsFileExtension(){
      return $this->_configs_file_extension;
   }
   
   /**
    * Restituisce il path della directory di un file di configurazione di un package specifico
    * 
    * @param Mixed  $package    Nome del package/Instanza del template stesso
    * @parma String $environment     Environment di configurazione, default quello del \Application_Kernel
    *
    * @return String
    */
   public function getConfigsDirPathForPackage($package,$environment = null)
   {
      $packageInstance = $package;
      
      if(is_string($package))
      {
         $packageInstance = $this->getApplicationKernel()->getPackageInstance($package);
      }
      
      if(!($packageInstance instanceof \Abstract_Package))
      {
          return self::throwNewException(926234923572835, 'Non è possibile trovare il package indicato: '.$package);
      }

      return $packageInstance->getConfigsDirectoryPath($environment,$packageInstance->getConfigsDirectoryPathDefault());
   }
   
   /**
    * Restituisce il path delle directory in cache di un file di configurazione di un package specifico
    * 
    * @param String $package package
    * 
    * @return String
    */
   private function getConfigsCacheDirPathForPackage($package)
   {
      return $this->getConfigsCacheDirPath().'/'.$package;
   }
   
   
   /**
    * Restituisce il path di un file di configurazione 
    * 
    * @param String  $configFileName    Nome del file di configurazione
    * 
    * @return String
    */
   public function getConfigsFilePath($configFileName)
   {
      return $this->getConfigsDirPath().'/'.$configFileName.'.'.$this->getConfigsFileExtension();
   }
  
      
   /**
    * Restituisce il path di un file di configurazione nel package specificato
    * 
    * @param String $configFileName     String
    * @param Mixed  $package       Nome del package, es: web-default
    * @param String $environment        Environment di configurazione, default quello del \Application_Kernel
    * 
    * @return String
    */
   public function getConfigsFilePathForPackage($configFileName,$package,$environment = null)
   {
      $packageInstance = $this->getApplicationKernel()->getPackageInstance($package);
      $extension            = $packageInstance->getConfigsFileExtension();
      
      return $this->getConfigsDirPathForPackage($package,$environment).'/'.$configFileName.'.'.$extension;
   }
   
   /**
    * Restituisce il path del file di configurazione nella cartella di cache dell'applicazione
    * 
    * @param String  $configFileName    Nome del file di configurazione
    * 
    * @return String
    */
   public function getConfigsCacheFilePath($configFileName)
   {
      return $this->getConfigsCacheDirPath().'/'.$configFileName.'_'.md5($this->getConfigsDirPath()).'.'.self::CONFIGS_FILE_CACHE_EXTENSION;
   }
      
   /**
    * Restituisce il path del file di configurazione nella cartella di cache dell'applicazione relativa ad un package specifico
    * 
    * @param String  $configFileName    Nome del file di configurazione
    * @param String  $package  Nome del package
    * 
    * @return String
    */
   public function getConfigsCacheFilePathForPackage($configFileName,$package)
   {
      return $this->getConfigsCacheDirPathForPackage($package).'/'.$configFileName.'.'.self::CONFIGS_FILE_CACHE_EXTENSION;
   }
   
   
   /**
    * Verifica che una configurazione sia esistente
    * 
    * @param String $configsFileName nome del file di configs
    * 
    * @return Boolean
    */
   public function isConfigsExists($configsFileName)
   {
      $configsFilePath = $this->getConfigsFilePath($configsFileName);
      return file_exists($configsFilePath)!==false;
   }
   
   /**
    * Verifica che una configurazione sia esistente
    * 
    * @param String $configsFileName    nome del file di configs del package
    * @param String $package       Nome del package
    * @param String $environment        Environment di configurazione, default quello del \Application_Kernel
    * 
    * @return Boolean
    */
   public function isConfigsExistsForPackage($configsFileName,$package,$environment = null)
   {
      $configsFilePath = $this->getConfigsFilePathForPackage($configsFileName,$package,$environment);
      
      return file_exists($configsFilePath)!==false;
   }
   
   /**
    * Verifica che sia cambiata una configurazione presente nalla cartella di configurazione dell'applicazione
    * 
    * @param String $configsFileName Nome del file di configurazione
    * 
    * @return Boolean
    */
   public function isConfigsChange($configsFileName)
   {
      $configsFilePath        = $this->getConfigsFilePath($configsFileName);
      $configsCacheFilePath   = $this->getConfigsCacheFilePath($configsFileName);
      
      return $this->_isConfigsChangeFile($configsFilePath,$configsCacheFilePath);
   }
   
   
   /**
    * Verifica che il file di configurazione sia presente in cache
    * 
    * @param String  $configsFileName Nome del file di configurazione
    * 
    * @return boolean
    */
   public function isConfigsCached($configsFileName)
   {
      $configsFilePath        = $this->getConfigsCacheFilePath($configsFileName);
      
      if(file_exists($configsFilePath)){
         return true;
      }
      
      return false;
   }
   
   /**
    * Verifica che il file di configurazione per questo package sia già presente in cache
    * 
    * @param String  $configsFileName Nome del file di configurazione
    * @param String  $package    Nome del package, es: web-default
    * @param String  $environment     [OPZIONALE] Nome environment da utilizzare, default NULL (Quello del Kernel)
    * 
    * @return boolean
    */
   public function isConfigsCachedForPackage($configsFileName,$package,$environment = null)
   {
      $configsFilePath        = $this->getConfigsCacheFilePathForPackage($configsFileName,$package,$environment);
      
      if(file_exists($configsFilePath)){
         return true;
      }
      
      return false;
   }
   
   /**
    * Verifica che sia cambiata una configurazione presente nalla cartella di configurazione del package specificato
    * <br>
    * <b>DEBUG Attivo restituisce sempre TRUE, (solo se il file di configurazione originale nel package esiste, senza controllare eventuali cambiamenti)</b>
    * <br>
    * <b>Questo controllo sarà estenso anche al file di configurazione dell'applicazione se si sta controllando un file di configurazione che lo estende dal package</b>
    * 
    * @param String  $configsFileName Nome del file di configurazione
    * @param String  $package    Nome del package, es: web-default
    * @param Boolean $extend          [OPZIONALE] Indica se questa configurazione estende una già esistente nella cartella delle configurazioni dell'applicazione, default TRUE
    * 
    * @return Boolean
    */
   public function isConfigsChangeForPackage($configsFileName,$package,$extend = true)
   {  
      $configsFilePath        = $this->getConfigsFilePathForPackage($configsFileName,$package);
      
      /**
       * Kernel in debug mode attivo
       */
      if($this->getKernelDebugActive())
      {
         if(file_exists($configsFilePath)){
            return true;
         }
         
         return false;
      }

     /**
      * Qualora il kernel non sia in debug ed esite un file di caching, restituisco false
      * 
      * Questo processo accellera la ricerca dei cambiamenti dei file di cache
      */
      if(!$this->getKernelDebugActive())
      {
         if(!file_exists($configsFilePath)){
             return false;
         }
         
         if($this->isConfigsCachedForPackage($configsFileName,$package,$extend)){
           return false;
         }
         
         return true;
      } 
         
      if($extend && $this->isConfigsChange($configsFileName))
      {
         return true;
      }
                  
      $configsCacheFilePath   = $extend ? $this->getConfigsCacheFilePath($configsFileName) : $this->getConfigsCacheFilePathForPackage($configsFileName,$package);
      
      return $this->_isConfigsChangeFile($configsFilePath,$configsCacheFilePath);
   }
   
   
   /**
    * Verifica che sia cambiata una configurazione presente nalla cartella di configurazione di tutti i package attivi e registrati al kernel.
    * <br>
    * <b>DEBUG Attivo restituisce sempre TRUE</b>
    * <br>
    * <b>Non appena verrà trovato il cambiamento del file, verrà terminato il controllo e non sarà esteso a tutti gli altri package rimasti da controllare</b>
    * 
    * @param String  $configsFileName Nome del file di configurazione
    * @param Boolean $extend          [OPZIONALE] Indica se questa configurazione estende una già esistente nella cartella delle configurazioni dell'applicazione, default TRUE
    * 
    * @return Boolean
    */
   public function isConfigsChangeForAlmostOnePackage($configsFileName,$extend = true)
   {
      if($this->getKernelDebugActive()){
         return true;
      }
      
      if($extend && $this->isConfigsChange($configsFileName)){
         return true;
      }
              
      $allPackages = $this->getApplicationKernel()->getPackagesRegistered();
      
      while($allPackages->valid())
      {
         $package    = $allPackages->current(); /*@var $package Abstract_Package*/

         $isChangeConfigs = $this->isConfigsChangeForPackage($configsFileName,$package->getName(),$extend);

         if($isChangeConfigs){
            return true;
         }

         $allPackages->next();
      }
      
      return false;
   }
   
   /**
    * Ricerca il valore di una configurazione precedentemente salvata in cache, verificando anche che questa sia ancora valida.
    * 
    * @param String  $configsFileName Nome del file di configurazione
    * @param Mixed   $default         [OPZIONALE] Valore di default, default FALSE
    * 
    * @return Mixed FALSE se configurazione assente
    */
   public function getConfigsFromCache($configsFileName,$default = false)
   {
      $configsCacheFilePath = $this->getConfigsCacheFilePath($configsFileName);

      if($this->isConfigsChange($configsFileName))
      {
         if(file_exists($configsCacheFilePath) && !unlink($configsCacheFilePath)){
            return self::throwNewException(9283463473648932,"Impossibile eliminare il file di cache in ".$configsCacheFilePath." per la configurazione ".$configsFileName);
         }         
         return $default;
      }
      
      return $this->getConfigsData($configsCacheFilePath,$default);
   }
   
   
   /**
    * Ricerca il valore di una configurazione precedentemente salvata in cache relativa al package specificato, verificando anche che questa sia ancora valida.
    * 
    * @param String   $configsFileName Nome del file di configurazione
    * @param String   $package    package, es: web-default
    * @param Boolean  $extend          [OPZIONALE] Indica se la configurazione estende quella di default dell'applicazione, default TRUE
    * @param Boolean  $default         [OPZIONALE] Valore di default, default FALSE
    * 
    * @return Mixed FALSE se configurazione assente
    */
   public function getConfigsFromCacheForPackage($configsFileName,$package,$extend = true,$default = false)
   {
      $configsCacheFilePath = $this->getConfigsCacheFilePathForPackage($configsFileName,$package);
      
      if($this->isConfigsChangeForPackage($configsFileName,$package,$extend))
      {
         if(file_exists($configsCacheFilePath) && !unlink($configsCacheFilePath)){
            return self::throwNewException(9283463473648932,"Impossibile eliminare il file di cache in ".$configsCacheFilePath." per la configurazione ".$configsFileName." del package ".$package);
         }
         
         return $default;
      }
      
      return $this->getConfigsData($configsCacheFilePath,$default);
   }
   
   /**
    * Stora il contenuto del file di configurazione in cache, effettuando il serialize
    * 
    * @param String $configsFileName Nome del file di configurazione
    * @param Mixed  $data        Data da storare su file (verrà serializzato)
    * 
    * @return Boolean
    */
   public function storeConfigsCache($configsFileName,$data)
   {
      if($this->getKernelDebugActive())
      {
         return false;
      }
            
      $configsCacheFilePath = $this->getConfigsCacheFilePath($configsFileName);
      
      return $this->storeConfigs($configsCacheFilePath,$data);
   }
   
   /**
    * Stora il contenuto del file di configurazione in cache relativo ad un package, effettuando il serialize
    * 
    * @param String $configsFile   Name Nome del file di configurazione
    * @param String $package  Nome del package
    * @param Mixed  $data          Data da storare su file (verrà serializzato)
    * 
    * @return Boolean
    */
   public function storeConfigsCacheForPackage($configsFileName,$package,$data)
   {
      if($this->getKernelDebugActive())
      {
         return false;
      }
            
      $configsCacheFilePath = $this->getConfigsCacheFilePathForPackage($configsFileName,$package);
      
      return $this->storeConfigs($configsCacheFilePath,$data);
   }
   
   /**
    * Ricerca il valore della configurazione gestita dai file application-configs.* installati in ogni package registrato al Kernel
    * 
    * @param String $name       Nome della configurazione
    * @param Mixed  $default    [OPZIONALE] Valore di default,default FALSE
    * 
    * @return Mixed
    */
   public function getConfigsValue($name,$default = false)
   {
       if($this->_CONFIGS_ARRAY_OBJECT->offsetExists($name))
       {
            $value = $this->_CONFIGS_ARRAY_OBJECT->offsetGet($name);
            return $this->_getConfigValue($value);
       }
       
       return $default;
   }
   
   /**
    * Stora una configurazione in memoria
    * 
    * @param String  $name              Nome
    * @param Mixed   $value             Value
    * @param Boolean $defineConstants   Indica se definere una costante, default TRUE
    * 
    * @return \Application_Configs
    */
   public function addConfig($name,$value,$defineConstant = true)
   {
       if(!is_scalar($value))
       {
          $value = serialize($value);
       }
       
       $name = strtoupper($name);
              
       $this->_CONFIGS_ARRAY_OBJECT->offsetSet($name, $value);
       
       if($defineConstant)
       {                    
          if(!defined($name))
          {
             define($name, $value);
          }
       }
       
       return $this;
   }
   
   /**
    * Restituisce l'Array Object di tutte le configurazioni definite nel framework tramite le costanti UTENTE
    * 
    * @return Array
    */
   public function getAllConfigsByUserConstants()
   {
       $configs      = get_defined_constants(true);
       $configsArray = array();
       
       if(isset($configs["user"]))
       {
          $configsArray =  $configs["user"];
       }
       
       return new ArrayObject($configsArray);
   }
   
   
   public function getConfigsValueConstant($name,$default)
   {
      $name = strtoupper($name);
      
      if(defined($name))
      {
         $value = constant($name);
         return $this->_getConfigValue($value);
      }
      
      return $default;
   }
   
   
   private function _getConfigValue($value)
   {
      try
      {
         if($this->getUtility()->String_isSerialized($value))
         {
           return unserialize($value);
         }
      }
      catch (\Exception $e)
      {

      }

      return $value;
   }
   
   /**
    * Restituisce l'Array Object di tutte le configurazioni definite nel framework
    * 
    * @return \ArrayObject
    */
   public function getAllConfigs()
   {
       return $this->_CONFIGS_ARRAY_OBJECT;
   }
   
   /**
    * Restituisce una configurazione presente in un file, parsandola
    * 
    * @param Atring $configFileName nome del file della configurazione, senza estenzione
    * 
    * @return Array
    */
   public function loadConfiguration($configFileName,$store = false)
   {
        $configFilePath = $this->getConfigsFilePath($configFileName);
        $configData     = $this->getConfigsFromCache($configFileName);

        if(!$configData)
        {
            $configData = $this->parseConfig($configFilePath,$this->_configs_file_extension);
            
            if($store)
            {
                $this->storeConfigsCache($configFileName, $configData);
            }
        }
        
        return $configData;
   }
   
   public function loadConfigsFile($configFileName)
   {              
       $configFilePath = $this->getConfigsFilePath($configFileName);

       $configData      = $this->getConfigsFromCache($configFilePath);
            
       if(!$configData)
       {
            $configData = $this->parseConfig($configFilePath,$this->_configs_file_extension);
       }

       if(is_array($configData) && count($configData) > 0)
       {
            $this->loadConfigsData($configData);
       }
       
       return $this;
   }
   
   /**
    * Carica le configurazioni di base presenti nella directory principale in cui sono presenti i file *.php con le costanti
    * 
    * @throws Exception_PortalErrorException  in caso di errore lancia un eccezione
    * 
    * @return Application_Configs
    */
   public function loadAllConfigs()
   {
      $confPath = $this->getConfigsDirPath();
      
      $this->loadMainConfigs();
      
      /**
       * Includo tutti i file di configurazioni presenti
       */
      $configsFileArray = glob("{$confPath}/*.".$this->_configs_file_extension);
      
      if(is_array($configsFileArray) && count($configsFileArray) > 0)
      {
         foreach($configsFileArray as $configFilePath)
         {
            $configData = $this->getConfigsFromCache($configFilePath);
            
            if(!$configData)
            {
                $configData = $this->parseConfig($configFilePath,$this->_configs_file_extension);
            }

            if(is_array($configData) && count($configData) > 0)
            {
                $this->loadConfigsData($configData);
            }
         }
      }
      
      if(!defined("DATE_DEFAULT_TIMEZONE"))
      {
         return self::throwNewException(92938837477366222,'La costante "DATE_DEFAULT_TIMEZONE" di configurazione non è presente! deve essere definita nei file di configs ');
      }
            
      if(!defined("CACHE_DIRECTORY"))
      {
         return self::throwNewException(1929382327472222,'La costante "CACHE_DIRECTORY" di configurazione non è presente! deve essere definita nei file di configs ');
      }
      
      date_default_timezone_set(DATE_DEFAULT_TIMEZONE);
            
      $this->initCache();
      
      $this->_CONFIGS_ARRAY_OBJECT = new ArrayObject(array_merge($this->getAllConfigs()->getArrayCopy(),$this->getAllConfigsByUserConstants()->getArrayCopy()));
      
      return $this;
   }
   
   /**
    * Include il file di configurazione principale
    * 
    * @return \Application_Configs
    */
   public function loadMainConfigs()
   {
      if($this->_main_configs_loaded)
      {
          return $this;
      }
      
      /**
       * Includo il file principale per eventuali dipendenze dei file file di configurazioni
       */
      $mainConfigsFilePath = $this->getConfigsFilePath(self::CONFIGS_FILE_MAIN);

      if(file_exists($mainConfigsFilePath))
      {
         $this->loadConfigsFile(self::CONFIGS_FILE_MAIN);
         $this->_main_configs_loaded = true;
      }
      
      return $this;
   }
   
   /**
    * Carica tutte le configurazioni presenti sul file di configurazioni del package, trasformando in costanti ogni parametro
    * 
    * @param Abstract_Package $package instanza del package
    * 
    * @return \Application_Configs
    */
   public function loadAllConfigsForPackage(Abstract_Package $package)
   {
      $configsName     = self::CONFIGS_FILE_NAME_PACKAGE;
      $configsData     = false;
            
      if($this->isConfigsExistsForPackage($configsName,$package->getName()))
      {
         $configsData = $this->getConfigsFromCacheForPackage($configsName, $package->getName(),false);
         
         if(!$configsData)
         {
            $configsData = $this->getParseConfigsForPackage($configsName, $package->getName(), $package->getConfigsFileExtension());
         }
         
         $this->loadConfigsData($configsData);
      }
      
      return $this;
   }
   
   /**
    * Carica l'array nelle configurazioni della classe
    * 
    * @param array $configsData array configurazioni
    * 
    * @return \Application_Configs
    * 
    * @throws \Exception
    */
   protected function loadConfigsData(array $configsData)
   {
      foreach($configsData as $constantName => $constantValue)
      {
         try
         {
            $this->addConfig($constantName, $constantValue);
         }
         catch(\Exception $e)
         {
            if($this->getApplicationKernel()->isDebugActive())
            {
               throw $e;
            }
         }
      }
      
      return $this;
   }
   
   
   /**
    * Effettua il parse del file di configurazione specifico del package indicato e stora la configurazione elaborata, specificando il formato del file sorgente
    * 
    * @param String  $configsFileName Nome del file di configurazione
    * @param String  $package    Nome del package, es: web-default
    * @param Boolean $extension       [OPZIONALE] Indica l'estensione del file di configurazione, default self::CONFIGS_FILE_EXTENSION_DEFAULT
    * 
    * @return Mixed 
    */
   public function getParseConfigsForPackage($configsName,$package,$extension = self::CONFIGS_FILE_EXTENSION_DEFAULT)
   {
      $configsData      = Array();
      $configsFilePath  = $this->getConfigsFilePathForPackage($configsName, $package);
      
      if(!file_exists($configsFilePath))
      {
         return self::throwNewException(12324245454543434, 'File di configurazione '.$configsFilePath.' non trovato!');
      }
      
      $configsData = $this->parseConfig($configsFilePath,$extension);
      
      $this->storeConfigsCacheForPackage($configsName,$package,$configsData);
      
      return $configsData;
   }
   
   
   /**
    * Pulisce la cache relativa ai file di configurazione dell'attuale enviroment in uso del kernel
    * 
    * @return Boolean
    */
   public function flushCache()
   {
      $configsCacheDirPath = $this->getConfigsCacheDirPath();
      
      if(!file_exists($configsCacheDirPath))
      {
          return true;
      }
      
      return $this->getUtility()->rrmdir($configsCacheDirPath);
   }
   
   /**
    * Effettua il parse della configurazione
    * 
    * @param String $configsFilePath    Path assoluto file di configurazione
    * @param String $extension          Estensione, defuault self::CONFIGS_FILE_EXTENSION_DEFAULT
    * 
    * @return mixed
    */
   protected function parseConfig($configsFilePath,$extension = self::CONFIGS_FILE_EXTENSION_DEFAULT)
   {
      switch ($extension)
      {
         case self::CONFIGS_FILE_EXTENSION_PHP:    
             
             if(!file_exists($configsFilePath))
             {
                 return false;
             }
             
             $configsData = require_once($configsFilePath);   
             
             if(!is_array($configsData))
             {
                 $configsData = get_defined_constants(true);
                 $configsData = $configsData['user'];
             }
                          
         break;
            
         case self::CONFIGS_FILE_EXTENSION_YAML:   
             
               if(!$this->getApplicationPlugins())
               {
                   throw new \Exception('Non è possibile caricare la configurazione in '.$configsFilePath.' per l\'estenzione "'.$extension.'" poichè il gestore dei plugin non è ancora inizializzato nel kernel',48590345144690);
               }
             
               $this->getApplicationPlugins()->includePlugin(self::YAML_PLUGIN_NAME);

               if(!function_exists("yaml_load_file"))
               {
                  self::throwNewException(998876367382888882822,'Questa configurazione '.$configsFilePath.' richiede le function yaml_* ');
               }

               $configsData = yaml_load_file($configsFilePath); 

         break;
                                                   
         default:
                
               if(!$this->getApplicationHooks())
               {
                   throw new \Exception('Non è possibile caricare la configurazione in '.$configsFilePath.' per l\'estenzione "'.$extension.'" poichè il gestore degli hooks non è ancora inizializzato nel kernel',47464562323);
               }
             
               $configsData = $this->getApplicationHooks()->processAll(Interface_HooksType::HOOK_TYPE_CONFIG_LOAD,$configsFilePath)->getResponseData()->getData();

               if(!is_array($configsData))
               {
                   self::throwNewException(998876367382888882822,'Questa configurazione '.$configsFilePath.' non è riconosciuta ');
               }

         break;
      }
      
      return $configsData;
   }
   
   /**
    * Controlla che il file sorgente sia stato modificato o che non sia presente il file di cache
    * 
    * @param String $configsFilePath       Path file di configurazione sorgente
    * @param String $configsCacheFilePath  Path file di configurazione cachato
    * 
    * @return boolean
    */
   private function _isConfigsChangeFile($configsFilePath,$configsCacheFilePath)
   {
      
      if(!file_exists($configsFilePath)){
         return false;
      }
      
      if(!file_exists($configsCacheFilePath)){
         return true;
      }
      
      if(filemtime($configsFilePath) > filemtime($configsCacheFilePath)){
         return true;
      }
      
      return false;
   }
   
   /**
    * Restituisce i dati del file di configurazione specificato
    * 
    * @param String  $configsCacheFilePath path del file
    * @param Mixed   $default              [OPZIONALE] Valore di default, default FALSE
    * 
    * @return Mixed
    */
   private function getConfigsData($configsCacheFilePath,$default = false)
   {
      
      if(file_exists($configsCacheFilePath))
      {
         $fileContent = utf8_decode(file_get_contents($configsCacheFilePath));
         
         if($fileContent!==false)
         {
            return unserialize($fileContent);
         }
      }
            
      return $default;
   }
   
   
   /**
    * Stora il valore del file di configurazione su cache 
    * 
    * @param String $configsCacheFilePath  Path file assoluto
    * @param Mixed  $data                  Dati da storare
    * 
    * @return boolean
    */
   private function storeConfigs($configsCacheFilePath,$data)
   {
      if(!file_exists($configsCacheFilePath))
      {
         $directory = pathinfo($configsCacheFilePath,PATHINFO_DIRNAME);
         
         if(!file_exists($directory))
         {
            if(!mkdir($directory,0777,true))
            {
               return self::throwNewException(8934298923849243,"Impossibile creare la directory di configurazione in cache: ".$directory);   
            }
         }
         
         if(!fopen($configsCacheFilePath,"w+"))
         {
            return self::throwNewException(8934298923849243,"Impossibile creare il file di configurazione in cache: ".$configsCacheFilePath);
         }
      }
      
      $serializedData = utf8_encode(serialize($data));
      $res            = file_put_contents($configsCacheFilePath,$serializedData);
      
      if($res){
         return true;
      }
      
      return self::throwNewException(23498239484008238,"Impossibile storare il contentuto del file di configurazione: ".$configsFileName." ".print_r($data,true));
   }
}