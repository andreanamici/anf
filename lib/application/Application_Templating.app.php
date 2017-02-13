<?php

/**
 * L'application Templating si occupa di elaborare i template, sfruttando 
 * i template engine caricati
 */
class Application_Templating implements Interface_ApplicationTemplating
{
    
    use Trait_ObjectUtilities,
            
        Trait_Application,
    
        Trait_ApplicationHooks;
                
    /**
     * Template engine di default php, vuoto senza nome.. cosi non ci si sbaglia :)
     * @var String 
     */
    const    DEFAULT_TEMPLATE_ENGINE_PHP          = '';        //Vuoto, nessun tpl engine
    
    /**
     * Formato dei file template php di default
     * @var String 
     */
    const    DEFAULT_TEMPLATE_FILENAME_FORMAT     = '%s.tpl.php';
    
    /**
     * Estenzione default file template
     * @var String 
     */
    const    DEFAULT_TEMPLATE_FILE_EXTENSION      = 'tpl';
    
      
    /**
     * Charset del portale. Utilizzare lo stesso per db e html
     * @var String 
     */
    protected static  $_site_charset               = APPLICATION_TEMPLATING_DEFAULT_CHARSET;      
          
    /**
     * Nome della directory che contiene i sotto template dalla root del sito
     * @var String 
     */
    protected  $_package_directory                  = APPLICATION_TEMPLATING_PACKAGE_DIRECTORY_NAME;
    
    /**
     * Nome del template engine utilizzato
     * @var String 
     */
    protected  $_template_engine_service          = APPLICATION_TEMPLATING_TPL_ENGINE_SERVICE;
      
    /**
     * Indica se utilizzare cache per i template compilati <br>
     * (sempre se il template engine specificato è diverso da php!)
     * @var Boolean 
     */
    protected  $_tpl_cache_enable                  = APPLICATION_TEMPLATING_TPL_CACHE_ENABLE;
    
    /**
     * Durata dei file cache dei template in secondi
     * @var Int
     */
    protected  $_tpl_cache_time_expire            = APPLICATION_TEMPLATING_TPL_CACHE_TIME_EXPIRE;
    
    
    /**
     * Nome della cartella relativa dalla root del portale in cui sono storati i template precompilati o cachati prodotti dal template engine
     * @var String 
     */
    protected  $_tpl_cache_dir                    = APPLICATION_TEMPLATING_TPL_CACHE_DIR;
    
    /**
     * Default Template root del sito
     * @var String 
     */
    protected  $_package_default            = APPLICATION_PACKAGE_DEFAULT;
    
    /**
     * Path assoluto in cui ricercare le viste da elaborare
     * 
     * @var string 
     */
    protected $_application_views_path  = '';
        
    /**
     * Path assoluto in cui ricercare  le risorse statiche
     * 
     * @var string 
     */
    protected $_application_resources_path  = '';
    
    protected $_root_path = ROOT_PATH;
    
    /**
     * Directory in cui sono contenuti i tpl
     * @var String 
     */
    protected  $_package                    = APPLICATION_PACKAGE_DEFAULT;

    
    /**
     * Estenzione dei Template, default <APPLICATION_TEMPLATING_TPL_FILE_EXTENSION>
     * @var String
     */
    protected  $_tpl_file_extension              = APPLICATION_TEMPLATING_TPL_FILE_EXTENSION;
    
    
    /**
     * Nome della cartella in cui si trovano i file template del portale
     * @var String
     */
    protected  $_tpl_path                       = APPLICATION_TEMPLATING_TPL_PATH;   
    
    /**
     * Nome della cartella in cui sono i file statici e le cartelle js,css,flash,video etc..
     * @var String 
     */
    protected  $_html_directory                 = "resources".DIRECTORY_SEPARATOR."html"; 
    
    /**
     * Contiene la root relativa HTTP del sito , default ""
     * @var String
     */
    protected  $_http_root                      = null; 
    
    /**
     * Contiene l'url HTTP del sito , es:http://www.sito.com
     * @var String
     */
    protected  $_http_site                     = HTTP_SITE; 
  
    
    /**
     * Array che contiene la lista dei template che costruiranno la pagina (soltanto nome del tpl, senza path assoluti etc es: 'header')
     * @var Array
     */
    private  $_tpl_build_arr         = Array();
    
    /**
     * Array che contiene la lista dei template con i path assoluti, cosi da essere elaborati ed inclusi
     * @var Array
     */
    private  $_tpl_to_load           = Array();
    
