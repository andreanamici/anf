<?php

/**
 * Classe di gestione delle varie classi di Cahing Methods implementate
 * 
 * Questo manager è un  wrapper per le classi Cache_<cacheengine> presenti nella cartella lib/DAO/Cache/
 */
class DAO_CacheManager extends Exception_ExceptionHandler
{
   use Trait_Singleton,Trait_ObjectUtilities;

   /**
    * Nome file delle chiavi registrate
    * @var String
    */
   public static $_cachefilename = 'cachekeys';
   
   /**
    * Default Time to live delle chiavi di caching
    * @var Int
    */
   const DEFAULT_KEY_TTL = CACHE_DEFAULT_TTL;
   
   /**
    * Tipologia di caching utilizzato, default <CACHE_ENGINE>
    * @var String
    */
   private $_cc_engine  = CACHE_ENGINE;
   
   /**
    * Reference al cache engine utilizzato, instanziato in questo manager
    * 
    * @var Interface_CacheMethod
    */
   private $_cc          = null;
   
   /**
    * Indica se scrive log delle chiave ricercate/create/eliminate
    * @var Boolean 
    */
   private $_writelog    = false;
   
   /**
    * Ccntiene il numero di chiavi fetchate per ogni esecuzione
    * 
    * @var Int
    */
   protected static $_keys_fetched  = 0;
   
   /**
    * Ccntiene il numero di chiavi storate per ogni esecuzione
    * @var Int
    */
   protected static $_keys_stored   = 0;
   
   /**
    * Restituisce il numero di chiavi fetchate per ogni esecuzione
    * 
    * @return Int
    */
   public static function getKeysFetchedNumber()
   {
      return static::$_keys_fetched;
   }
   
   
   /**
    * Restituisce il numero di chiavi storate per ogni esecuzione
    * 
    * @return Int
    */
   public static function getKeysStoredNumber()
   {
      return static::$_keys_stored;
   }
   
   
   /**
    * Verifica che la tipologia di caching indicato esista 
    * 
    * @param String $cacheEngine Cache engine, es file, memcached, apc etc..
    * 
    * @return boolean
    */
   public function isExistsCacheEngine($cacheEngine)
   {
      $ccfilepath  = $this->_getCacheEngineFilePath($cacheEngine);
      $ccClassName = $this->_getCacheEngineClassName($cacheEngine);
      
      if(file_exists($ccfilepath))
      {
         require_once $ccfilepath;
         
         if(class_exists($ccClassName,false)){
            return true;
         }
      }
   }
   
   /**
    * Reinzializza il caching Engine utilizzato da questo manager, verificando che quello indicato esista e si attivo.
    * 
    * Restituirà l'instanza del manager creata
    * 
    * @param String $cacheEngine
    * 
    * @return Interface_CacheMethod
    */
   public function changeCacheEngine($cacheEngine)
   {
      if($this->isExistsCacheEngine($cacheEngine))
      {
         $this->_cc_engine = $cacheEngine;
         $this->initCacheEngineClass();
         
         if($this->_cc->check()){
            return $this->_cc;
         }
         
         return false;
      }
      
      return self::throwNewException(33345342134143,'Caching Engine Invalido: '.$cacheEngine.' Classe non valida. ');
   }
   
   
   /**
    * Restituisce l'instanza di un cache Engine 
    * 
    * @param String $cacheEngineName [OPZIONALE] Nome del cache Engine, default quello usato dal manager
    * 
    * @return \Interface_CacheMethod
    * 
    * @throws \Exception
    */
   public function generateCacheEngine($cacheEngineName = null)
   {
      $cacheEngineName = strlen($cacheEngineName)  == 0 ? $this->_cc_engine : $cacheEngineName; 
       
      if(!$this->isExistsCacheEngine($cacheEngineName))
      {
         return self::throwNewException(3939938489910393,'Caching Engine Invalido: '.$cacheEngineName.'., Classe non valida. ');
      }
      
      $ccfilepath  = $this->_getCacheEngineFilePath($cacheEngineName);
      $ccClassName = $this->_getCacheEngineClassName($cacheEngineName);

      require_once $ccfilepath;

      $cacheEngineInstance =  method_exists($ccClassName,'getInstance') ? call_user_func(array($ccClassName,'getInstance')) : new $ccClassName();/*@var $cacheEngineInstance \Interface_CacheMethod*/
      
      if(!is_subclass_of($cacheEngineInstance,'Interface_CacheMethod'))
      {
         return self::throwNewException(3272773478392987432978234,'Non è possibile usare questo engine "'.$cacheEngineInstance.'" poichè questo deve implementare l\'interfaccia "Interface_CacheMethod"');
      }

      if(!$cacheEngineInstance->check())
      {
         return self::throwNewException(838283829918128898382817456,'Non è possibile utilizzare questo engine: "'.$cacheEngineInstance.'" poichè non è abilitato, il metodo '.  get_class($cacheEngineInstance). '::check() ha restituito FALSE ');
      }  
      
      return $cacheEngineInstance;
   }
   
   
   /**
    * Inizializza la classe per il caching Engine su base $this->_cc_engine (nome parziale della classe, es: memcached)
    * 
    * @return DAO_CacheManager
    */
   public function initCacheEngineClass()
   {
      $this->_cc = $this->generateCacheEngine($this->_cc_engine);

      return $this;
   }
   
   /**
    * Restituisce la tipologia di cache engine attualmente utilizzato
    * @return String
    */
   public function getCacheEngine()
   {
       return $this->_cc_engine;
   }
   
