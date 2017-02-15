<?php

/**
 * Classe per la gestione di tutta la request dell'applicazione, permette di modificare i dati dell' HTTP Request
 * 
 * @method Application_HttpRequest getInstance() Restituisce l'instanza originale di questo oggetto sfruttando il DesignPattern Singleton
 */
class Application_HttpRequest
{
   use Trait_Singleton; 
    
   use Trait_ApplicationKernel;
   
   /**
    * Contiene i dati POST nel momento dell'elaborazione
    * 
    * @var Application_ArrayObjectBag
    */
   protected $POST        = null;
   
   /**
    * Contiene i dati in Get nel momento dell'elaborazione
    * 
    * @var Application_ArrayObjectBag
    */
   protected $GET         = null;
   
   /**
    * Contiene i dati della Request nel momento dell'elaborazione
    * 
    * @var Application_ArrayObjectBag
    */
   protected $REQUEST     = null;
   
   /**
    * Contiene i dati in Sessione nel momento dell'elaborazione
    * 
    * @var Application_ArrayObjectBag
    */
   protected $SESSION     = null;
   
   /**
    * Contiene i dati in cookie nel momento dell'elaborazione
    * 
    * @var Application_ArrayObjectBag
    */
   protected $COOKIE     = null;
   
   /**
    * Contiene i dati del Server nel momento dell'elaborazione
    * 
    * @var Application_ArrayObjectBag
    */
   protected $SERVER     = null;
   
   /**
    * Contiene eventuali file da caricare nel momento dell'eleborazione
    * 
    * @var Application_ArrayObjectBag 
    */
   protected $FILE       = null;
   
   /**
    * Contiene i dati di ENVIRONMENT dell'applicativo
    * 
    * @var Application_ArrayObjectBag
    */
   protected $ENV        = null;
   
   /**
    * Headers connessione
    * 
    * @var Application_ArrayObjectBag
    */
   protected $HEADERS    = null;
   
   /**
    * Tipologia di request, POST/GET verso l'action
    * 
    * @var String
    */
   protected $_method    = null;
      
   /**
    * Indirizzo IP chiamante
    * 
    * @var String
    */
   protected $_ip;
   
   /**
    * User Agent Chiamante
    * 
    * @var String
    */
   protected $_user_agent;
   
   /**
    * Protocollo HTTP
    * 
    * @var String
    */
   protected $_protocol  = 'http';
   
   /**
    * Base Url
    * 
    * @var String
    */
   protected $_baseurl = '';
   
   /**
    * Path assoluto
    * 
    * @var String
    */
   protected $_path = '';
   
   /**
    * Path puliti da eventuali script di front-controller presenti
    * 
    * @var String
    */
   protected $_path_without_scriptname = '';
   
   /**
    * Host
    * 
    * @var String
    */
   protected $_host    = '';
   
   /**
    * Indica se la connession è https
    * 
    * @var Boolean
    */
   protected $_is_https = false;
   
   /**
    * Restituisce il protocollo usato e host
    * 
    * @var String
    */
   protected $_protocol_host = '';
   