    /**
     * Eventuale sottopath della directory principale dei template
     * @var String
     */
    private  $_tpl_subfolder         = "";                           
                         
    
    /**
     * Array di Parametri ottenuti dagli Action_Object controllers e che verranno passati al template 
     * 
     * @var Array
     */
    public   $_params                = null;
    
        
    /**
     * Imposta l'HTTP ROOT gestito
     * 
     * @param String $httpRoot  Valore httpRoot
     * 
     * @return \Application_Templating
     */
    public  function setHttpRoot($httpRoot)
    {
        $this->_http_root = $httpRoot;
        return $this;
    }
    
    
    /**
     * Imposta HTTP SITE gestito
     * 
     * @param String $httpSite  Valore httpSite
     * 
     * @return \Application_Templating
     */
    public  function setHttpSite($httpSite)
    {
        $this->_http_site = $httpSite;
        return $this;
    }
    
    
    /**
     * Imposta la directory che contiene il modulo del package
     * 
     * @param String  $mode  Nome del package, NULL per nessun package (ricerca template nella directory principale)
     * @param Boolean $check Indica se controllare l'esistenza
     * 
     * @return \\Application_Templating
     * 
     * @throws Exception Se il package specificato non è valido
     */
    public function setPackage($package,$check = true)
    {
       if($package && $check && !$this->getApplicationKernel()->isValidPackage($package))
       {
          return self::throwNewException(23842893420020349, 'package fornito invalido: '.$package);
       }

       $this->_package = $package;
       return $this;
    }
    
    
    public function setTemplatePath($tplPath)
    {
        $this->_tpl_path = $tplPath;
        return $this;
    }
        
    
    public function getTemplatePath()
    {
        return $this->_tpl_path;
    }
    
    /**
     * Imposta una directory per i packages dell'applicazione
     * 
     * @param String $dir Directory Name
     * 
     * @return \Application_Templating
     */
    public function setPackageDirectory($dir)
    {
       $this->_package_directory = $dir;
       return $this;
    }
    
    
    /**
     * Imposta una directory per i template all'interno di quella di default
     * 
     * @param String $tplDir Directory Name
     * 
     * @return \Application_Templating
     */
    public function setTemplateSubFolder($tplDir)
    {
       if(strlen($tplDir) > 0 )
       {
          $this->_tpl_subfolder = $tplDir.DIRECTORY_SEPARATOR;
       }
       
       return $this;
    }
    
    
    /**
     * Imposta il formato estenzione dei file template
     * 
     * @param String $ext Estenzione
     * 
     * @return \Application_Templating
     */
    public function setTemplateFileExtension($ext)
    {
       $this->_tpl_file_extension = $ext;
       return $this;
    }
    
    /**
     * Imposta se utilizzare caching per il template elaborato
     * 
     * @param Boolean $enable
     * 
     * @return \Application_Templating
     */
    public function setTemplateCacheEnable($enable)
    {
       $this->_tpl_cache_enable = $enable;
       return $this;
    }
    
    
    /**
     * Aggiunge un elemento ai parametri utilizzati per il render dei template
     * 
     * @param String $key    Array key
     * @param Mixed  $value  Valore
     * 
     * @return \Application_Templating
     */
    public function addParams($key,$value)
    {
        $this->_params[$key] = $value;
        return $this;
    }

    /**
     * Merge dei parametri passati con quelli attualmente presenti per il render dei template
     * 
     * @param Array $array Array da unire $this->_params
     * 
     * @return \Application_Templating
     */
    public function addParamsArray(array $array)
    {
        $this->_params = array_merge($this->_params,$array);
        return $this;
    }
    
    
    /**
     * Imposta il nome del servizio del Template Engine, se NULL verrà usato PHP
     * 
     * @param String $service Nome del servizio
     * 
     * @return \Application_Templating
     */
    public function setTemplateEngine($service)
    {
       if(strlen($service) == 0)
       {
          $this->_template_engine_service = self::DEFAULT_TEMPLATE_ENGINE_PHP;
       }
       else
       {
          $this->_template_engine_service = $service;
          $this->initTemplateEngine();
       }
       
       return $this;
    }
    
    
    /**
     * Restituisce il Nome della user-directory degli actionObject e delle resources
     * @return String
     */
    public  function getUserDirectoryName()
    {
        return $this->_package_directory;
    }
    
    
    /**
     * Restituisce l'HTTP ROOT gestito
     * @return String
     */
    public  function getHttpRoot()
    {
        return $this->_http_root;
    }
    
    
    /**
     * Restituisce HTTP SITE gestito
     * @return String
     */
    public  function getHttpSite()
    {
        return $this->_http_site;
    }
    
