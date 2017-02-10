<?php

/**
 * Eccezione da lanciare in caso si voglia effettuare il redirect
 */
class Exception_RedirectException extends Exception_PortalErrorException
{
    
    /**
     * Url Redirect
     * @var String
     */
    private $_url          = null;
    
    /**
     * Eccezione da lanciare in caso si voglia effettuare il redirect
     * 
     * @param String     $mess       Messaggio
     * @param Int        $code       Codice errore
     * @param String     $url        Url di redirect
     * 
     * @return Boolean
     */
    public function  __construct($message , $code , $url = null)
    {       
       parent::__construct($message, $code);
       
       if(strlen($url) > 0 ){
         $this->setUrl($url);
       }
       
       return $this;
    }

    
    public function  __destruct()
    {
        unset($this);
        return true;
    }

    /**
     * Restituisce l'url per il redirect
     * 
     * @return String
     */
    public function getUrl(){
       return $this->_url;
    }
    
    
    /**
     * Imposta l'url per il redirect
     * 
     * @param String Url di redirect
     * 
     * @return Exception_RedirectException
     */
    public function setUrl($url)
    {
       $this->_url = $url;
       return $this;
    }
    
    
    public function __toString()
    {
        return 'Redirect to '.$this->getUrl().'... <meta http-equiv="refresh" content="2; url='.$this->getUrl().'">';
    }
}

