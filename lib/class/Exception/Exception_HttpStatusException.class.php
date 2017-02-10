<?php

/**
 * Eccezione da lanciare per forzare un determinato status HTTP
 */
class Exception_HttpStatusException extends Exception_PortalErrorException
{   
     
    use Trait_ApplicationRouting;
    
    /**
     * Eccezione da lanciare per forzare un determinato status HTTP
     * 
     * @param String     $message    Messaggio
     * @param Int        $code       Codice errore
     * 
     * @return Boolean
     */
    public function  __construct($message,$code)
    {       
       parent:: __construct($message, $code);
       return true;
    }

    
    public function  __destruct()
    {
        unset($this);
        return true;
    }
   
    
    /**
     * [ALIAS]
     *  
     * Restituisce lo statusCode dell'eccezione (errorCode)
     * 
     * @return Int
     */
    public function getStatusCode(){
       return $this->getCode();
    }
}
