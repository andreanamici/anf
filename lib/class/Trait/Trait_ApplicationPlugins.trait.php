<?php

/**
 * Trait utile per l'accesso al gestore dei plugins
 */
trait Trait_ApplicationPlugins
{
      
   /**
    * Restituisce il gestore dei plugins
    * 
    * @return Application_Plugins
    */
   public static function getApplicationPlugins()
   {
      return \ApplicationKernel::getInstance()->getApplicationPlugins();
   }
   
   /**
    * Restituisce l'instanza di un plugin
    * 
    * @param String $pluginName Nome del plugin
    * 
    * @return Mixed Instanza
    */
   public static function getPlugin($pluginName,array $options = array())
   {
       return \ApplicationKernel::getInstance()->getApplicationPlugins()->getPluginInstance($pluginName,$options);
   }
   
   /**
    * Include un plugin
    * 
    * @param String $pluginName Nome del plugin
    * 
    * @return Application_Plugins
    */
   public static function includePlugin($pluginName,array $options = array())
   {
       return \ApplicationKernel::getInstance()->getApplicationPlugins()->includePlugin($pluginName,$options);
   }
}