   /**
    * Inizializza i dati della Request
    * 
    * @param array $request   [OPZIONALE] Request parameters, NULL per non variare
    * @param array $post      [OPZIONALE] Post parameters, NULL per non variare
    * @param array $get       [OPZIONALE] Get parameters, NULL per non variare
    * @param array $session   [OPZIONALE] Session parameters, NULL per non variare
    * @param array $server    [OPZIONALE] Server parameters, NULL per non variare
    * @param array $file      [OPZIONALE] Files parameters, NULL per non variare
    * @param array $cookie    [OPZIONALE] Cookie parameters, NULL per non variare
    * @param array $env       [OPZIONALE] Enviroment parameters, NULL per non variare
    * 
    * @return \Application_HttpRequest
    */
   public function initialize(array $request = null,array $post = null,array $get = null,array $session = null,array $server = null, array $file = null,array $cookie = null,array $env = null)
   {
 
      $sessionManager =  $this->getApplicationKernel()->get('@session');/*@var $sessionManager Application_SessionManager*/
      $cookieManager  =  $this->getApplicationKernel()->get('@cookie'); /*@var $cookieManager Application_CookieManager*/

      $self = $this;
            
      $this->REQUEST  =  !is_null($request) ? new \Application_ArrayObjectBag($request,array(
          
                                Application_ArrayObjectBag::ON_OFFSET_GET      => function($key,$default = false,$xss = true) use($self,&$request){ $val = array_dot_notation($request, $key,$default); return $xss ?  $self->xssFilter($val) : $val; },
                                Application_ArrayObjectBag::ON_OFFSET_SET      => function($key,$value) use($self,&$request){ $request[$key] = $value; },
                                Application_ArrayObjectBag::ON_OFFSET_UNSET    => function($key) use($self,&$request){ unset($request[$key]); },
                                Application_ArrayObjectBag::ON_OFFSET_ALL      => function($xss = true) use($self,&$request){ return $xss ? $self->xssFilter($request) : $request; },
                                Application_ArrayObjectBag::ON_ARRAY_EXCHANGE  => function($array) use($self,&$request){ $request =  $array; return  $self->getRequest(); }
                                
                         )) : ($this->REQUEST ? $this->REQUEST : new \Application_ArrayObjectBag());
                         
      $this->POST     =  !is_null($post) ? new \Application_ArrayObjectBag($post,array(
          
                                Application_ArrayObjectBag::ON_OFFSET_GET      => function($key,$default = false,$xss = true) use($self,&$post){ $val = array_dot_notation($post, $key,$default); return $xss ?  $self->xssFilter($val) : $val;  },
                                Application_ArrayObjectBag::ON_OFFSET_SET      => function($key,$value) use($self,&$post){ $post[$key] = $value;  },
                                Application_ArrayObjectBag::ON_OFFSET_UNSET    => function($key) use($self,&$post){ unset($post[$key]); },
                                Application_ArrayObjectBag::ON_OFFSET_ALL      => function($xss = true)  use($self,&$post){ return $xss ? $self->xssFilter($post) : $post; },
                                Application_ArrayObjectBag::ON_ARRAY_EXCHANGE  => function($array) use($self,&$post){ $post = $array; return $self->getPost(); }
                                
                         ))  : ($this->POST ? $this->POST : new \Application_ArrayObjectBag());
                                
      $this->GET      =  !is_null($get) ? new \Application_ArrayObjectBag($get,array(
          
                                Application_ArrayObjectBag::ON_OFFSET_GET     => function($key,$default = false,$xss = true)  use($self,&$get){ $val = array_dot_notation($get, $key,$default); return $xss ?  $self->xssFilter($val) : $val; },
                                Application_ArrayObjectBag::ON_OFFSET_SET     => function($key,$value) use($self,&$get){ $get[$key] = $value;  },
                                Application_ArrayObjectBag::ON_OFFSET_UNSET   => function($key) use($self,&$get){ unset($get[$key]); },
                                Application_ArrayObjectBag::ON_OFFSET_ALL     => function($xss = true)  use($self,&$get){ return $xss ? $self->xssFilter($get) : $get; },
                                Application_ArrayObjectBag::ON_ARRAY_EXCHANGE => function($array) use($self,&$get){ $get = $array; return $self->getGet(); }
                                
                         )) : ($this->GET ? $this->GET : new \Application_ArrayObjectBag());
                                
      $this->SERVER   =  !is_null($server) ? new \Application_ArrayObjectBag($server,array(
          
                                Application_ArrayObjectBag::ON_OFFSET_SET     => function($key,$value) use($self,&$server){ $server[$key] = $value;  },
                                Application_ArrayObjectBag::ON_OFFSET_UNSET   => function($key) use($self,&$server){ unset($server[$key]); },
                                Application_ArrayObjectBag::ON_OFFSET_ALL     => function($xss = true) use($self,&$server){ return $server; },
                                Application_ArrayObjectBag::ON_ARRAY_EXCHANGE => function($array) use($self,&$server){ $server = $array; return $self->getServer(); }
                                
                         )) : ($this->SERVER ? $this->SERVER : new \Application_ArrayObjectBag());
                         
      $this->FILE     =  !is_null($file) ? new \Application_ArrayObjectBag($file,array(
          
                                Application_ArrayObjectBag::ON_OFFSET_SET     => function($key,$value) use($self,&$file){ $file[$key] = $value;  },
                                Application_ArrayObjectBag::ON_OFFSET_UNSET   => function($key) use($self,&$file){ unset($file[$key]); },
                                Application_ArrayObjectBag::ON_OFFSET_ALL     => function($xss = true) use($self,&$file){ return $file; },
                                Application_ArrayObjectBag::ON_ARRAY_EXCHANGE => function($array) use($self,&$file){ $file = $array; return $self->getFile(); }
                                
                         )) : ($this->FILE ? $this->FILE : new \Application_ArrayObjectBag());
            
      $this->ENV      =  !is_null($env) ? new \Application_ArrayObjectBag($env,array(
          
                                Application_ArrayObjectBag::ON_OFFSET_SET     => function($key,$value) use($self,&$env){ $env[$key] = $value;  },
                                Application_ArrayObjectBag::ON_OFFSET_UNSET   => function($key) use($self,&$env){ unset($env[$key]); },
                                Application_ArrayObjectBag::ON_OFFSET_ALL     => function($xss = true) use($self,&$env){ return $env; },
                                Application_ArrayObjectBag::ON_ARRAY_EXCHANGE => function($array) use($self,&$env){ $env = $array; return $self->getEnv(); }
                                
                         )) : ($this->ENV ? $this->ENV : new \Application_ArrayObjectBag());
                         
      /**
       * Ogni qualvolta modifico la session dell'HttpRequest applico le callback del SessionManager
       */
      $this->SESSION  =  !is_null($session) ? new \Application_ArrayObjectBag($session,array(          
          
                                Application_ArrayObjectBag::ON_OFFSET_SET    => array($sessionManager,'addIndex'),
                                Application_ArrayObjectBag::ON_OFFSET_UNSET  => array($sessionManager,'removeIndex'),
                                Application_ArrayObjectBag::ON_OFFSET_GET    => array($sessionManager,'getIndex'),
                                Application_ArrayObjectBag::ON_OFFSET_EXISTS => array($sessionManager,'exists'),
                                Application_ArrayObjectBag::ON_OFFSET_ALL    => array($sessionManager,'getAll'),
                                Application_ArrayObjectBag::ON_ARRAY_EXCHANGE => function($array) use($self,$sessionManager){ $sessionManager->exchangeArray($array); return $sessionManager->getAll(); }
          
                         )) : ($this->SESSION ? $this->SESSION : new \Application_ArrayObjectBag());
      /**
       * Ogni qualvolta modifico i cookie dell'HttpRequest applico le callback del CookieManager
       */
      $this->COOKIE   =  !is_null($cookie) ? new \Application_ArrayObjectBag($cookie,array(          
          
                                Application_ArrayObjectBag::ON_OFFSET_SET    => array($cookieManager,'addIndex'),
                                Application_ArrayObjectBag::ON_OFFSET_UNSET  => array($cookieManager,'removeIndex'),
                                Application_ArrayObjectBag::ON_OFFSET_GET    => array($cookieManager,'getIndex'),
                                Application_ArrayObjectBag::ON_OFFSET_EXISTS => array($cookieManager,'exists'),
                                Application_ArrayObjectBag::ON_OFFSET_ALL    => array($cookieManager,'getAll'),
                                Application_ArrayObjectBag::ON_ARRAY_EXCHANGE => function($array) use($self,$cookieManager){ $cookieManager->exchangeArray($array); return $cookieManager->getAll(); }
          
                         )) : ($this->COOKIE ? $this->COOKIE : new \Application_ArrayObjectBag());
                         
      $requestHeaders =  server_request_headers($server);
    
      $this->HEADERS  =  is_array($requestHeaders) ? new \Application_ArrayObjectBag($requestHeaders) : null;
      
      $this->_method  =  $this->SERVER->getVal('REQUEST_METHOD','GET');
      
      if($this->SERVER)
      {
          $this->_protocol      = (!empty($this->SERVER['HTTPS']) && $this->SERVER['HTTPS'] !== 'off' || $this->SERVER['SERVER_PORT'] == 443) ? 'https' : 'http';
          $this->_baseurl       = $this->getApplicationKernel()->get('routing')->getBaseUrl();//isset($this->SERVER["HTTP_HOST"]) ? $this->SERVER["HTTP_HOST"].getConfigValue('HTTP_ROOT') : getConfigValue('HTTP_SITE');
          $this->_host          = str_replace($this->_protocol."://","",$this->SERVER["HTTP_HOST"]);
          
          if(empty($this->_baseurl))
          {
             $this->_baseurl = '/';
          }

          $this->_path                       = str_replace($this->_host,'',$this->_baseurl);
          $this->_path_without_scriptname    = preg_replace('/[a-z\_]+\.php/','',$this->_baseurl);   //Rimuovo eventuali front-controller dall'url
          
          if(empty($this->_path_without_scriptname) || substr($this->_path_without_scriptname,-1) != '/')
          {
              $this->_path_without_scriptname.= '/'; 
          }
          
          $this->_is_https      = isset($this->SERVER['HTPS']) && $this->SERVER['HTTPS'] == 'on' ? true : false;
          $this->_protocol_host = $this->_protocol.'://'.$this->_host;
          $this->_user_agent    = $this->HEADERS->getIndex('User-Agent',null);
          
          $this->_ip            = $this->SERVER->getVal('HTTP_CLIENT_IP', //check ip from share internet
                                       $this->SERVER->getVal('HTTP_X_FORWARDED_FOR',  //to check ip is pass from proxy
                                           $this->SERVER->getVal('REMOTE_ADDR','unknow')));
      }
      
      return $this;
   }
   
