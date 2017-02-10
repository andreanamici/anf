<?php

/**
 * Classe Astratta wrapper del template engine usato
 * 
 * Questa classe deve essere sempre estesa dalla classe contenitore del template engine
 * <b>Ogni template engine installato deve essere quindi wrappato nel in questo contenitore, e deve essere sfruttato con lo scopo esclusivo di rendere template</b>
 * 
 */
abstract class Abstract_TemplateEngine implements Interface_TemplateEngine
{   
    use Trait_Singleton,Trait_ObjectUtilities,
            
        Trait_ApplicationKernel,
            
        Trait_ApplicationConfigs,
           
        Trait_ApplicationLanguages;
    
    /**
     * Array di configurazione
     * @var Array
     */
    public static  $_TEMPLATE_ENGINE_CONFIGURATION = Array();
    
    /**
     * Nome del template engine
     * @var String
     */
    public static  $_TEMPLATE_ENGINE_NAME = "";
    
    /**
     * Instanza del template Engine utilizzato
     * @var Mixed
     */
    private $_template_engine         = null;
    
    private $_template_params         = Array();
    private $_template_to_load        = Array();
    private $_template_dir            = "";
    private $_template_file_extension = "tpl";
    
    private $_charset                 = 'UTF-8';
    private $_debug                   = false;
    private $_phpenable               = false;
    private $_sandbox                 = false;
    
    private $_use_cache               = true;
    private $_cache_dir               = "";
    private $_cache_expire_time       = 86400;
    private $_tpl_compiled_source     = "";
    private $_tpl_compiled_directory  = "";
    
    private $_tpl_base_url            = "";
    private $_tpl_base_url_js         = "";
    private $_tpl_base_url_css        = "";
    private $_tpl_base_url_media      = "";
    private $_tpl_base_url_images     = "";
    private $_tpl_base_url_photos     = "";
    
    
    /**
     * Imposta l'instanza del template engine utilizzato
     * 
     * @param Mixed $templateEngineInstance Instanza template
     * 
     * @return \Abstract_TemplateEngine
     */
    protected function setTemplateEngineInstance($templateEngineInstance)
    {
       $this->_template_engine = $templateEngineInstance;
       return $this;
    }
    
    
    /**
     * Imposta il charset utilizzato
     * 
     * @param String $charset
     * 
     * @return \Abstract_TemplateEngine
     */
    public function setCharset($charset)
    {
       $this->_charset = $charset;
       return $this;
    }
    
    /**
     * Restituisce il charset utilizzato
     * 
     * @return String
     */
    public function getCharset()
    {
       return $this->_charset;
    }
    
    /**
     * Imposta se il sistema è in debug
     * 
     * @param Boolean $debug
     * 
     * @return \Abstract_TemplateEngine
     */
    public function setDebug($debug)
    {
       $this->_debug = $debug;
       return $this;
    }
    
    /**
     * Indica se il sistema è in debug
     * 
     * @param Boolean $debug
     * 
     * @return \Abstract_TemplateEngine
     */
    public function getDebug()
    {
       return $this->_debug;
    }
    
    /**
     * Imposta se il template supporta i tag nativi di php
     * 
     * @param Boolean $phpenable
     * 
     * @return \Abstract_TemplateEngine
     */
    public function setPHPTagsEnable($phpenable)
    {
       $this->_phpenable = $phpenable;
       return $this;
    }
    
    /**
     * Indica se il template supporta i tag nativi di php
     * 
     * @param Boolean $phpenable
     * 
     * @return \Abstract_TemplateEngine
     */
    public function getPHPTagsEnable()
    {
       return $this->_phpenable;
    }
    
    /**
     * Imposta se il template è in sandbox mode
     * 
     * @param Boolean $phpenable
     * 
     * @return \Abstract_TemplateEngine
     */
    public function setSandbox($sandbox)
    {
       $this->_sandbox = $sandbox;
       return $this;
    }
    
    /**
     * Indica se il template è in sandbox mode
     * 
     * @param Boolean $phpenable
     * 
     * @return \Abstract_TemplateEngine
     */
    public function getSandbox()
    {
       return $this->_sandbox;
    }
    
    /**
     * Imposta se usare il caching per i template già elaborati
     * 
     * @param type $usechache
     * 
     * @return Abstract_TemplateEngine
     */
    public function setUseCache($usecache)
    {
       $this->_use_cache = $usecache;
       return $this;
    }
    
    /**
     * Imposta la durata della cache in sec
     * 
     * @param Int $cachetime
     * 
     * @return Abstract_TemplateEngine
     */
    public function setCacheExpireTime($cachetime)
    {
       $this->_cache_expire_time = $cachetime;
       return $this;
    }
    
