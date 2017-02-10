<?php

/**
 * Gestore della Sessione
 */
class Application_SessionManager  implements Interface_ArrayTraversable,ArrayAccess
{
    use Trait_ApplicationKernel,
                
        Trait_ObjectUtilities;
    
    /**
     * Nome della chiave dell'elemento della sessione che contiene i dati 'flash' temporanei
     * @var String
     */
    const FLASH_DATA_BAG_NAME     = '_flashdata';
     
    /**
     * Indica i messaggi normali
     * @var String
     */
    const FLASH_MESSAGES_MESSAGE  = 'message';
    
    /**
     * Indica i messaggi di warning
     * @var String
     */
    const FLASH_MESSAGES_WARNING  = 'message.warning';
   
    /**
     * Indica i messaggi di errore
     * @var String
     */
    const FLASH_MESSAGES_ERROR    = 'message.error';
    
    /**
     * Nome del campo che indica se i flash messages sono abilitati
     * 
     * @var String
     */
    const FLASH_MESSAGES_DISABLE_FLAG_NAME  = 'message.stop';
    
    /**
     * Session Handler nativo, dal php.ini
     * @var String
     */
    const SESSION_HANDLER_NATIVE            = 'files';
    
    protected static $_session_name                    = SESSION_NAME;
    protected static $_session_cookie_path             = SESSION_COOKIE_PATH;
    protected static $_session_cookie_domain           = SESSION_COOKIE_DOMAIN;
    protected static $_session_use_cookies             = SESSION_USE_COOKIES;
    protected static $_session_use_only_cookies        = SESSION_USE_ONLY_COOKIES;
    protected static $_session_cookie_secure           = SESSION_COOKIE_SECURE;
    protected static $_session_cookie_lifetime         = SESSION_COOKIE_LIFETIME;
    protected static $_session_cookie_httponly         = SESSION_COOKIE_HTTPONLY;
    protected static $_session_gc_maxlifetime          = SESSION_GC_MAXLIFETIME;
    protected static $_session_gc_probability          = SESSION_GC_PROBABILITY;
    protected static $_session_gc_division             = SESSION_GC_DIVISION;
    protected static $_session_save_path               = SESSION_SAVE_PATH;
    protected static $_session_save_handler            = SESSION_SAVE_HANDLER;
    protected static $_session_cache_expire            = SESSION_CACHE_EXPIRE;  
    protected static $_session_autostart               = SESSION_AUTOSTART;
    protected static $_session_upload_progress_enabled = SESSION_UPLOAD_PROGRESS_ENABLED;
    protected static $_session_upload_progress_freq    = SESSION_UPLOAD_PROGRESS_FREQ;
    protected static $_session_upload_progress_minfreq = SESSION_UPLOAD_PROGRESS_MIN_FREQ;
    protected static $_session_upload_progress_name    = SESSION_UPLOAD_PROGRESS_NAME;
    protected static $_session_upload_progress_prefix  = SESSION_UPLOAD_PROGRESS_PREFIX;
    protected static $_session_upload_progress_cleanup = SESSION_UPLOAD_PROGRESS_CLEANUP;
    
    /**
     * Handler user-level sessione
     * @var mixed
     */
    protected $_session_handler;
    
    /**
     * Restituisce il Nome della sessione utilizzata
     * @return String
     */
    public function getSessionName(){
        return self::$_session_name;
    }
    
    /**
     * Restituisce il numero di Secondi di durata della sessione
     * @return Int
     */
    public function getSessionCacheExpire()
    {
        return self::$_session_cache_expire;
    }
    
    /**
     * Restituisce tutte le informazioni relative alla sessione attiva
     * 
     * @return Array 
     */
    public function getAll()
    {
       if(isset($_SESSION))
       {
          return $_SESSION; 
       }
       
       return null;
    }

    
    /**
     * Restituisce l'id Sessione corrente, oppure lo rigenera
     * 
     * @param Boolean $new Se TRUE, forza la rigenerazione dell'id
     * 
     * @return String Id Sessione
     */
    public function getSessionId($new=false)
    {
       return $new ? session_regenerate_id() : session_id();
    }
    
    /**
     * Session Manager - Classe per la gestione della sessione
     * 
     * Questo metodo inizializza la classe ed avvia una sessione, controllando che non  sia stata precendentemente aperta
     * 
     * @return boolean 
     */
    public function  __construct($sessionHandler = null)
    {          
        /**
         * Registro la sessione, lancio un hook apposito che potrà estendere le funzionalità di questo manager, registrando ancheun eventuale handler specifico di sessione
         */
        $this->getApplicationKernel()->processHooks(\Interface_HooksType::HOOK_TYPE_SESSION_REGISTER,$this);
        
        if(static::$_session_autostart)
        {
            $this->sessionStart();
        }
                
        return true;
    }

