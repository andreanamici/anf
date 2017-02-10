<?php

/**
 * Questa classe si occupa di gestire i plugin dell'applicazione.
 * 
 * Ogni plugin può creare nuove funzioni globali, restituire un instanza di un oggetto, l'importante è che il file che verra incluso automaticamente faccia questo. 
 */
class Application_Plugins implements Interface_ApplicationPlugins
{
   use Trait_ApplicationKernel,
                  
       Trait_Exception,Trait_Singleton;
              
      
   /**
    * Path plugins
    * 
    * @var ArrayIterator
    */
   protected $_PLUGINS_PATHS       = null;
   
   
   public function __construct() 
   {
      $this->_PLUGINS_PATHS    = new ArrayIterator();
      $pluginsDirectory        = $this->getApplicationKernel()->getApplicationConfigs()->getConfigsValue("APPLICATION_PLUGINS_PATHS");
      
      if(is_array($pluginsDirectory) && count($pluginsDirectory) > 0)
      {
         foreach($pluginsDirectory as $path)
         {
            $this->_PLUGINS_PATHS->append($path);
         }
      }
      else
      {
        $this->_PLUGINS_PATHS->append($pluginsDirectory);
      }
   }
   
   /**
    * Restituisce il file principale che dovrà essere incluso dal gestore del plugin
    * 
    * @param String $plugin Nome o path relativo al plugin, a partire dalla cartella dei plugins inclusa automaticamente
    * 
    * @throws Exception_PortalErrorException
    * 
    * @return String
    */
   public function getPluginPath($plugin)
   {
      foreach($this->_PLUGINS_PATHS as $pluginDirectory)
      {
         $pluginPath = $pluginDirectory.'/'.$plugin.'/'.self::PLUGINS_MAIN_FILE;
         
         if(file_exists($pluginPath))
         {
            return $pluginPath;
         }         
      }
      
      return self::throwNewException(39938827662554144267, 'Non è possibile trovare il plugin '.$plugin.' in nessun path tra questi: '.print_r($this->_PLUGINS_PATHS->getArrayCopy(),true));
   }
   
      
   /**
    * Include il file principale del plugin, utile qualora il plugin non debba restiuire instanze di oggetti ma carica, modifica o installa delle function, oggetti etc..
    * 
    * @param String $plugin  Nome o path relativo al plugin, a partire dalla cartella dei plugins inclusa automaticamente
    * @param Array  $options Opzioni passate al plugin, default Array
    * 
    * @return Application_Plugins
    */
   public function includePlugin($plugin,array $options = array())
   {
      $pluginPath = $this->getPluginPath($plugin);
      
      require_once $pluginPath;
      
      return $this;
   }
   
   /**
    * Restituisce l'instanza dell'oggetto restituito dal plugin
    * 
    * @param String $plugin  Nome o path relativo al plugin, a partire dalla cartella dei plugins inclusa automaticamente
    * @param Array  $options Opzioni passate al plugin, default Array
    * 
    * @throws Exception_PortalErrorException
    * 
    * @return Mixed Instanza del plugin
    */
   public function getPluginInstance($plugin,array $options = array())
   {
      $pluginPath       = $this->getPluginPath($plugin);
      $pluginInstance   = require $pluginPath;
      
      if(is_object($pluginInstance))
      {
         return $pluginInstance;
      }
      
      return self::throwNewException(9339828290409828477, 'Non è possibile restiture un instanza di un oggetto valida per questo plugin: '.$plugin.' poichè nel file '.$pluginPath.' non esiste nessun return di un oggetto relativo al plugin.');
   }
   
   /**
    * Verifica che il un plugin esista cercando il path fisico
    * 
    * @param String $plugin Plugin
    * 
    * @return boolean
    */
   public function hasPlugin($plugin)
   {
       try
       {
          $pluginPath       = $this->getPluginPath($plugin);
       }
       catch(\Exception $e)
       {
           return false;
       }
       
       return $pluginPath ? true : false;
   }
       
   
   
   /**
    * Registra i plugins presenti in questo package, aggiungendo la directory dei plugin tra la lista di quelli disponibili
    * 
    * @param Abstract_Package $package package
    * 
    * @return Application_Plugins
    */
   public function registerPluginsForPackage(Abstract_Package $package)
   {
      $pluginsPath = $package->getPluginsPath();
      
      if(!file_exists($pluginsPath))
      {
         return false;
      }
      
      $this->_PLUGINS_PATHS->append($pluginsPath);
      
      return $this;
   }
}