   /**
    * Restituisce il protocollo utilizzato
    * 
    * @return String
    */
   public function getProtocol()
   {
       return $this->_protocol;
   }
   
   /**
    * Restituisce il base url
    * 
    * @param String $path  path da concatenare al base url, default null
    * 
    * @return String
    */
   public function getBaseUrl($path = '')
   {
       return $this->_baseurl;
   }
   
   /**
    * Restituisce il path a partire da "/"
    * 
    * @return String
    */
   public function getPath()
   {
       return $this->_path_without_scriptname;
   }
      
   /**
    * Restituisce l'host
    * 
    * @return String
    */
   public function getHost()
   {
       return $this->_host;
   }
   
   /**
    * Restituisce il protocollo usato e l'host
    * 
    * @return String
    */
   public function getProtocolAndHost()
   {
       return $this->_protocol_host;
   }
   
   /**
    * Restituisce l'url assoluto di un path indicato
    * 
    * @param strng $path path relativo
    * 
    * @return string
    */
   public function getAbsoluteUrl($path = null)
   {
       return $this->getProtocolAndHost() . $this->getPath() . $path;
   }
   
   /**
    * Restituisce l'IP in uso dal chiamante
    * 
    * @return string
    */
   public function getIp()
   {
       return $this->_ip;
   }
   