    /**
     * Unset SessionManager
     * @return Boolean 
     */
    public function   __destruct()
    {
        unset($this);
        return true;
    }
    
    
    //ArrayAccess Interface *****************************
    
    public function offsetExists($offset)
    {
        return $this->exists($offset);
    }
    
    public function offsetGet($offset)
    {
        return $this->getIndex($offset);
    }
    
    public function offsetSet($offset, $value)
    {
        return $this->addIndex($offset, $value);
    }
    
    public function offsetUnset($offset)
    {
        return $this->removeIndex($offse);
    }
    
    
    public function exchangeArray(array $data)
    {
        $_SESSION = $data;
        return $this;
    }
    
    
    //ArrayAccess Interface *****************************
    
    
    /**
     * Registra un handler di sessione
     * 
     * @param Interface_SessionHandler $sessionHandler Handler per la gestione della sessione
     * @param Boolean                  $restartSession [OPZIONALE] Indica se reinizializzare la sessione, default TRUE
     * 
     * @return \Application_SessionManager
     */
    public function registerHandler(Interface_SessionHandler $sessionHandler,$restartSession = true)
    {
        if($sessionHandler != self::SESSION_HANDLER_NATIVE)
        {
            session_write_close();
            session_set_save_handler($sessionHandler,true);
            register_shutdown_function('session_write_close');
            
            if($restartSession)
            {
               $this->_session_handler = $sessionHandler;
               $this->sessionStart(true);
            }
        }
        
        return $this;
    }
    
    /**
     * Inizializza le Sessione, controlla che questa non sia stata precendentemente richiamata
     * @return boolean 
     */
    public function sessionStart($restartSession = false)
    {
       if(!$this->getIndex("session_started") || $restartSession)
       {
           if(!isset($this->_session_handler))
           {
               $this->configureSession();
           }
           
           $this->openSession($restartSession);
           $this->addIndex("session_started",time());
           $this->addIndex("timeout",time()+self::$_session_cache_expire);
           return true;
       }  
       
       return false;
    }
    
    /**
     * Inizializza la sessione
     * <b>Verrano riabilitati i messaggi flash</b>
     * 
     * @param Array $options Opzioni per l'inizializzazione dei parametri ini_*
     * 
     * @return \Application_SessionManager
     */
    public function configureSession($options = Array())
    {
       $defaultOptions = $this->getDefaultSettings();
       
       $sessionOptions = array_merge($defaultOptions,$options);
       
       foreach($sessionOptions as $key => $value)
       {
           if(!preg_match('/^session\./',$key))
           {
               return $this->throwNewException(825984592952,'Questa opzione non è valida per il gestore della sessione: '.$key);
           }

           ini_set($key,$value);
       }
       
       return $this;
    }
    
    /**
     * Restituisce la configurazione base della sessione
     * 
     * @return Array
     */
    public function getDefaultSettings()
    {
        return array(
            "session.name"                     => self::$_session_name,
            "session.cookie_path"              => self::$_session_cookie_path,
            "session.cookie_domain"            => self::$_session_cookie_domain,
            "session.use_cookies"              => self::$_session_use_cookies,
            "session.use_only_cookies"         => self::$_session_use_only_cookies,
            "session.cookie_lifetime"          => self::$_session_cookie_lifetime,
            "session.cookie_httponly"          => self::$_session_cookie_httponly,
            "session.cookie_secure"            => self::$_session_cookie_secure,
            "session.gc_maxlifetime"           => self::$_session_gc_maxlifetime,     
            "session.gc_division"              => self::$_session_gc_division,
            "session.gc_probability"           => self::$_session_gc_probability,
            "session.save_handler"             => self::$_session_save_handler,
            "session.save_path"                => self::$_session_save_path,
            "session.cache_expire"             => time()+self::$_session_cache_expire,
            "session.auto_start"               => self::$_session_autostart,
            "session.upload_progress.enabled"  => self::$_session_upload_progress_enabled,
            "session.upload_progress.prefix"   => self::$_session_upload_progress_prefix,
            "session.upload_progress.freq"     => self::$_session_upload_progress_freq,
            "session.upload_progress.min_freq" => self::$_session_upload_progress_minfreq,
            "session.upload_progress.name"     => self::$_session_upload_progress_name,
            "session.upload_progress.cleanup"  => self::$_session_upload_progress_cleanup,
       );
    }
    
    
    /**
     * Avvia la sessione
     * 
     * @return Boolean 
     * 
     * @throws \Exception   Quanto viene avviata una sessione da "cli"
     */
    private function openSession($force = false)
    {   
        if(!$force && session_status() == PHP_SESSION_ACTIVE)
        {
            return false;
        }
        
        if(!$this->getApplicationKernel()->isServerApiCLI())
        {
            if(self::$_session_autostart)
            {
                @session_write_close();
            }

            @session_start();
        }
        
        return true;
    }
    