    /**
     * Restituisce l'attuale charset usato dalle viste
     * 
     * @return string
     */
    public function getCharset()
    {
        return self::$_site_charset;
    }
    
    /**
     * Restituisce il nome del package attualmente in uso
     * 
     * @return String
     */
    public  function getPackage()
    {
        return $this->_package;
    }
    
    
    /**
     * Restituisce il nome del package di default
     * 
     * @return String
     */
    public function getPackageDefault()
    {
       return $this->_package_default;
    }
   
   
    /**
     * Restituisce il Nomde del Template Engine utilizzato, default  "" => PHP
     * @return String
     */
    public  function getTemplateEngine()
    {
       return  $this->_template_engine_service;
    }
        
    
    
    /**
     * Restituisce l'estenzione dei file template usati dal Template Engine corrente, default <APPLICATION_TEMPLATING_TPL_FILE_EXTENSION>
     * @return String
     */
    public  function getTemplateFileExtension()
    {
       return $this->_tpl_file_extension;
    }
    
    /**
     * Restituisce la subfolder in cui cercare le viste indicate
     * @return String
     */
    public function getTemplateSubFolder()
    {
        return $this->_tpl_subfolder;
    }
    
    /**
     * Restituisce il parametro di indice $key utilizzato per renderizzare un template
     * 
     * @param String $key Chiave del vettore da cercare
     * 
     * @return Mixed or FALSE
     */
    public function getParams($key)
    {
        if(isset($this->_params[$key]))
        {
           return $this->_params[$key];
        }
        
        return false;
    }
        
    /**
     * Controller Template - si occupa di processare e visualizzare l'output di elaborazione
     * 
     * @param String $action        Azione da renderizzare    
     * @param String $actionType    Tipologia di Action
     * 
     * @return boolean 
     */
    public function  __construct()
    {
        $this->_params                     = array();
        $this->_application_views_path     = APPLICATION_RESOURCES_TEMPLATE_DIRECTORY_PATH;
        $this->_application_resources_path = APPLICATION_RESOURCES_ASSETS_DIRECTORY_PATH;
                
        $this->_http_root                  = $this->getService('httprequest')->getPath();
    }