   /**
    * Restituisce lo user agent usato dal chiamante
    * 
    * @return string
    */
   public function getUserAgent()
   {
       return $this->_user_agent;
   }
   
   /**
    * Indica se la connessione è https
    * @return Boolean
    */
   public function isHttps()
   {
       return $this->_is_https;
   }
   
   /**
    * Restiusce l'ArrayObject dei dati per l'attributo REQUEST al momento dell'elaborazione dell'Action
    * 
    * @return \Application_ArrayObjectBag
    */
   public function getRequest()
   {
      return $this->REQUEST;
   }
   
   /**
    * Restiusce l'ArrayObject dei dati per l'attributo POST al momento dell'elaborazione dell'Action
    * 
    * @return Application_ArrayObjectBag
    */
   public function getPost()
   {
      return $this->POST;
   }
   
   /**
    * Restiusce l'ArrayObject dei dati per l'attributo SESSION al momento dell'elaborazione dell'Action
    * 
    * @return Application_ArrayObjectBag
    */
   public function getSession()
   {
      return $this->SESSION;
   }
   
   /**
    * Restiusce l'ArrayObject dei dati per l'attributo GET al momento dell'elaborazione dell'Action
    * 
    * @return Application_ArrayObjectBag
    */
   public function getGet()
   {
      return $this->GET;
   }
   
   /**
    * Restiusce l'ArrayObject dei dati per l'attributo COOKIE al momento dell'elaborazione dell'Action
    * 
    * @return Application_ArrayObjectBag
    */
   public function getCookie()
   {
      return $this->COOKIE;
   }
   
