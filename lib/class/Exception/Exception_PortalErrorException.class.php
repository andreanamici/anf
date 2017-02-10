<?php

class Exception_PortalErrorException extends RuntimeException implements Interface_HttpStatus
{    
   
    /**
     * Codice errore sovrascritto per accettare dati interi/double/string
     * @var Mixed 
     */
    protected $code;
    
   
    /**
     * Dati aggiuntivi passati a questa eccezione
     * 
     * @var Mixed
     */
    private $_data          = null;
    
    
    /**
     * Exception Personalizzata relativa a tutti gli errori scaturiti dal portale
     * 
     * @param String     $message       Messaggio
     * @param Int        $code          Codice errore
     * 
     * @return Boolean
     */
    public function  __construct($message , $code)
    {         
       $this->setMessage($message)
            ->setCode($code);
       
       return $this;
    }

    
    public function __destruct()
    {
        unset($this);
        return true;
    }

    /**
     * Restituisce il template Path dell'errore in base all'errore dell'eccezione stessa
     * @return String
     */
    public function getTplErrorPath()
    {
        return Exception_ExceptionHandler::getTplPath($this->code);
    }
    
    
    /**
     * Imposta i dati dell'eccezione
     * 
     * @param Mixed $data Data, può essere anche una callback alla quale sarà passato il controller attualmente utilizzato
     * 
     * @return Exception_PortalErrorException
     */
    public function setData($data)
    {
       $this->_data = $data;
       return $this;
    }
    
    
    /**
     * Restituisce i dati
     * 
     * @return Mixed
     */
    public function getData()
    {
       return $this->_data;
    }
    
    /**
     * Imposta il messaggio dell'eccezione
     * 
     * @param String $message
     * 
     * @return \Exception_PortalErrorException
     */
    public function setMessage($message)
    {
       $this->message = $message;
       return $this;
    }
    
    
    /**
     * Setta il codice dell'eccezione
     * <br>
     * <b> Questo metodo può lanciare un Exception qualora il code non sia valido!</b>
     * 
     * @param Int $code
     * 
     * @return \Exception_PortalErrorException
     */
    public function setCode($code)
    {
       $this->code = $code;
       return $this;
    }
    
}