    /**
     * Inizializza il template Engine
     * 
     * @return \\Application_Templating
     */
    public function initTemplateEngine()
    {
         if($this->hasService($this->_template_engine_service))
         {
            $templateEngineInstance  = $this->getService($this->_template_engine_service);

            //Configuration Template Engine         
            $cnf_tpl_engine_name   = $templateEngineInstance->getTemplateEngineName();
          
            $cnf_tpl_params        = $this->_params;
            $cnf_tpl_extension     = $this->_tpl_file_extension;
            
            /**
             * Costruisco i path in base al package attualmente utilizzato dalla rotta
             */
            if($this->_package)
            {
                $cnf_tpl_dir           = $this->_root_path.DIRECTORY_SEPARATOR.$this->_package_directory.DIRECTORY_SEPARATOR.$this->_package.DIRECTORY_SEPARATOR.$this->_tpl_path.DIRECTORY_SEPARATOR.$this->_tpl_subfolder;
                $cnf_tpl_dir_compiled  = $this->_root_path.DIRECTORY_SEPARATOR.$this->_tpl_cache_dir.DIRECTORY_SEPARATOR.$cnf_tpl_engine_name.DIRECTORY_SEPARATOR."php".DIRECTORY_SEPARATOR.$this->_package.DIRECTORY_SEPARATOR;  //Cartella template da elaborare
            }
            else
            {
                $cnf_tpl_dir           = $this->_application_views_path.DIRECTORY_SEPARATOR.$this->_tpl_subfolder;
                $cnf_tpl_dir_compiled  = $this->_root_path.DIRECTORY_SEPARATOR.$this->_tpl_cache_dir.DIRECTORY_SEPARATOR.$cnf_tpl_engine_name.DIRECTORY_SEPARATOR."php".DIRECTORY_SEPARATOR;  //Cartella template da elaborare
            }
            
            /**
             * Preparo la lista di template da caricare, di default il view controller mi passa tutti i path assoluti dei file
             */
            $cnf_tpl_list = Array();
            
            foreach($this->_tpl_to_load as $template)
            {
               $template       = str_replace('.'.$cnf_tpl_extension,'',$template);
               $cnf_tpl_list[] = str_replace($cnf_tpl_dir,'',$template);
            }
            
            //Caching Template File
            $cnf_use_cache         = $this->_tpl_cache_enable;
            $cnf_cache_time        = $this->_tpl_cache_time_expire;
            $cnf_cache_dir         = $this->_root_path.DIRECTORY_SEPARATOR.$this->_tpl_cache_dir.DIRECTORY_SEPARATOR.$cnf_tpl_engine_name.DIRECTORY_SEPARATOR."html".DIRECTORY_SEPARATOR.$this->_package.DIRECTORY_SEPARATOR;  //Cartella template elaborati

            $cnf_base_url          = $this->_http_root;
            $cnf_base_url_js       = $this->_http_root  . $this->getConfigValue('APPLICATION_RESOURCES_ASSETS_RELATIVE_URL'). DIRECTORY_SEPARATOR;
            $cnf_base_url_css      = $this->_http_root  . $this->getConfigValue('APPLICATION_RESOURCES_ASSETS_RELATIVE_URL'). DIRECTORY_SEPARATOR;
            $cnf_base_url_media    = $this->_http_root  . $this->getConfigValue('APPLICATION_RESOURCES_ASSETS_RELATIVE_URL'). DIRECTORY_SEPARATOR;
            $cnf_base_url_images   = $this->_http_root  . $this->getConfigValue('APPLICATION_RESOURCES_ASSETS_RELATIVE_URL'). DIRECTORY_SEPARATOR;
            $cnf_base_url_photos   = $this->_http_root  . $this->getConfigValue('APPLICATION_RESOURCES_ASSETS_RELATIVE_URL'). DIRECTORY_SEPARATOR;
                        
            if($this->_package != $this->_package_default)
            {
               $cnf_base_url          .= $this->_package .DIRECTORY_SEPARATOR;
               $cnf_base_url_js       .= $this->_package .DIRECTORY_SEPARATOR;
               $cnf_base_url_css      .= $this->_package .DIRECTORY_SEPARATOR;
               $cnf_base_url_media    .= $this->_package .DIRECTORY_SEPARATOR;
               $cnf_base_url_images   .= $this->_package .DIRECTORY_SEPARATOR;
               $cnf_base_url_photos   .= $this->_package . DIRECTORY_SEPARATOR;
            }
            
            /**
             * Configurazione Template Engine
             */
            $templateEngineInstance->setTemplateParams($cnf_tpl_params)
                                      ->setTemplateDirectory($cnf_tpl_dir)
                                      ->setTemplateToLoad($cnf_tpl_list)
                                      ->setTemplateFileExtension($cnf_tpl_extension)
                                      ->setCompiledDirectory($cnf_tpl_dir_compiled)

                                      //Environment Info
                                      ->setCharset(self::$_site_charset)
                                      ->setDebug($this->getKernelDebugActive())
                                      ->setSandbox(false)
                                      ->setPHPTagsEnable(true)

                                      //Caching Info
                                      ->setUseCache($cnf_use_cache)
                                      ->setCacheDirectory($cnf_cache_dir)
                                      ->setCacheExpireTime($cnf_cache_time)

                                      //css,js,media info
                                      ->setTemplateBaseUrl($cnf_base_url)
                                      ->setTemplateBaseUrlJavascript($cnf_base_url_js)
                                      ->setTemplateBaseUrlCss($cnf_base_url_css)
                                      ->setTemplateBaseUrlMedia($cnf_base_url_media)
                                      ->setTemplateBaseUrlImages($cnf_base_url_images)
                                      ->setTemplateBaseUrlPhotos($cnf_base_url_photos);

            ////////////////////////////////////////////////////////////////////////////////////////
            ////////////////////////////////////////////////////////////////////////////////////////


            //Preparo e compilo il template
            $templateEngineInstance->configureTplEngine();
            $templateEngineInstance->clearCache();
         }
         
         return $this;
    }
    
    
    /**
     * Aggiunge un template da caricare
     * 
     * @param String $tpl nome del template es "header_ext","footer_int","sidebar"
     * 
     * @return \Application_Templating
     */
    public function addTemplate($tpl)
    {
        $newIndex = count($this->_tpl_build_arr)+1;
        $this->_tpl_build_arr[$newIndex] = $tpl;
        return $this;
    }
    
    
    /**
     * Pulisce la lista del template da processare
     * 
     * @return \Application_Templating
     */
    public function clearTemplateList()
    {
        $this->_tpl_build_arr  = array();
        $this->_tpl_to_load    = array();
        
        return $this;
    }
    
    
    /**
     * Aggiunge uno o più template da caricare 
     * 
     * @param Array $tplArr  Array di template da caricare
     * 
     * @return \Application_Templating
     * 
     */
    public function addTemplateArr($tplArr)
    {
        if(is_array($tplArr) && count($tplArr)>0)
        {
            foreach($tplArr as $tpl)
            {
               $newIndex = count($this->_tpl_build_arr)+1;
               $this->_tpl_build_arr[$newIndex] = $tpl;
            }
            
            return $this;
        }
        
        return $this;
    }
    
