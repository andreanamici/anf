<?php

/**
 * Classe wrapper del templateEngine RainTPL
 */
class TemplateEngine_RainTpl extends \Abstract_TemplateEngine
{
   /**
    * Classe wrapper del templateEngine RainTPL
    */
   public function __construct()
   {
      static::$_TEMPLATE_ENGINE_NAME = 'RainTpl';

      /**
       * Prepend di questo autoload e in più controllo eventualmente
       * se l'autoload function indicata puà essere settata correttamente
       */
      spl_autoload_register(function($class){

             if (strpos($class,'Rain\\Tpl') !== false)
             {
                 $path     = str_replace("\\", DIRECTORY_SEPARATOR, $class );
                 $abs_path = str_replace("\\", DIRECTORY_SEPARATOR, dirname(__FILE__) . "\\" . $path . ".php");

                 if(file_exists($abs_path)) {
                    require_once $abs_path;  
                 }
             }

      },true,true);
      
      parent::__construct();
   }

   /**
    * Restituisce l'instanza di Rain
    * @return Rain\Tpl
    */
   public function getTemplateEngineInstance()
   {
       return parent::getTemplateEngineInstance();
   }

   public function configureTplEngine()
   {  
       Rain\Tpl::configure(array(
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
               "debug"            => $this->getDebug()
      ));

      Rain\Tpl::registerPlugin(new Rain\Tpl\Plugin\TemplateExtention());
      Rain\Tpl::registerPlugin(new Rain\Tpl\Plugin\Translation());
      Rain\Tpl::registerPlugin(new Rain\Tpl\Plugin\VariableModifier());      
      Rain\Tpl::registerPlugin(new Rain\Tpl\Plugin\UrlGenerator());
      Rain\Tpl::registerPlugin(new Rain\Tpl\Plugin\PathReplace());
      Rain\Tpl::registerPlugin(new Rain\Tpl\Plugin\HTMLSelect());
      Rain\Tpl::registerPlugin(new Rain\Tpl\Plugin\FlashData());
      Rain\Tpl::registerPlugin(new Rain\Tpl\Plugin\Functions());

      $this->setTemplateEngineInstance(new Rain\Tpl());

      return true;
   }

   public function drawTemplate() 
   {
      $this->getTemplateEngineInstance()->assign($this->getTemplateParams());

      $compiledSource = "";
      
      foreach($this->getTemplateToLoad() as $templateName)
      {
          $compiledSource.= $this->getTemplateEngineInstance()->draw($templateName,true);
      }

      $this->setCompiledSource($compiledSource);

      return strlen($compiledSource)>0 ? true : false;
   }

   public function drawString($string,array $parameters = array())
   {           
       $this->getTemplateEngineInstance()->assign($parameters);
       return $this->getTemplateEngineInstance()->drawString($string,true);
   }

   public function clearCache()
   {
      return $this->getTemplateEngineInstance()->clean($this->getCacheExpireTime());
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
}