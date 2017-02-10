<?php

require dirname(__FILE__) . "/Exception_PortalErrorException.class.php";

/**
 * Classe che gestisce le eccezioni, le lancia e ne crea i log
 */
class Exception_ExceptionHandler implements Interface_ExceptionThrowers
{
    use Trait_Singleton,Trait_ObjectUtilities;

    /**
     * Classe che gestisce le eccezioni, le lancia e ne crea i log
     * 
     * @return Boolean
     */
    public function  __construct()
    {
       return true;
    }

    
    public function  __destruct()
    {
        unset($this);
        return true;
    }

    /**
     * Restiuisce il path del template di exception da utilizzare. 
     * <b>Il file è .php</b>
     * 
     * @return String path
     */
    public static function getTplPath($errorCode = null)
    {
        
       if(empty($errorCode))
       {
           $errorCode = 404;
       }
       
       
       $tplViewPathGeneric      = EXCEPTION_ERROR_PAGE;
       $tplViewPathGenericCode  = EXCEPTION_ERROR_VIEWS_PATH. DIRECTORY_SEPARATOR . $errorCode . '.php';

       try
       {
          $kernel     = ApplicationKernel::getInstance();
          $appRouting = $kernel->getApplicationRouting();
       
          if($appRouting)
          {
             $routingData  = $appRouting->getApplicationRoutingData(); //Ricerco i dati del routing attualmente processati

             if($routingData)
             {
                $package  = $routingData->getPackage();
                $configsExists = $kernel->has("%APPLICATION_TEMPLATING_PACKAGE_DIRECTORY_NAME") && $kernel->has("%APPLICATION_TEMPLATING_TPL_DIR_ERROR");

                if($configsExists)
                {
                   $packageInstance      = $kernel->get('+'.$package,array(),false);
                   
                   if($packageInstance)
                   {
                       $packageErrorPathGeneric   = $packageInstance->getViewsErrorPath('error');
                       $packageErrorPathCode      = $packageInstance->getViewsErrorPath($errorCode);
                      
                       /**
                        * Verifico che il package abbia il file di errore specifico per il code specificato
                        */
                       if(file_exists($packageErrorPathCode))
                       {
                          return $packageErrorPathCode;
                       }
                       /**
                        * Verifico che il package abbia il file di errore generico per tutti gli errori
                        */
                       else if(file_exists($packageErrorPathGeneric))
                       {
                          return $packageErrorPathGeneric;
                       }             
                   }
                }
             }
          }
       }
       catch(\Exception $e)
       {
          
       }
       
       /**
        * Verifico che esista il file generico degli errori
        */
       if(file_exists($tplViewPathGenericCode))
       {
          return $tplViewPathGenericCode;
       }
       
       return $tplViewPathGeneric;
    }
    
    /**
     * Lancia un eccezione catturabile con la quale mostare errori a video all'utente
     * 
     * @param Mixed  $code                          Codice errore univoco o HTTP Statu
     * @param String $message                       Messaggio di errore
     * @param String $type                          Provenienza exception, un valore delle costanti TYPE_*, default TYPE_EXCEPTION
     * @param Mixed  $exceptionClassNameOrInstance  Instanza di \Exception o nome classe da lanciare come eccezione, default 'Exception_PortalErrorException'
     * @param String $classLateStaticBinding        Nome della classe dalla quale l'eccezione è stata lanciata, default NULL (autoelaborata)

     * @throws Exception lancia un eccezione instanza di \Exception o di una classe figlia
     */
    public static function throwNewException($code,$message,$type = self::TYPE_EXCEPTION,$exceptionClassNameOrInstance = self::DEFAULT_EXCEPTION_CLASS_NAME,$classLateStaticBinding = null)
    {
       $calledClass = is_null($classLateStaticBinding) ? get_called_class() : $classLateStaticBinding;

       $errorMessage = "";
       $errorCode    = $code;
       
       switch($type)
       {
           case self::TYPE_EXCEPTION:
              
                                        $errorMessage = "[PHP EXCEPTION]:  Class ". $calledClass .", ".$message;
                                        $logMessage   = "[".date("d/m/Y H:i:s")."] [EXCEPTION] ".trim($errorMessage)."\n";

                                      break;
                                   
           case  self::TYPE_ERROR:
              
                                        $errorMessage = "[PHP Fatal Error]:  Class ". $calledClass .", ".$message;
                                        $logMessage   = "[".date("d/m/Y H:i:s")."] [PHP FATAL ERROR] ".trim($message)."\n";

                                      break;
                                   
           case  self::TYPE_HTTP_ERROR:
              
                                        $errorMessage = "[HTTP Error]: ". $calledClass  .", ".$message."</h3>";
                                        $logMessage   = "[".date("d/m/Y H:i:s")."] [HTTP] ".trim($message)."\n";

                                      break;
           default:
                                        $errorMessage = "[UNKNOW Error]: ".$message;
                                        $logMessage   = "[".date("d/m/Y H:i:s")."] [UNKNOW] ".trim($message)."\n";

                                      break;

       }
                     
       $exceptionInstance = false;
//       echo $errorMessage;die();
       
       if(is_string($exceptionClassNameOrInstance) && class_exists($exceptionClassNameOrInstance))
       {
          $exceptionInstance =  new $exceptionClassNameOrInstance($errorMessage, $errorCode);
       }
       else if($exceptionClassNameOrInstance instanceof Exception) 
       {
          $exceptionInstance = $exceptionClassNameOrInstance;
          if($exceptionInstance instanceof Exception_PortalErrorException)
          {
               $exceptionInstance->setMessage($errorMessage)
                                 ->setCode($errorCode);
          }
          else
          {
               $exceptionInstance->__construct($errorMessage);
          }
          
       }
       
       if(!$exceptionInstance)
       {
         $exceptionInstance  = new \Exception($errorMessage,$errorCode);
       }
       
       self::writeLog($logMessage,$type);
       
       throw $exceptionInstance;
       
       return false;
    }
}