    /**
     * Restituisce il template HTML elaborato
     * 
     * @return String
     */
    public function view()
    {        
        $tpl_arr = Array();
             
        if(count($this->_tpl_build_arr) > 0)
        {
           foreach($this->_tpl_build_arr as $template)
           {   
              $this->_tpl_to_load[] = $this->buildTplPath($template); //Template 
           }
        }
        
        $hookData = array(
            'templates' => $this->_tpl_to_load,
            'params'    => $this->_params
        );
                
        //[Application_Hooks::HOOK_TYPE_PRE_TEMPLATE] +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        $hookData = $this->processHooks(self::HOOK_TYPE_PRE_TEMPLATE,$hookData)->getData();
        //[Application_Hooks::HOOK_TYPE_PRE_TEMPLATE] +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
             
        if(count($this->_tpl_build_arr)==0 && !empty($hookData['templates']))
        {
            if(is_string($hookData['templates']))
            {
                $hookData['templates']  = array($hookData['templates']);
            }
            
            foreach($hookData['templates'] as $key => $template)
            {   
               $hookData['templates'][$key] =  $this->buildTplPath($template); //Template 
            }            
        }
        
        $this->_tpl_to_load  = $hookData['templates'];
        $this->_params       = $hookData['params']; 
        
        if(count($this->_tpl_to_load) == 0)
        {
            return self::throwNewException(92938849048859,"Nessun template da renderizzare");
        }

        $templateOutput     =  $this->_render();

        //[Application_Hooks::HOOK_TYPE_POST_TEMPLATE] ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        $templateOutput     = $this->processHooks(self::HOOK_TYPE_POST_TEMPLATE,$templateOutput)->getData();
        //[Application_Hooks::HOOK_TYPE_POST_TEMPLATE] ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
                
        return $templateOutput;
    }
    
    
    /**
     * [METODO SHORTCUT]
     * 
     * Effettua il render del template indicato sfruttando i setting di default del controller o quelli impostati precedentemente a questa chiamata.
     * 
     * @param Mixed  $templates            Nome del template, o lista di template senza estenzione
     * @param Array  $params               [OPZIONALE] Parametri da passare per il rendering
     * @param String $package              package attuale, se NULL non utilizza nessun package e ricerca nella directory delle resources di default, indicare con '' per non cambiare package
     * @param String $templateExtension    [OPZIONALE] Estenzione dei file template
     * 
     * @return String
     */
    public function renderView($templates,array $params = array(),$package = '',$templateExtension = null)
    {       
       $hookData = array('params' => $params);
       
       //[Application_Hooks::HOOK_TYPE_PRE_TEMPLATE] +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
       $hookData = $this->processHooks(self::HOOK_TYPE_PRE_TEMPLATE,$hookData)->getData();
       //[Application_Hooks::HOOK_TYPE_PRE_TEMPLATE] +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
              
       $params    = $hookData['params'];

       $templates = is_string($templates) && strlen($templates) > 0 ? array($templates) : $templates;
       $package   = $package ? $package : (is_null($package) ? $package : $this->getPackage());
       
       $currentPackage          = $this->_package;
       $currentTplFileExtension = $this->_tpl_file_extension;
       
       $this->clearTemplateList()
            ->addTemplateArr($templates)
            ->setPackage($package)
            ->addParamsArray($params);
       
       if(strlen($templateExtension) > 0)
       {
          $this->setTemplateFileExtension($templateExtension);
       }
       else
       {
          $this->setTemplateFileExtension('php');
       }

       $return  = $this->view();
       
       $this->setPackage($currentPackage)
            ->setTemplateFileExtension($currentTplFileExtension);
       
       return $return;
    }
    
