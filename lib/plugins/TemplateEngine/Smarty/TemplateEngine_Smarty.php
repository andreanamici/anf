<?php

require_once  dirname(__FILE__)."/smarty/libs/Smarty.class.php";

/**
 * Template Engine SMARTY
 * 
 * @see http://www.smarty.net/
 */
class TemplateEngine_Smarty extends \Abstract_TemplateEngine
{
   /**
    * Wrapper Smarty template engine
    * 
    * @return boolean
    */
   public function __construct()
   {
      static::$_TEMPLATE_ENGINE_NAME = 'Smarty';
      parent::__construct();
   }

   /**
    * Return smarty template engine instance
    * 
    * @return \Smarty
    */
   public function getTemplateEngineInstance()
   {
       return parent::getTemplateEngineInstance();
   }

   public function configureTplEngine()
   {       
      /**
       * Template Engine configuration
       */
      self::$_TEMPLATE_ENGINE_CONFIGURATION = array(  
               "base_url"         => $this->getTempateBaseUrl(),
               "base_url_js"      => $this->getTempateBaseUrlJavascript(),
               "base_url_css"     => $this->getTempateBaseUrlCss(),
               "base_url_media"   => $this->getTempateBaseUrlMedia(),
               "base_url_images"  => $this->getTempateBaseUrlImages(),
               "base_url_photos"  => $this->getTempateBaseUrlPhotos(),
               "tpl_dir"          => $this->getTemplateDirectory(),
               "tpl_ext"          => $this->getTemplateFileExtension(),
               "tpl_c_dir"        => $this->getCompiledDirectory(),
               "cache_enable"     => $this->getUseCache(),
               "cache_dir"        => $this->getCacheDirectory(),
               "php_enabled"      => $this->getPHPTagsEnable(),
               "sandbox"          => $this->getSandbox(),
               "debug"            => $this->getDebug()    // set to false to improve the speed
      );     

      $smarty =  new Smarty();

      $smarty->allow_php_templates = self::$_TEMPLATE_ENGINE_CONFIGURATION["php_enabled"];
      $smarty->caching             = self::$_TEMPLATE_ENGINE_CONFIGURATION["cache_enable"];
      $smarty->cache_lifetime      = $this->getCacheExpireTime();
      $smarty->caching_type        = 'file';
      $smarty->force_compile       = false;
      $smarty->debugging           = false;

      $templateDirectory = strlen($this->getTemplateDirectory()) == 0 ? $this->getDefaultTemplateDirectory() : $this->getTemplateDirectory();

      $smarty->setTemplateDir($templateDirectory)
             ->setCompileDir($this->getCompiledDirectory()."/templates_c")
             ->setCacheDir($this->getCacheDirectory()."/cache")
             ->setPluginsDir(dirname(__FILE__)."/smarty/libs/plugins")
             ->setConfigDir(dirname(__FILE__)."/smarty/configs");

      $smarty->loadFilter('pre','translation');

      $this->setTemplateEngineInstance($smarty);

      return true;
   }

   public function drawTemplate() 
   {
      // assign a variable
      $this->getTemplateEngineInstance()->assign($this->getTemplateParams());

      $compiledSource = "";
      // draw the template
      foreach($this->getTemplateToLoad() as $templateName){
          $compiledSource.= $this->getTemplateEngineInstance()->fetch($templateName.".".self::$_TEMPLATE_ENGINE_CONFIGURATION["tpl_ext"]);
      }

      $this->setCompiledSource($compiledSource);

      return strlen($this->getCompiledSource())>0 ? true : false;
   }


   public function clearCache($type = self::CACHING_TYPE_TEMPLATES)
   {
      $ret = 0;

      switch($type)
      {
         case self::CACHING_TYPE_TEMPLATES_COMPILED:  $ret = $this->getTemplateEngineInstance()->clearCompiledTemplate(null,null,$this->getCacheExpireTime()); break;
         case self::CACHING_TYPE_TEMPLATES:           $ret = $this->getTemplateEngineInstance()->clearCache(null,null,$this->getCacheExpireTime());            break;
         case self::CACHING_TYPE_TEMPLATES_ALL:       $ret = $this->getTemplateEngineInstance()->clearAllCache($this->getCacheExpireTime());                   break;
      }

      return $ret;
   }


   public function view($getoutput = false)
   {

      if(!$this->getUseCache())
      {
         $this->setCacheExpireTime(-1);
         $this->clearCache();   
      }

      if($getoutput){
         return $this->getCompiledSource();
      }

      echo $this->getCompiledSource();
   }


   public function drawString($string,array $parameters = Array())
   {
      $this->getTemplateEngineInstance()->assign($parameters);
      return $this->getTemplateEngineInstance()->display("string:{$string}");
   }

}