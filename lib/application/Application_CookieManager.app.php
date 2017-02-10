<?php

/**
 * Cookie Manager - Classe per la gestione dei cookie del portale
 */
class Application_CookieManager implements Interface_ArrayTraversable
{
    use Trait_Singleton,
            
        Trait_Exception;
    
    /**
     * @var String Path disponibilità cookie
     */
    public static $_COOKIE_PATH_SPACE_DEFAULT   = COOKIE_PATH_SPACE;
    
    /**
     * @var String Dominio disponibità cookie
     */
    public static $_COOKIE_DOMAIN_DEFAULT       = COOKIE_DOMAIN;
    
    /**
     * @var Boolean Indica se il cookie è disponibile solamente in https.
     */
    public static $_COOKIE_SECURE_DEFAULT       = COOKIE_SECURE;
    
    /**
     * @var Boolean Indica se il cookie sarà disponibile solamente lato server e non accessibile via javascript
     */
    public static $_COOKIE_HTTPONLY_DEFAULT     = COOKIE_HTTP_ONLY;
    
    /**
     * @var String Indica il prefix da applicare al cookie
     */ 
    public static $_COOKIE_PREFIX               = COOKIE_PREFIX;
    
    /**
     * Timelife cookie attualmente configurato
     * @var Int
     */
    private $_cookie_lifetime   = COOKIE_LIFETIME;
    
    /**
     * Path di accesso del cookie attuale
     * @var String
     */
    private $_cookie_path       = COOKIE_PATH_SPACE;
    
    /**
     * Dominio di disponibilità del cookie attualmente in uso
     * @var String
     */
    private $_cookie_domain     = COOKIE_DOMAIN;
    
    /**
     * Indica se i cookies sono disponibili solamente in https
     * @var Boolean
     */
    private $_cookie_secure     = COOKIE_SECURE;
    
    /**
     * Indica se i cookies sono disponibili solamente in http e nn da altri linguaggi
     * @var Boolean
     */
    private $_cookie_httponly   = COOKIE_HTTP_ONLY;
    
    
    
    /**
     * @author Andrea Namici Marzo 2012
     * 
     * Cookie Manager - Classe per la gestione dei cookie del portale
     * <br>
     * <b>In Sviluppo, viene settato a NULL $_COOKIE_DOMAIN_DEFAULT per evitare che su hercules non possa settare il domain al cookie.</b>
     * 
     * @return Boolen
     */
    public function __construct()
    {
        $this->initCookieInfo(static::$_COOKIE_PATH_SPACE_DEFAULT,static::$_COOKIE_DOMAIN_DEFAULT,static::$_COOKIE_SECURE_DEFAULT,static::$_COOKIE_HTTPONLY_DEFAULT);
        
        return true;
    }
    
    
    /**
     * Inizializza tutte le informazioni per gestire i cookie all'interno anche di sottodomini, su pathspace diversi, tramite https o meno
     * 
     * @param String   $path        Path di validità del cookie
     * @param String   $domain      Dominio di validità
     * @param Boolean  $secure      Indica se il cookie è disponibile solamente in HTTPS
     * @param Boolean  $httponly    Indica che il cookie sarà accessibile solamente tramite HTTP e non da altri linguaggi, es Javascript
     * 
     * @return CookieManager        Instanza Oggetto
     * 
     */
    public function initCookieInfo($path,$domain,$secure ,$httponly)
    {
        $this->_cookie_path     = $path;
        $this->_cookie_domain   = $domain;
        $this->_cookie_secure   = $secure;
        $this->_cookie_httponly = $httponly;
        
        return $this;
    }
    
    /**
     * Setta un cookie nel browser del client.
     * 
     * <b>NB: se l'header è già partito questo metodo restituirà sempre FALSE!</b>
     * 
     * @param String  $name         Nome del cookie
     * @param String  $value        Valore del cookie
     * @param Array   $options      Opzioni da passare per gestire l'expire, il path, il domain etc..
     * 
     * @return Boolean Restituisce TRUE se settato correttamente, FALSE altrimenti
     */
    public function addIndex($name,$value,array $options = array())
    {
       $time = 0;
       
       $expire   = isset($options['expire'])   ? $options['expire']    : $this->_cookie_lifetime;
       $path     = isset($options['path'])     ? $options['path']      : $this->_cookie_path;
       $domain   = isset($options['domain'])   ? $options['domain']    : $this->_cookie_domain;
       $secure   = isset($options['secure'])   ? $options['secure']    : $this->_cookie_secure;
       $httponly = isset($options['httponly']) ? $options['httponly']  : $this->_cookie_httponly;
               
       if($expire>0)
       {
          $time = time() + $expire;
       }
       
       $ob = ini_get('output_buffering'); 
       
       if (headers_sent() && (bool) $ob === false || strtolower($ob) == 'off' ) 
       {
          return false; 
       }
       
       $name = self::$_COOKIE_PREFIX.$name;
       
       if(setcookie($name,$value, $time,$path,$domain,$secure,$httponly)!==false)
       {
           $_COOKIE[$name] = $value;
           return true;
       }
       
       return false;
    }
    
    /**
     * Restituisce un Cookie
     * 
     * @param String $name Nome del Cookie
     * 
     * @return Mixed cookie or FALSE
     */
    public function getIndex($name)
    {
        $name = self::$_COOKIE_PREFIX.$name;
        
        if($this->exists($name))
        {
            return $_COOKIE[$name];
        }
        
        return false;
    }
    
    /**
     * Controlla l'esistenza di un Cookie
     * 
     * @param String $name Nome del cookie
     * 
     * @return Boolean Restituisce TRUE se il cookie esiste, FALSE altrimenti
     */
    public function exists($name)
    {
       $name = self::$_COOKIE_PREFIX.$name;
       return isset($_COOKIE[$name]);
    }
    
    
    /**
     * Rimuove un cookie
     * 
     * @param String $index Nome
     * 
     * @return Boolean
     */
    public function removeIndex($index)
    {
        return $this->destroyCookies($index);
    }
    
    
    public function getAll()
    {
        return $_COOKIE;
    }
    
    
    public function exchangeArray(array $data)
    {
        $_COOKIE = $data;
        return $this;
    }
    
    
    /**
     * Elimina un Cookie
     * 
     * @param String $name    Nome del cookie, se NULL cancella tutti i cookie del sito
     * @param Array  $save    Array contenente i nomi dei cookie da lasciare attivi
     * 
     * @return Boolean
     */
    public function destroyCookies($name = null, $save = Array())
    {
        $name = self::$_COOKIE_PREFIX.$name;
        if(strlen($name)>0)
        {
           if(isset($_COOKIE[$name]))
           {
               if(setcookie($name,"",1,$this->_cookie_path,$this->_cookie_domain,$this->_cookie_secure,$this->_cookie_httponly)!==false){
                   unset($_COOKIE[$name]);
               }
               
               return true;
           }
        }
        else
        {
           if(count($_COOKIE)>0)
           {
              foreach($_COOKIE as $cookieName=>$value)
              {
                  if(!in_array($cookieName,$save))
                  {
                     if(setcookie($cookieName,"",1,$this->_cookie_path,$this->_cookie_domain,$this->_cookie_secure,$this->_cookie_httponly)!==false){
                        unset($_COOKIE[$cookieName]);
                     }
                  }
              }
              
              return true;
           }
        }
        
        return false;
    }
}