    /**
     * Restituisce una response valida per il kernel, effettuando il render del template richiesto
     * 
     * @param Mixed  $templates            Nome del template, o lista di template senza estenzione
     * @param Array  $params               [OPZIONALE] Parametri da passare per il rendering
     * @param Array  $headers              [OPZIONALE] Headers da rilasciare, default array()
     * @param String $package              [OPZIONALE] package attuale, default NULL
     * @param String $templateExtension    [OPZIONALE] Estenzione dei file template, default NULL
     * 
     * @return String
     */
    public function response($view,array $params = array(),array $headers = array(),$package = null,$templateExtension = null)
    {
        return \AppController::response($this->renderView($view,$params,$package,$templateExtension),$headers);
    }
    
    /**
     * Pulisce la cache dei template elaborati php
     * 
     * @return Boolean
     */
    public function flushCache()
    {
        $tplCachePath = $this->_root_path . DIRECTORY_SEPARATOR . $this->_tpl_cache_dir;
        
        if(!file_exists($tplCachePath))
        {
            return true;
        }
      
        if(file_exists($tplCachePath) && $this->getUtility()->rrmdir($tplCachePath)!==false)
        {
            return mkdir($tplCachePath,0777,true);
        }
        
        return self::throwNewException(2983462843490348,'Impossibile svuotare la cache dei template, path: '.$tplCachePath);
    }
    
    
    /**
     * Elabora i Templates files da Caricare
     * 
     * @return Boolean
     */
    private function _render()
    {       
       if(count($this->_tpl_to_load ) == 0)
       {
          return self::throwNewException(92394923483465757,"Nessun Template da Caricare!");
       }
       
       //Controllo esistenza Template!
       foreach($this->_tpl_to_load as $template)
       {
          if(!file_exists($template))
          {
             if($this->getKernelDebugActive())
             {
                return self::throwNewException(210392084207204,"Template Invalido: {$template}");
             }
             else
             {
                return self::throwNewExceptionPageNotFound();
             }
          }
       }

       switch($this->_template_engine_service)     
       {
          case self::DEFAULT_TEMPLATE_ENGINE_PHP:   $templateOutput = $this->_renderPHP();              break;
          default:                                  $templateOutput = $this->_renderTemplateEngine();   break;
       }
       
       $this->clearTemplateList();
       
       return $templateOutput;     
    }
    
    /**
     * Render Template senza un template Engine associato
     * 
     * @return String
     */
    private function _renderPHP()
    {       
       extract($this->_params);
              
       ob_start();
       
       foreach($this->_tpl_to_load as $template){
          require_once $template;
       }
       
       $output = ob_get_clean();
       
       return $output;
    }
    
    /**
     * Render del Template tramite il Template Engine specificato al controller o nei file di configurazione
     * 
     * 
     * @return Boolean|String
     */
    private function _renderTemplateEngine()
    {
         $this->initTemplateEngine();
         
         /**
          * Template Engine da Utilizzare configurato nel file di configurazione dei service
          */         
         $templateEngineInstance = $this->getService($this->_template_engine_service);/*@var $templateEngineInstance Abstract_TemplateEngine*/
             
         $templateOutput = "";
                  
         if($templateEngineInstance->drawTemplate()!==false)
         {
            $templateOutput = $templateEngineInstance->view(true);
         }
         
         return  $templateOutput;
    }
    
    /**
     * Controlla che il template esista (in base ai parametri attualmente utilizzati dal controller)
     * 
     * @param String $tplName nome del template
     * 
     * @return Boolean
     */
    public function isTemplateExists($tplName)
    {
        return file_exists($this->buildTplPath($tplName,true));
    }
    