    /**
     * Imposta la directory in cui creare i template php compilati da eseguire. Controlla l'esistenza della directory e se non esiste la crea
     * 
     * @param String $dir path assoluto cache directory
     * 
     * @return Abstract_TemplateEngine
     */
    public function setCompiledDirectory($dir)
    {
       if(file_exists($dir)==false){
          mkdir($dir,0777,true);
       }
       
       $this->_tpl_compiled_directory = $dir;
       return $this;
    }
    
    /**
     * Imposta la directory per il caching dei template già processati. Controlla l'esistenza della directory e se non esiste la crea
     * 
     * @param String $dir path assoluto cache directory
     * 
     * @return Abstract_TemplateEngine
     */
    public function setCacheDirectory($dir)
    {         
       if(file_exists($dir)==false)
       {
          $mkdir = mkdir($dir,0777,true);
          if(!$mkdir){
             $this->throwNewException(8324829349779604,'Impossibile creare la directory caching dei template: '.$dir);
          }
       }
       
       $this->_cache_dir = $dir;
       return $this;
    }
    
    /**
     * Imposta i parametri da utilizzare nel template
     * 
     * @param Array $params Parametri
     * 
     * @return Abstract_TemplateEngine
     */
    public function setTemplateParams($params)
    {
       $this->_template_params = $params;
       return $this;
    }
    
    /**
     * Imposta l'array di template da caricare
     * 
     * @param Array $params lista template da caricare
     * 
     * @return Abstract_TemplateEngine
     */
    public function setTemplateToLoad($tplList)
    {
       $this->_template_to_load = $tplList;
       return $this;
    }
    
    /**
     * Imposta la directory dei template
     * 
     * @param String  $dir  Path directory dei template
     * 
     * @return Abstract_TemplateEngine
     */
    public function setTemplateDirectory($dir)
    {
       if(substr($dir, strlen($dir)-1,1)!="/"){
           $dir.="/";
       }
       $this->_template_dir = $dir;
       return $this;
    }
    
    /**
     * Indica il nome dell'estensione del file del template da compilare
     * 
     * @param String $ext nome estensione file
     * 
     * @return Abstract_TemplateEngine
     */
    public function setTemplateFileExtension($ext)
    {
       $this->_template_file_extension = $ext;
       return $this;
    }
    
    /**
     * Imposta il base url utilizzato dal template engine per riscrevere i percorsi relativi in percorsi con questo baseurl 
     * 
     * @param String $url
     * 
     * @return Abstract_TemplateEngine
     */
    public function setTemplateBaseUrl($url)
    {
       $this->_tpl_base_url = $url;
       return $this;
    }
    
    /**
     * Imposta il base url utilizzato dal template engine per riscrevere i percorsi relativi degli script javascript in percorsi con questo baseurl
     * 
     * @param String $url
     * 
     * @return Abstract_TemplateEngine
     */
    public function setTemplateBaseUrlJavascript($url)
    {
       $this->_tpl_base_url_js = $url;
       return $this;
    }
    
    /**
     * Imposta il base url utilizzato dal template engine per riscrevere i percorsi relativi dei file css in percorsi con questo baseurl
     * 
     * @param String $url
     * 
     * @return Abstract_TemplateEngine
     */
    public function setTemplateBaseUrlCss($url)
    {
       $this->_tpl_base_url_css = $url;
       return $this;
    }
    
    /**
     * Imposta il base url utilizzato dal template engine per riscrevere i percorsi relativi dei file multimediali audio/video in percorsi con questo baseurl
     * 
     * @param String $url
     * 
     * @return Abstract_TemplateEngine
     */
    public function setTemplateBaseUrlMedia($url)
    {
       $this->_tpl_base_url_media = $url;
       return $this;
    }
    
    /**
     * Imposta il base url utilizzato dal template engine per riscrevere i percorsi relativi delle immagini in percorsi con questo baseurl
     * 
     * @param String $url
     * 
     * @return Abstract_TemplateEngine
     */
    public function setTemplateBaseUrlImages($url)
    {
       $this->_tpl_base_url_images = $url;
       return $this;
    }
    
    
    /**
     * Imposta la stringa di compilazione del file php che verrà processato
     * 
     * @return String php file string content
     * 
     * @return Abstract_TemplateEngine
     */
    protected function setCompiledSource($phpSourceCode)
    {
       $this->_tpl_compiled_source = $phpSourceCode;
       return $this;
    }
    
    /**
     * Imposta il base url utilizzato dal template engine per riscrevere i percorsi relativi delle foto del portale in percorsi con questo baseurl
     * 
     * @param String $url
     * 
     * @return Abstract_TemplateEngine
     */
    public function setTemplateBaseUrlPhotos($url)
    {
       $this->_tpl_base_url_photos = $url;
       return $this;
    }
    
    
    /**
     * Restituisce l'instanza del template engine instanziato
     * @return Mixed
     */
    public function getTemplateEngineInstance(){
       return $this->_template_engine;
    }
    