   /**
    * Restiusce l'ArrayObject dei dati per l'attributo FILE al momento dell'elaborazione dell'Action
    * 
    * @return Application_ArrayObjectBag
    */
   public function getFile()
   {
      return $this->FILE;
   }
   
   /**
    * Restiusce l'ArrayObject dei dati per l'attributo SERVER al momento dell'elaborazione dell'Action
    * 
    * @return Application_ArrayObjectBag
    */
   public function getServer()
   {
      return $this->SERVER;
   }
   
   
   /**
    * Restiusce l'ArrayObject dei dati per l'attributo HEADERS al momento dell'elaborazione dell'Action
    * 
    * @return Application_ArrayObjectBag
    */
   public function getHeaders()
   {
       return $this->HEADERS;
   }
   
   /**
    * Restiusce l'ArrayObject dei dati di ENVIRONMENT dell'applicativo
    * 
    * @return Application_ArrayObjectBag
    */
   public function getEnv()
   {
      return $this->ENV;
   }
   
   /**
    * Ricerca un parametro nella REQUEST, o in POST, o GET e se lo trova lo restituisce
    * 
    * @param String  $key      Parametri da ricercare, dot notation supportata
    * @param String  $default  Valore di default
    * @param Boolean $xss      Indica se effettuare l'xss, default TRUE
    * 
    * @return Mixed
    */
   public function get($key,$default = false,$xss = true)
   {   
      return  $this->REQUEST->getIndex($key,
                  $this->POST->getIndex($key,
                      $this->GET->getIndex($key,
              $default,$xss),$xss),$xss);
   }
   
   /**
    * Indica se la request attuale ha il parametro indicato
    * 
    * @param String $key Chiave, dot notation supportata
    * 
    * @return Boolean
    */
   public function has($key)
   {
       return $this->get($key,false) !== false;
   }
   
   /**
    * Imposta il valore nella REQUEST (GET/POST compresi)
    * 
    * @param String $key    Chiave
    * @param String $value  Valore
    * 
    * @return \Application_HttpRequest
    */
   public function set($key,$value)
   {
       $this->REQUEST->offsetSet($key, $value);
       $this->GET->offsetSet($key, $value);
       $this->POST->offsetSet($key, $value);
       return $this;
   }
   
   /**
    * Ricerca un parametro nella REQUEST, o in POST, o GET qualora non sia empty() e se lo trova lo restituisce
    * 
    * @param String  $key      Parametri da ricercare
    * @param String  $default  Valore di default
    * @param Boolean $xss      Indica se effettuare l'xss, default TRUE
    * 
    * @return Mixed
    */
   public function getVal($key,$default = false,$xss = true)
   {
      return  $this->REQUEST->getVal($key,
                  $this->POST->getVal($key,
                      $this->GET->getVal($key,
              $default,$xss),$xss),$xss);
   }
   
   /**
    * Restituisce la tipologia di Request attualmente instanziata tra server e client
    * 
    * @return String
    */
   public function getMethod()
   {
      return $this->_method;
   }
   
   /**
    * Restituisce TRUE qualora la connessione attualmente gestiata sia in POST, quindi si stanno trasmettendo dei dati al server
    * 
    * @return Boolean
    */
   public function isMethodPost()
   {
      return $this->_method == 'POST';
   }
   
   /**
    * Restituisce TRUE controllando che nel parametro SERVER sia presente uno degli elementi con la chiave fornita dal parametro $values
    * 
    * @param Array $values [OPZIONALE] Array chiave => valore che verra ricercato nella proprietà SERVER di questo oggetto, default array('HTTP_X_REQUESTED_WITH' => 'xmlhttprequest')
    * 
    * @return boolean
    */
   public function isXmlHttpRequest(array $values = array('HTTP_X_REQUESTED_WITH' => 'xmlhttprequest'))
   {
      $server = $this->getServer();
      
      if($server instanceof \Application_ArrayObjectBag)
      {
         if(is_array($values) && count($values) > 0)
         {
            foreach($values as $key => $value)
            {
               if($server->offsetExists($key) && strtolower($server->offsetGet($key)) == $value)
               {
                  return true;
               }
            }
         }
      }
      
      return false;
   }
   