    /**
     * Chiude la sessione corrente, eliminando tutti i dati in essa memorizzata.
     * 
     * @param Boolean $startNewSession Indica se far partire una nuova sessione, default TRUE
     * 
     * @return boolean 
     */
    public function sessionClose($startNewSession = true)
    {
        if($this->getApplicationKernel()->isServerApiCLI())
        {
           return true; 
        }
        
        $flashData = $this->getIndex(self::FLASH_DATA_BAG_NAME,false);
        
        @session_destroy();
        
        $this->clearData();
        
        if($startNewSession)
        {
            if (static::$_session_use_cookies)
            {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }

            $this->sessionStart($startNewSession);        
            
            if($flashData)
            {
               $this->addIndex(self::FLASH_DATA_BAG_NAME, $flashData);
            }
        }
        
        return true;
    }
    
    
    /**
     * Aggiunge un indice alla sessione Corrente
     * 
     * @param String  $index   Chiave 
     * @param Mixed   $value   Valore
     * @param Array   $options Opzioni
     * 
     * @return Boolean 
     */
    public function addIndex($index,$value,array $options = array())
    {     
        if(!isset($_SESSION))
        {
           return false;
        }
        
        return $_SESSION[$index] = $value;
//        return array_dot_notation_set($_SESSION,$index,$value);
    }
    
    
    
    /**
     * Merge array informazioni in session con $array
     * 
     * @param Array  $array Array da storare in sessione 
     * 
     * @return Boolean 
     */
    public function mergeSessionData(array $array)
    {
        if(!isset($_SESSION))
        {
           return false;
        }
        
        return $_SESSION = array_merge($_SESSION,$array);
    }

    /**
     * Restituisce il valore dei indice selezionato, 
     * 
     * @param String $index      l'indice può essere anche una stringa con l'annotazione java style. 
     *                           es: Application_SessionManager->getIndex("key1.key2") , ricercherà in sessione l'elemento $_SESSION[key1][key2]
     * 
     * @param Mixed  $default    Valore restituito come default in caso di non corrispondenza dell'index ricercato, default FALSE
     * 
     * @return Mixed Value or FALSE se valore inesistente
     */
    public function getIndex($index,$default = false)
    {
        if(!isset($_SESSION))
        {
           return $default;
        }
                        
        $sessionData = $this->getAll();
        
        if(isset($sessionData[$index]))
        {
           return $sessionData[$index];
        }
        
        return array_dot_notation($sessionData,$index,$default);
    }

    
    public function exists($index)
    {
       return $this->getIndex($index,false) !== false ? true : false;
    }
    
    /**
     * Elimina il valore di indice $index
     * 
     * @param String $index Indice da eliminare
     * 
     * @return Boolean TRUE on success FALSE altrimenti 
     */
    public function removeIndex($index)
    {
        return $this->delIndex($index);
    }
    
    /**
     * Elimina il valore di indice $index
     * 
     * @param String $index Indice da eliminare
     * 
     * @return Boolean TRUE on success FALSE altrimenti 
     */
    public function delIndex($index)
    {
        if(isset($_SESSION[$index]))
        {
            unset($_SESSION[$index]);
            return true;
        }
        
        return false;
    }
    
    /**
     * Verifica che la sessione attuale sia attiva
     * 
     * @param String $indexTest Indice di test da verificare
     * 
     * @return boolean if indice esiste
     */
    public function isActive($indexTest='timeout')
    {
       if(isset($_SESSION[$indexTest])){
          return true;
       }
       
       return false;
    }
    
    /**
     * Pulisce i dati presenti nel $_SESSION
     * 
     * @return Boolean
     */
    public function clearData()
    {
       $_SESSION = Array();
       return true;
    }
    
    
    // FLASH DATA ********************************************************************************
    
    
    /**
     * Aggiunge Dati Flash, utilizzabili finchè non verranno richiesti
     * 
     * @param String $index  Index
     * @param Mixed  $value  Valore
     * 
     * @return boolean
     */
    public static function addFlashData($index,$value)
    {
       if(!isset($_SESSION))
       {
          return false;
       }
       
       return $_SESSION[self::FLASH_DATA_BAG_NAME][$index] = $value;
    }
    
    
    /**
     * Restituisce Dati Flash, pulendoli alla richiesta
     * 
     * @param String   $index     Index
     * @param Mixed    $default   Valore da restituire in caso di assenza del dato flash, default FALSE
     * @param Boolean  $autoclear Indica se eliminare subito il dato flash, default TRUE
     * 
     * @return boolean
     */
    public static function getFlashData($index,$default = false,$autoclear = true)
    {
       if(!isset($_SESSION))
       {
          return $default;
       }
              
       if(isset($_SESSION[self::FLASH_DATA_BAG_NAME][$index]))
       {
          $value = $_SESSION[self::FLASH_DATA_BAG_NAME][$index];
          
          if($autoclear)
          {
             unset($_SESSION[self::FLASH_DATA_BAG_NAME][$index]);
          }
       }
       else
       {
          $value = $default;
       }
       
       return $value;
    }
    
    
    /**
     * Pulisce tutti i dati flash
     * 
     * @return Boolean
     */
    public static function clearFlashData()
    {
       if(isset($_SESSION[self::FLASH_DATA_BAG_NAME]))
       {
          unset($_SESSION[self::FLASH_DATA_BAG_NAME]);
          return true;
       }
       
       return false;
    }
    
