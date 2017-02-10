<?php

/**
 * Hook per la gestione della sessione, sfrutta memcached
 */
class Hooks_SessionHandlerMemcached extends \Abstract_Hooks implements Interface_SessionHandler
{
 
    /**
     * Memcached instance
     * 
     * @var Cache_Memcached
     */
    private $memcached;
    
    /**
     * Instanza manager sessione
     * 
     * @var Application_SessionManager
     */
    private $sessionManager;
 
    /**
     * Questo hook registra delle callback user-level per la gestione della sessione, 
     * modificando di fatto il modo in cui l'applicazione gestisce la session
     */
    public function __construct()
    {   
        $this->initMe(self::HOOK_TYPE_SESSION_REGISTER);
    }
    
    public function isRegistrable() 
    {
        return false;
    }
    
    public function doProcessMe(\Application_HooksData $hookData)
    {        
        $session                = $hookData->getData(); /*@var $session \Application_SessionManager*/
        $cacheManager           = $hookData->getKernel()->get('cache');
        
        $this->memcached        = $cacheManager->generateCacheEngine('memcached');
        $this->sessionManager   = $session;

        $session->registerHandler($this,true); 
    }
    
    
    public function open($save_path, $name)
    {
        return true;
    }
    
    public function close()
    {
        return true;
    }
    
    public function destroy($session_id)
    {
        return $this->memcached->delete($session_id);
    }
    
    public function write($session_id, $session_data)
    {
        return $this->memcached->store($session_id, $session_data, $this->sessionManager->getStaticProperty('session_cookie_lifetime'));
    }    
    
    public function read($session_id)
    {
        return $this->memcached->fetch($session_id) ?: '';
    }
    
    public function gc($maxlifetime)
    {
        return true;
    }
}