    /**
     * Restituisce l'url relativo / assoluto di una risorsa statica di un package o di default dell'applicazione
     * 
     * @param String  $resource     Path relativo della risorsa dalla cartella delle risorse
     * @param String  $package      Nome del package, default null (quello usato dal templating)
     * @param Boolean $absolute     Indica se l'url è assoluto, default TRUE
     * 
     * @return String
     */
    public function getResourceUrl($resource, $package = null, $absolute = true)
    {
        $kernel      =  $this->getApplicationKernel();
        $httpRequest =  $kernel->getApplicationHttpRequest();
        
        $packageName = $package ? $package : $this->_package;
        $package     = null;

        if($kernel->isValidPackage($packageName))
        {
            $package = $kernel->getPackageInstance($packageName);
        }
        
        if($package)
        {
            return $package->getResourceUrl($absolute) . DIRECTORY_SEPARATOR . $resource;
        }
        
        return ($absolute ? $httpRequest->getBaseUrl() : $httpRequest->getPath()). 'app'.DIRECTORY_SEPARATOR .APPLICATION_TEMPLATING_ASSETS_PATH. DIRECTORY_SEPARATOR .$resource;
    }
    
    /**
     * Restituisce il path assoluto della risorsa
     * 
     * @param String $resource path relativo della risorsa
     * 
     * @return String
     */
    public function getResourcePath($resource, $package = null)
    {
        $kernel      = $this->getApplicationKernel();
        $packageName = $package ? $package : $this->_package;
        $package     = null;
        
        if($kernel->isValidPackage($packageName))
        {
            $package = $kernel->getPackageInstance($packageName);
        }
        
        if($package)
        {
            return $package->getResourcePath() . DIRECTORY_SEPARATOR .  $resource;
        }
        
        return $this->_application_resources_path . DIRECTORY_SEPARATOR . $resource;
    }
    
    /**
     * Restituisce il path assoluto delle viste
     * 
     * @param String $view      vista
     * @param String $package   Package, default null, quello usato nel routing
     * 
     * @return String
     */
    public function getViewPath($view, $package = null)
    {
        $kernel      =  $this->getApplicationKernel();
        $packageName =  $package ? $package : $this->_package;
        $package     = null;
        
        if($kernel->isValidPackage($packageName))
        {
            $package = $kernel->getPackageInstance($packageName);
        }
        
        if(strpos($view,$this->_tpl_file_extension) === false)
        {
            $view = $view.'.'.$this->_tpl_file_extension;
        }
                
        if($package)
        {
            return $package->getViewsPath() . DIRECTORY_SEPARATOR . $view;
        }
        
        return $this->_application_views_path .DIRECTORY_SEPARATOR. $view;
    }
    
    
    /**
     * Costruisce il nome del Template, opzionale aggiunge o rimuove il path assoluto
     * 
     * @param String  $tplname   Nome del template
     * @param Boolean $fullPath  Indica se costruire il path completo, Default true
     * 
     * @return String
     */
    public function buildTplPath($tplname,$fullPath = true)
    {
        $templatePath = "";
        
        if(file_exists($tplname.(!strpos($tplname,'.'.$this->_tpl_file_extension) ?  '.'.$this->_tpl_file_extension : '')))
        {
           return $tplname;
        }

        if(preg_match('/@([A-z0-9\_\-]+)\//',$tplname,$matches))
        {
            $this->setPackage($matches[1]);
            $tplname     = str_replace('@'.$matches[1].DIRECTORY_SEPARATOR,'',$tplname);
        }
        
        if($fullPath)
        {
           $templatePath.= $this->_buildTemplateAbsolutePath();
        }
        
        $templatePath.= $tplname.(!strpos($tplname,'.'.$this->_tpl_file_extension) ?  '.'.$this->_tpl_file_extension : '');
        
        return $templatePath;
    }
    
    /**
     * Restituisce il path assoluto della cartella template da utilizzare
     * 
     * @return String 
     */
    private function _buildTemplateAbsolutePath()
    {         
         if($this->_package)
         {
            $templateAbsolutePath = $this->_root_path;

            if(strlen($this->_package_directory) > 0)
            {
               $templateAbsolutePath.= DIRECTORY_SEPARATOR.$this->_package_directory;
            }

            if(strlen($this->_package) > 0)
            {
                $templateAbsolutePath.= DIRECTORY_SEPARATOR.$this->_package;
            }

            if(strlen($this->_tpl_path) > 0)
            {
                $templateAbsolutePath.= DIRECTORY_SEPARATOR.$this->_tpl_path;
            }

            if(strlen($this->_tpl_subfolder) > 0)
            {
                $templateAbsolutePath.= DIRECTORY_SEPARATOR.$this->_tpl_subfolder;
            }
         }
         else
         {
             $templateAbsolutePath = $this->_application_views_path;
         }
         
         return $templateAbsolutePath . DIRECTORY_SEPARATOR;
    }
    
}