    /**
     * Blocca i messaggi impostati come dati flash
     * 
     * @return Boolean
     */
    public static function disableFlashMessages()
    {
       return self::addFlashData(self::FLASH_MESSAGES_DISABLE_FLAG_NAME,1);
    }
    
    /**
     * Sblocca i messaggi permettendo di poterli aggiungere in futuro
     * 
     * @return Boolean
     */
    public static function enableFlashMessages()
    {
       return self::addFlashData(self::FLASH_MESSAGES_DISABLE_FLAG_NAME,0);
    }
    
    
    /**
     * Indica se i flash messages sono abilitati
     * 
     * @return Boolean
     */
    public static function isEnableFlashMessages()
    {
       return self::getFlashData(self::FLASH_MESSAGES_DISABLE_FLAG_NAME) == 0 ? true : false;
    }
    
    
    /**
     * Aggiunge un messaggio flash di qualsiati tipo, default 'message'
     * <br>
     * <b>Attenzione! questo metodo controlla se sono abilitati i flash messages</b>
     * 
     * @param String $message Messaggio
     * @param String $type    Tipologia di messaggio, default self::FLASH_MESSAGES_MESSAGE
     * 
     * @return Boolean
     */
    public static function addFlashMessage($message,$type = self::FLASH_MESSAGES_MESSAGE)
    {
       if(self::isEnableFlashMessages())
       {
          return self::addFlashData($type,$message);
       }
       
       return false;
    }
    
    /**
     * Aggiunge un messaggio flash di warning
     * <br>
     * <b>Attenzione! questo metodo controlla se sono abilitati i flash messages</b>
     * 
     * @param String $message Messaggio
     * 
     * @return Boolean
     */
    public static function addFlashMessageWarning($message)
    {
       if(self::isEnableFlashMessages())
       {
          return self::addFlashData(self::FLASH_MESSAGES_WARNING,$message);
       }
       
       return false;
    }
    
    /**
     * Aggiunge un messaggio flash di errore
     * <br>
     * <b>Attenzione! questo metodo controlla se sono abilitati i flash messages</b>
     * 
     * @param String $message Messaggio
     * 
     * @return Boolean
     */
    public static function addFlashMessageError($message)
    {
       if(self::isEnableFlashMessages())
       {
          return self::addFlashData(self::FLASH_MESSAGES_ERROR,$message);
       }
       
       return false;
    }
    
    /**
     * Verifica che vi sia un flash Message impostato
     * 
     * @return Boolean
     */
    public static function hasFlashMessage()
    {
        return self::getFlashData(self::FLASH_MESSAGES_MESSAGE,false,false) != false  ||
               self::getFlashData(self::FLASH_MESSAGES_WARNING,false,false) != false  ||
               self::getFlashData(self::FLASH_MESSAGES_ERROR,false,false)   != false;
    }
        
    /**
     * Restituisce il messaggio flash precedentemente impostato
     * 
     * @param String $type       Tipologia di messaggio, default self::FLASH_MESSAGES_MESSAGE
     * @param Mixed  $default    Valore di default restituito, default FALSE
     * 
     * @return String
     */
    public static function getFlashMessage($type = self::FLASH_MESSAGES_MESSAGE,$default = false)
    {
       return self::getFlashData($type,$default);
    }
    
    /**
     * Restituisce il messaggio flash di warning precedentemente impostato
     * 
     * @param Mixed $default Valore di default, default FALSE
     * 
     * @return String
     */
    public static function getFlashMessageWarning($default = false)
    {
       return self::getFlashData(self::FLASH_MESSAGES_WARNING,$default);
    }
    
    /**
     * Restituisce il messaggio flash di error precedentemente impostato
     * 
     * @param Mixed $default Valore di default, default FALSE
     * 
     * @return String
     */
    public static function getFlashMessageError($default = false)
    {
       return self::getFlashData(self::FLASH_MESSAGES_ERROR,$default);
    }
    
}