   /**
    * Imposta l'oggetto cache da utilizzare
    * 
    * @param \Interface_CacheMethod $cacheEngineInstance Engine caching
    * 
    * @return \DAO_CacheManager
    */
   public function setCacheEngineInstance(\Interface_CacheMethod $cacheEngineInstance)
   {
       $this->_cc = $cacheEngineInstance;
       return $this;
   }
   
   /**
    * Restituisce l'oggetto cache attualmente utilizzato
    * 
    * @return \Interface_CacheMethod
    */
   public function getCacheEngineInstance($cacheEngineName = null)
   {
      if(is_null($cacheEngineName))
      {
         return $this->_cc;
      }
      
      $cacheEngine = $this->generateCacheEngine($cacheEngineName);
      return $cacheEngine;
   }
   
   /**
    * Manager Cache Sistem - invoca la classe manager per il caching delle informazioni princpali
    */
   public function __construct()
   {
      $this->_writelog      = defined("CACHE_WRITE_LOG")     ? CACHE_WRITE_LOG    : false;
      self::$_cachefilename = defined("CACHE_LOG_FILENAME")  ? CACHE_LOG_FILENAME : self::$_cachefilename;
      
      return $this->_initCacheManagerInstance();
   }
   
   /**
    * Restituisce se la cache è attivata
    * @return Boolean TRUE cache attiva, false altrimenti
    */
   public static function isActive()
   {
      if(defined("CACHE_ACTIVE") && CACHE_ACTIVE){
         return true;
      }
      
      return false;
   }
   
   
   /**
    * Restituisce il nome della chiave su base parametri, concatenando i valori
    * 
    * @param  Mixed  Lista di Tutti i parametri che concorrono alla costruzione della chiave per la cache
    * 
    * @return string
    */
   public function prepareKey()
   {
      $params = func_get_args();
      return implode("_",$params);
   }
   
   /**
    * Ricerca la chiave specifica nella cache, se presente restituisce il valore, FALSE altrimenti
    * 
    * @param String $key         Valore chiave
    * @param Int    $compressed  [OPZIONALE] indica se è compresso
    * 
    * @return Mixed or FALSE
    */
   public function fetch($key,$compressed = 0) 
   {
      $rc = $this->_cc->fetch($key,$compressed);
     
      if($this->_writelog)
      {
         $this->writeLog(' [CACHE '.$this->_cc_engine.'] [FETCH] '.$key.' => '.print_r($rc,true),self::$_cachefilename);
      }
      
      if($rc!==false)
      {
         static::$_keys_fetched++;
      }
      
      return $rc;
   }
   
   /**
    * Salva il valore specificato per un certo periodo di sec nella cache
    * 
    * @param String $key         Chiave da salvare
    * @param Mixed  $data        Dato da salvare in cache
    * @param Int    $ttl         Time to live della chiave, default self::DEFAULT_KEY_TTL
    * @param Int    $compressed  [OPZIONALE] indica se è compresso
    * 
    * @return type
    */
   public function store($key,$data,$ttl = self::DEFAULT_KEY_TTL,$compressed=0)
   {
      if($this->_writelog)
      {
         $this->writeLog(' [CACHE '.$this->_cc_engine.'] [STORE] '.$key.' => '.print_r($data,true),self::$_cachefilename);
      }
      
      static::$_keys_stored++;
      
      return $this->_cc->store($key,$data,$ttl,$compressed);
   }
   
   /**
    * Elimina la chiave specificata dalla cache
    * 
    * @param String $key         Chiave da elimibare
    * 
    * @return Boolean 
    */
   public function delete($key) 
   {
      if($this->_writelog){
         $this->writeLog(' [CACHE '.$this->_cc_engine.'] [DELETE] '.$key,self::$_cachefilename);
      }
      
      return $this->_cc->delete($key);
   }
   
   /**
    * Elimina / Rende invalide tutte le chiavi storate 
    * 
    * @return Boolean
    */
   public function flush()
   {
      if($this->_writelog)
      {
         $this->writeLog(' [CACHE '.$this->_cc_engine.'] [FLUSH] ',self::$_cachefilename);
      }
      
      return $this->_cc->flush();
   }
   
   
   public function flushByPrefix($prefix = CACHE_KEYPREFIX)
   {
      
   }
   
   
   /**
    * Inizializza l'oggetto manager per la cache
    * 
    * @return Boolean
    */
   private function _initCacheManagerInstance()
   {
      if($this->isActive())
      {
         $this->initCacheEngineClass();
      }
      
      return true;
   }
   
   /**
    * Restituisce il Nome della classe Invocata con il cache engine attuale
    * 
    * @param String $ccClassName [OPZIONALE] Nome cache engine da usare, parziale es: memcached
    * 
    * @return String
    */
   private function _getCacheEngineClassName($ccClassName = '')
   {
      $ccClassName = 'Cache_'.ucfirst(strlen($ccClassName)==0  ? $this->_cc_engine : $ccClassName);
      return $ccClassName;
   }
   
   /**
    * Restituisce il path completo del file contenente la classe del Cache Engine
    * 
    * @param String $ccClassName [OPZIONALE] Nome cache engine da usare, parziale es: memcached
    * 
    * @return String
    */
   private function _getCacheEngineFilePath($ccClassName = '')
   {
      $ccClassName = 'Cache_'.ucfirst(strlen($ccClassName)==0 ? $this->_cc_engine : $ccClassName);         
      $ccfilepath  = dirname(__FILE__)."/Cache/".$ccClassName.".class.php";
      return $ccfilepath;
   }
   
   
   
   public function __wakeup()
   {
      $this->__construct();
   }
}