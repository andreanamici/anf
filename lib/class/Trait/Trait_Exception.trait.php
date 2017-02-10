<?php


/**
 * Trait che mette a disposizione tutti i metodi utili per lanciare eccezioni verso il Kernel
 */
trait Trait_Exception
{
       
    /**
     * Lancia un eccezione generica del portale
     * 
     * @param Int    $code,                        Codice errore univoco, Inventalo tu no?? :)
     * @param String $message                      Messaggio di errore
     * @param String $type                         Tipologia di messaggio
     * @param Mixed  $exceptionClassNameOrInstance Instanza di Exception o nome classe da lanciare come eccezione, default 'Exception_PortalErrorException'
     * @param Mixed  $classLateStaticBinding       Instanza della classe che lancia l'eccezione, default late static binding
     * 
     * @throws Exception_PortalErrorException
     */
   public static function throwNewException($errorCode,$errorMessage,$type = null,$exceptionClassNameOrInstance = null,$classLateStaticBinding = null)
   {
      if(!class_exists('Exception_ExceptionHandler',false))
      {
          throw new \Exception($errorMessage,intval($errorCode));
      }
      
      $type                             = $type ? $type :  Exception_ExceptionHandler::TYPE_ERROR;
      $exceptionClassNameOrInstance     = $exceptionClassNameOrInstance ? $exceptionClassNameOrInstance :  Exception_ExceptionHandler::DEFAULT_EXCEPTION_CLASS_NAME;
      
      $classLateStaticBinding = $classLateStaticBinding ? $classLateStaticBinding : get_called_class();
      
      if(is_object($classLateStaticBinding))
      {
          $classLateStaticBinding = get_class($classLateStaticBinding);
      }

      return Exception_ExceptionHandler::throwNewException($errorCode, $errorMessage,$type,$exceptionClassNameOrInstance,$classLateStaticBinding);
   }
   
   
   /**
    * Lancia un eccezione Redirect
    * 
    * @param String $url        Url di redirect
    * @param String $message    Messaggio per il log interno, non mostrabile a video
    * 
    * @throws Exception_RedirectException
    */
   public static function throwNewExceptionRedirect($url,$message = '')
   {
      $message               = strlen($message) > 0  ? $message : 'Redirect to '.$url;
      $exceptionInstance     = new Exception_RedirectException($message,Interface_HttpStatus::HTTP_ERROR_REDIRECT,$url);
      
      if(!class_exists('Exception_ExceptionHandler',false))
      {
          throw new \Exception($message,302);
      }
      
      return Exception_ExceptionHandler::throwNewException(Interface_HttpStatus::HTTP_ERROR_REDIRECT, $message,Exception_ExceptionHandler::TYPE_HTTP_ERROR,$exceptionInstance);
   }
   
    /**
     * Lancia un eccezione HttpStatus
     * 
     * @param Int    $statusCode                    Codice HTTP
     * @param String $message                       Messaggio, opzionale, sarò visibile solamente nel log, all'utente questo messaggio non viene mostrato.
     * @param Mixed  $exceptionClassNameOrInstance  Instanza di Exception o nome classe da lanciare come eccezione, default 'Exception_PortalErrorException'
     *  
     * @throws Exception_HttpStatusException
     */
   public static function throwNewExceptionHttpStatus($statusCode,$message = 'Http Status ')
   {
      if(!class_exists('Exception_ExceptionHandler',false))
      {
          throw new \Exception($message,intval($statusCode));
      }
      
      $classLateStaticBinding = get_called_class();
      
      $reflection              = new ReflectionClass('Exception_HttpStatusException');
      
      if(!in_array($statusCode,$reflection->getConstants()))
      {
         self::throwNewException(2176172846178264871264,'HTTP Status non valido: '.$statusCode);
      }
      
      return Exception_ExceptionHandler::throwNewException($statusCode, $message, Exception_ExceptionHandler::TYPE_HTTP_ERROR,'Exception_HttpStatusException',$classLateStaticBinding);
   }
   
    /**
     * Lancia un eccezione Page Not Found
     * 
     * @param String $message                       Messaggio, opzionale, sarò visibile solamente nel log, all'utente questo messaggio non viene mostrato.
     * @param Mixed  $exceptionClassNameOrInstance  Instanza di Exception o nome classe da lanciare come eccezione, default 'Exception_PortalErrorException'
     *  
     * @throws Exception_HttpStatusException
     */
   public static function throwNewExceptionPageNotFound($message = 'Page not found')
   {
      if(!class_exists('Exception_ExceptionHandler',false))
      {
          throw new \Exception($message,404);
      }
       
      $classLateStaticBinding = get_called_class();
      
      return Exception_ExceptionHandler::throwNewException(Interface_HttpStatus::HTTP_ERROR_PAGE_NOT_FOUND, $message,Exception_ExceptionHandler::TYPE_HTTP_ERROR,'Exception_HttpStatusException',$classLateStaticBinding);
   }
   
    /**
     * Lancia un eccezione InternalServerError
     * 
     * @param String $message                       Messaggio, opzionale, sarò visibile solamente nel log, all'utente questo messaggio non viene mostrato.
     * @param Mixed  $exceptionClassNameOrInstance  Instanza di Exception o nome classe da lanciare come eccezione, default 'Exception_PortalErrorException'
     * 
     * @throws Exception_HttpStatusException
     */
   public static function throwNewExceptionInternalServerError($message = 'Internal server Error')
   {
      if(!class_exists('Exception_ExceptionHandler',false))
      {
          throw new \Exception($message,500);
      }
      
      $classLateStaticBinding = get_called_class();
      
      return Exception_ExceptionHandler::throwNewException(Interface_HttpStatus::HTTP_ERROR_INTERNAL_SERVER_ERROR, $message,Exception_ExceptionHandler::TYPE_HTTP_ERROR,'Exception_HttpStatusException',$classLateStaticBinding);
   }
   
}