    /**
     * Restituisce se usare il caching per i template già elaborati
     * @return Boolean
     */
    public function getUseCache(){
       return $this->_use_cache;
    }
    
    /**
     * Restituisce la durata della cache in sec
     * @return Int
     */
    public function getCacheExpireTime(){
       return $this->_cache_expire_time;
    }
    
    /**
     * Restituisce la directory in cui creare i template php compilati da eseguire
     * @return String
     */
    public function getCompiledDirectory(){
       return $this->_tpl_compiled_directory;
    }
    
    
    /**
     * Restituisce la directory per il caching dei template.
     * @return String
     */
    public function getCacheDirectory(){
       return $this->_cache_dir;
    }
    
    /**
     * Restituisce i parametri da utilizzare nel template
     * @return Array
     */
    public function getTemplateParams(){
       return $this->_template_params;
    }
    
    /**
     * Restituisce l'array di template da caricare
     * @return Array
     */
    public function getTemplateToLoad(){
       return $this->_template_to_load;
    }
    
    /**
     * Restituisce la directory dei template
     * @return String Path
     */
    public function getTemplateDirectory(){
       return $this->_template_dir;
    }
    
    /**
     * Restituisce il path in cui cercare i template di default
     * @return String
     */
    public function getDefaultTemplateDirectory()
    {
        return $this->getApplicationConfigs()->getConfigsValue('APPLICATION_RESOURCES_TEMPLATE_DIRECTORY_PATH');
    }
    
    /**
     * Restituisce il nome dell'estensione del file del template da compilare
     * @return String
     */
    public function getTemplateFileExtension(){
       return $this->_template_file_extension;
    }
    
    /**
     * Restituisce il base url per il template
     * @return String path relative
     */
    public function getTempateBaseUrl(){
       return $this->_tpl_base_url;
    }
    
    /**
     * Restituisce il base url javascript per il template
     * @return String path relative
     */
    public function getTempateBaseUrlJavascript(){
       return $this->_tpl_base_url_js;
    }
    
    
    /**
     * Restituisce il base url css per il template
     * @return String path relative
     */
    public function getTempateBaseUrlCss(){
       return $this->_tpl_base_url_css;
    }
    
    /**
     * Restituisce il base url per i file multimediali  per il template
     * @return String path relative
     */
    public function getTempateBaseUrlMedia(){
       return $this->_tpl_base_url_media;
    }
    
    /**
     * Restituisce il base url per le immagini per il template
     * @return String path relative
     */
    public function getTempateBaseUrlImages(){
       return $this->_tpl_base_url_images;
    }
    
    /**
     * Restituisce il base url per le foto  per il template
     * 
     * @return String path relative
     */
    public function getTempateBaseUrlPhotos(){
       return $this->_tpl_base_url_photos;
    }
    
    /**
     * Restiuisce la stringa di compilazione del file php che verrà processato
     * @return String php file string content
     */
    public function getCompiledSource(){
       return $this->_tpl_compiled_source;
    }
    
    /**
     * Restituisce il nome del Template Engine
     * @return String
     */
    public static function getTemplateEngineName(){
       return self::$_TEMPLATE_ENGINE_NAME;
    }
    
    
    public function __construct()
    {
        if(strlen($this->getDefaultTemplateDirectory()) > 0 && !file_exists($this->getDefaultTemplateDirectory()))
        {
            mkdir($this->getDefaultTemplateDirectory(),0755,true);
        }
        
        $this->_tpl_compiled_directory = "";
        
        $this->configureTplEngine();
    }
    
    /**
     * Effettua la configurazione necessaria al template Engine prima del drawTemplate()!
     * 
     * @return Boolean
     */
    abstract public function configureTplEngine();
    
    
    /**
     * Effettua la compilazione del template
     * 
     * @return Boolean
     */
    abstract public function drawTemplate();
    
    /**
     * Pulisce la cache dai template con un expire date scaduto
     * 
     * @return Boolean
     */
    abstract public function clearCache();
    
    /**
     * Effettua la compilazione di una stringa e restituisce il codice compilato HTML
     * @param String $string
     * @return String
     */
    abstract public function drawString($string,array $parameters = array());
    
    /**
     * Visualizza il template elaborato oppure restituisce l'output come stringa
     * 
     * @param String $getoutput Indica se ricevere l'output del template compilato oppure lo elabora direttamente
     * 
     * @return Void | String
     */
    abstract public function view($getoutput = false);
}