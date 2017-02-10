<?php

require_once dirname(__FILE__).'/lib/Twig/Autoloader.php';

/**
 * TemplateEngine Twig
 * @see http://twig.sensiolabs.org/
 */
class TemplateEngine_Twig extends \Abstract_TemplateEngine
{   
   /**
    * Inizializza l'autoload del template engine
    * 
    * @return Boolean
    */
   public function __construct()
   {
      static::$_TEMPLATE_ENGINE_NAME = 'Twig';
      Twig_Autoloader::register(true);
      parent::__construct();
   }

   /**
    * Return Twig template engine instance
    * 
    * @return Twig_Environment
    */
   public function getTemplateEngineInstance()
   {
       return parent::getTemplateEngineInstance();
   }

   /**
    * Configura il template Engine
    * @return \TemplateEngine_Twig
    */
   public function configureTplEngine()
   {  
      $loader        = new Twig_Loader_Filesystem();
      $loader->addPath($this->getDefaultTemplateDirectory());

      $twig  = new Twig_Environment($loader, array(
               'charset'             => $this->getCharset(),
               'base_template_class' => 'Twig_Template',
               'strict_variables'    => false,
               'autoescape'          => 'html',
               'auto_reload'         => null,
               'optimizations'       => -1,
               "base_url"            => $this->getTempateBaseUrl(),
               "base_url_js"         => $this->getTempateBaseUrlJavascript(),
               "base_url_css"        => $this->getTempateBaseUrlCss(),
               "base_url_media"      => $this->getTempateBaseUrlMedia(),
               "base_url_images"     => $this->getTempateBaseUrlImages(),
               "base_url_photos"     => $this->getTempateBaseUrlPhotos(),
               "tpl_dir"             => $this->getTemplateDirectory(),
               "tpl_ext"             => $this->getTemplateFileExtension(),
               "tpl_c_dir"           => $this->getCompiledDirectory(),
               "cache_enable"        => $this->getUseCache(),
               "cache"               => $this->getCacheDirectory(),
               "php_enabled"         => $this->getPHPTagsEnable(),
               "sandbox"             => $this->getSandbox(),
               "debug"               => $this->getDebug()
      ));

      $this->setTemplateEngineInstance($twig)
           ->addExtensions();

      return $this;
   }

   public function drawTemplate() 
   {
      if(is_array($this->getTemplateToLoad()) && count($this->getTemplateToLoad()) > 1)
      {
         return self::throwNewException(9234827342392395002,'Questo template engine prevede che venga specificato esclusivamente un template da renderizzare');
      }       
      else if(is_array($this->getTemplateToLoad()) && count($this->getTemplateToLoad()) == 1)
      {
         $templateName = $this->getTemplateToLoad()[0];

         $loader       = $this->getTemplateEngineInstance()->getLoader();
         $loader->addPath($this->getTemplateDirectory());

         $templateName   = $templateName.".".$this->getTemplateFileExtension();
         $compiledSource = $this->getTemplateEngineInstance()->render($templateName,$this->getTemplateParams());

         $this->setCompiledSource($compiledSource);

         return strlen($compiledSource)>0 ? true : false;
      }

      return false;
   }


   public function clearCache($force = false)
   {
      if($force)
      {
         $this->getTemplateEngineInstance()->clearCacheFiles($this->getCacheExpireTime());
         $this->getTemplateEngineInstance()->clearTemplateCache($this->getCacheExpireTime());
      }
   }


   public function view($getoutput = false)
   {
      if(!$this->getUseCache())
      {
         $this->setCacheExpireTime(-1);
         $this->clearCache();   
      }

      if($getoutput)
      {
         return $this->getCompiledSource();
      }

      echo $this->getCompiledSource();
   }

   public function drawString($string,array $parameters = array())
   {           
       $oldLoader = $this->getTemplateEngineInstance()->getLoader();
       $this->getTemplateEngineInstance()->setLoader(new \Twig_Loader_String());

       $renderedString =  $this->getTemplateEngineInstance()->render($string, $parameters);
       $this->getTemplateEngineInstance()->setLoader($oldLoader);

       return $renderedString;
   }

   /**
    * Aggiunge al template engine le function e i filters necessari
    * @return TemplateEngine_Twig
    */
   private function addExtensions()
   {
      $twig = $this->getTemplateEngineInstance(); /*@var $twig Twig_Environment*/

      $filters     = Array();
      $functions   = Array();

      //***************************

      $filters[]   = new Twig_SimpleFilter('trans', function (Twig_Environment $env, $context, $string) {
               return translate($string);
      }, array('needs_context' => true, 'needs_environment' => true));

      $functions[] = new Twig_SimpleFunction('path', function ($where,array $params = array()) {
              return path($where, $params);
      });

      $functions[] = new Twig_SimpleFunction('url', function ($where,array $params = array()) {
              return url($where, $params);
      });


      //***************************

      foreach($filters as $filter){     $twig->addFilter($filter);     };
      foreach($functions as $function){ $twig->addFunction($function); };


      return $this->setTemplateEngineInstance($twig);
   }
}