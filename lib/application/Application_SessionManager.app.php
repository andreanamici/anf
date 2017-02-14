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
    protected $_flash_data_bag_name     = '_flashdata';
    
    /**
     * Session Handler nativo, dal php.ini
     * @var String
     */
    protected $_session_handler_native_name             = SESSION_SAVE_HANDLER;
    
    protected $_session_name                    = SESSION_NAME;
    protected $_session_cookie_path             = SESSION_COOKIE_PATH;
    protected $_session_cookie_domain           = SESSION_COOKIE_DOMAIN;
    protected $_session_use_cookies             = SESSION_USE_COOKIES;
    protected $_session_use_only_cookies        = SESSION_USE_ONLY_COOKIES;
    protected $_session_cookie_secure           = SESSION_COOKIE_SECURE;
    protected $_session_cookie_lifetime         = SESSION_COOKIE_LIFETIME;
    protected $_session_cookie_httponly         = SESSION_COOKIE_HTTPONLY;
    protected $_session_gc_maxlifetime          = SESSION_GC_MAXLIFETIME;
    protected $_session_gc_probability          = SESSION_GC_PROBABILITY;
    protected $_session_gc_division             = SESSION_GC_DIVISION;
    protected $_session_save_path               = SESSION_SAVE_PATH;
    protected $_session_save_handler            = SESSION_SAVE_HANDLER;
    protected $_session_cache_expire            = SESSION_CACHE_EXPIRE;  
    protected $_session_autostart               = SESSION_AUTOSTART;
    protected $_session_upload_progress_enabled = SESSION_UPLOAD_PROGRESS_ENABLED;
    protected $_session_upload_progress_freq    = SESSION_UPLOAD_PROGRESS_FREQ;
    protected $_session_upload_progress_minfreq = SESSION_UPLOAD_PROGRESS_MIN_FREQ;
    protected $_session_upload_progress_name    = SESSION_UPLOAD_PROGRESS_NAME;
    protected $_session_upload_progress_prefix  = SESSION_UPLOAD_PROGRESS_PREFIX;
    protected $_session_upload_progress_cleanup = SESSION_UPLOAD_PROGRESS_CLEANUP;
    
    /**
     * Handler user-level sessione
     * 
     * @var mixed
     */
    protected $_session_handler;
    
    /**
     * Elenco tipologie flash messages
     * 
     * @var array
     */
    protected $_flash_messages_types = array(
            'success' => 'message.success',
            'warning' => 'message.warning',
            'error'   => 'message.error',
            'notify'  => 'message.notify'
    );
    
    
    /**
     * Restituisce il Nome della sessione utilizzata
     * @return String
     */
    public function getSessionName(){
        return $this->_session_name;
    }
    
    /**
     * Restituisce il numero di Secondi di durata della sessione
     * @return Int
     */
    public function getSessionCacheExpire()
    {
        return $this->_session_cache_expire;
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
        
        if($this->_session_autostart)
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
        if($sessionHandler)
        {               
            $this->_session_handler = $sessionHandler;
            
            session_write_close();
            session_set_save_handler($this->_session_handler,true);
            register_shutdown_function('session_write_close');
            
            if($restartSession)
            {
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
           $this->addIndex("timeout",time()+$this->_session_cache_expire);
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
            "session.name"                     => $this->_session_name,
            "session.cookie_path"              => $this->_session_cookie_path,
            "session.cookie_domain"            => $this->_session_cookie_domain,
            "session.use_cookies"              => $this->_session_use_cookies,
            "session.use_only_cookies"         => $this->_session_use_only_cookies,
            "session.cookie_lifetime"          => $this->_session_cookie_lifetime,
            "session.cookie_httponly"          => $this->_session_cookie_httponly,
            "session.cookie_secure"            => $this->_session_cookie_secure,
            "session.gc_maxlifetime"           => $this->_session_gc_maxlifetime,     
            "session.gc_division"              => $this->_session_gc_division,
            "session.gc_probability"           => $this->_session_gc_probability,
            "session.save_handler"             => $this->_session_save_handler,
            "session.save_path"                => $this->_session_save_path,
            "session.cache_expire"             => time()+$this->_session_cache_expire,
            "session.auto_start"               => $this->_session_autostart,
            "session.upload_progress.enabled"  => $this->_session_upload_progress_enabled,
            "session.upload_progress.prefix"   => $this->_session_upload_progress_prefix,
            "session.upload_progress.freq"     => $this->_session_upload_progress_freq,
            "session.upload_progress.min_freq" => $this->_session_upload_progress_minfreq,
            "session.upload_progress.name"     => $this->_session_upload_progress_name,
            "session.upload_progress.cleanup"  => $this->_session_upload_progress_cleanup,
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
            if($this->_session_autostart)
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
        
        $flashData = $this->getIndex($this->_flash_data_bag_name,false);
        
        @session_destroy();
        
        $this->clearData();
        
        if($startNewSession)
        {
            if ($this->_session_use_cookies)
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
               $this->addIndex($this->_flash_data_bag_name, $flashData);
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
    public function addFlashData($index,$value)
    {
       if(!isset($_SESSION))
       {
          return false;
       }
       
       return $_SESSION[$this->_flash_data_bag_name][$index] = $value;
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
    public function getFlashData($index,$default = false,$autoclear = true)
    {
       if(!isset($_SESSION))
       {
          return $default;
       }
              
       if(isset($_SESSION[$this->_flash_data_bag_name][$index]))
       {
          $value = $_SESSION[$this->_flash_data_bag_name][$index];
          
          if($autoclear)
          {
             unset($_SESSION[$this->_flash_data_bag_name][$index]);
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
    public function clearFlashData()
    {
       if(isset($_SESSION[$this->flash_data_bag_name]))
       {
          unset($_SESSION[$this->_flash_data_bag_name]);
          return true;
       }
       
       return false;
    }
    
    /**
     * Blocca i messaggi impostati come dati flash
     * 
     * @return Boolean
     */
    public function disableFlashMessages()
    {
       return $this->addFlashData('flash_messages_enable',1);
    }
    
    /**
     * Sblocca i messaggi permettendo di poterli aggiungere in futuro
     * 
     * @return Boolean
     */
    public function enableFlashMessages()
    {
       return $this->addFlashData('flash_messages_enable',0);
    }
    
    /**
     * Indica se i flash messages sono abilitati
     * 
     * @return Boolean
     */
    public function isEnableFlashMessages()
    {
       return $this->getFlashData('flash_messages_enable') == 0 ? true : false;
    }
    
    /**
     * Restituisce il messaggio flash precedentemente impostato
     * 
     * @param String $type       Tipologia di messaggio, default 'success'
     * @param Mixed  $default    Valore di default restituito, default FALSE
     * 
     * @return String
     */
    public function getFlashMessage($type = null,$default = false)
    {
       return $this->getFlashData($this->_flash_messages_types[$type ? $type : 'success'],$default);
    }
   
    /**
     * Aggiunge un messaggio flash di qualsiati tipo, default 'message'
     * <br>
     * <b>Attenzione! questo metodo controlla se sono abilitati i flash messages</b>
     * 
     * @param String $message Messaggio
     * @param String $type    Tipologia di messaggio, default 'message.success'
     * 
     * @return Boolean
     */
    public function addFlashMessage($message,$type = 'success')
    {
       if($this->isEnableFlashMessages())
       {
          return $this->addFlashData($this->_flash_messages_types[$type],$message);
       }
       
       return false;
    }
    
    /**
     * Restituisce il messaggio flash di warning precedentemente impostato
     * 
     * @param Mixed $default Valore di default, default FALSE
     * 
     * @return String
     */
    public function getFlashMessageWarning($default = false)
    {
       return $this->getFlashMessage('warning',$default);
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
    public function addFlashMessageWarning($message)
    {
       return $this->addFlashMessage($message, 'warning');
    }
    
    /**
     * Restituisce il messaggio flash di error precedentemente impostato
     * 
     * @param Mixed $default Valore di default, default FALSE
     * 
     * @return String
     */
    public function getFlashMessageError($default = false)
    {
       return $this->getFlashMessage('error',$default);
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
    public function addFlashMessageError($message)
    {
       return $this->addFlashMessage($message, 'error');
    }
    
    /**
     * Restituisce il messaggio flash di error precedentemente impostato
     * 
     * @param Mixed $default Valore di default, default FALSE
     * 
     * @return String
     */
    public function getFlashMessageNotify($default = false)
    {
       return $this->getFlashMessage('notify',$default);
    }
     
    /**
     * Aggiunge un messaggio flash di notify
     * <br>
     * <b>Attenzione! questo metodo controlla se sono abilitati i flash messages</b>
     * 
     * @param String $message Messaggio
     * 
     * @return Boolean
     */
    public function addFlashMessageNotify($message)
    {
       return $this->addFlashMessage($message, 'notify');
    }
    
    /**
     * Verifica che vi sia un flash Message di un certo tipo impostato, o se ne ha uno di message.*
     * 
     * @return Boolean
     */
    public function hasFlashMessage($type = null)
    {
        $type = $type ? $this->_flash_messages_types[$type] : $type;
        
        if(!empty($type))
        {
            return  $this->getFlashData($type,false,false) != false;
        }
        
        $has = false;
        
        foreach($this->_flash_messages_types as $type => $flashKey)
        {
            $has = $this->getFlashData($flashKey, false, false) || $has;
        }
        
        return $has;
    }
        
   
    /**
     * Restituisce tutte le tipologie di flash messages supportati
     * 
     * @return Array
     */
    public function getAllFlashMessagesTypes()
    {
        return array_keys($this->_flash_messages_types);
    }
}