   /**
    * Verifica che il browser in uso sia un dispositovo mobile
    * 
    * @return bool
    */
   public function isMobile()
   {
        return !empty($this->_user_agent) && ( 
                     preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $useragent) ||
                     preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($this->_user_agent,0,4)
               ));
   }
   
   /**
    * Verifica che abbia dei parametri in GET o in POST
    * 
    * @return Boolean
    */
   public function isRequestEmpty()
   {
      return ($this->getGet()->count() == 0 && $this->getPost()->count() == 0);
   }
   
   
   /**
    * Verifica che sia presente il file indicato per il caricamento
    * 
    * @param String $fieldName File
    * 
    * @return Boolean
    */
   public function hasFile($fieldName)
   {
       return $this->FILE->count() > 0 && $this->FILE->offsetExists($fieldName);
   }
   
   
   /**
    * Carica il file presente in \Application_ActionRequestData::$FILE
    * 
    * @param String $fieldName Nome del file
    * @param array  $configs   Array delle configurazioni da passare all'utilityUpload utilizzato per il caricamento
    * @param String $service   Nome del service per effettuare l'upload, default 'utility.upload' (Questo servizio dovrà estendere la classe nativa \Utility_Upload
    * 
    * @return Array  File caricati, FALSE altrimenti
    * 
    * @see \Utility_Upload
    */
   public function uploadFile($fieldName,array $configs = array(),$service = 'utility.upload')
   {
       if($this->FILE->offsetExists($fieldName))
       {
            $upload = getApplicationService($service);/*@var $upload \Utility_Upload*/

            $configs = array_merge(array(
                        'fileArr'           => $this->FILE->getArrayCopy(),
                        'fileArrName'       => $fieldName
            ),$configs);
                        
            if($upload->startUpload($configs))
            {
               $this->FILE->offsetUnset($fieldName);

               return $upload->getUploadeArr();
            }
       }
       
       return false;
   }   
   
   /**
    * Inizializza una HttpRequest a partire dai valori global
    * 
    * @return \Application_HttpRequest
    */
   public static function createFromGlobals()
   {
       $httpRequest =  new static();    
       return $httpRequest->initialize($_REQUEST, $_POST, $_GET, isset($_SESSION) ? $_SESSION : array(), $_SERVER, $_FILES, $_COOKIE, $_ENV);
   }
   
   /**
    * Sovrascrive le variabili globali
    * 
    * @return \Application_HttpRequest
    */
   public static function overrideGlobals()
   {
        $httpRequest = static::getInstance();
        
        if($httpRequest)
        {
            $_GET     = $httpRequest->getGet()->getArrayCopy();
            $_POST    = $httpRequest->getRequest()->getArrayCopy();
            $_COOKIE  = $httpRequest->getCookie()->getArrayCopy();
            $_SESSION = $httpRequest->getSession()->getArrayCopy();
            $_FILES   = $httpRequest->getFile()->getArrayCopy();
            $_REQUEST = $httpRequest->getRequest()->getArrayCopy();
            $_ENV     = $httpRequest->getEnv()->getArrayCopy();
        }
        
        return $httpRequest;
   }
   
   /**
    * Filtro i dati della Request
    * 
    * @param mixed $value string / array
    * 
    * @return mixed
    */
   public function xssFilter($value)
   {   
       if(empty($value))
       {
           return $value;
       }
       
       if(!is_array($value))
       {
//          $value = filter_var($value, FILTER_SANITIZE_STRING);
          $value = filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);
          
          return $value;
       }
       
       if(is_array($value))
       {
            foreach($value as $key => $val)
            {
                $value[$key] = $this->xssFilter($val);
            }
       }
       
       return $value;
   }
   
   /**
    * Restituisce l'attributo
    * 
    * @param String $name Nome attributo, lower upper insensitive case
    * 
    * @return Mixed
    */
   public function __get($name)
   {
       if(isset($this->$name))
       {
           return $this->$name;
       }
       
       $nameUpper = strtoupper($name);
       if(isset($this->$nameUpper))
       {
           return $this->$nameUpper;
       }
       
       $nameLower = strtolower($name);
       if(isset($this->$nameLower))
       {
           return $this->$nameLower;
       }
       
       $ucfirstName = ucfirst(strtolower($name));
       if(isset($this->$ucfirstName))
       {
           return $this->$ucfirstName;
       }
       
       return $this->getVal($name, false);
   }
}