<?php

/**
 * Questo Trati si occupa di fornire i metodi utili per accedere al logger dell'applicazione
 */
trait Trait_ApplicationLogsManager
{
    /**
     * Restituisce il manager dei file di logs
     * 
     * @return Application_LogWriter
     */
    protected static function getLogsManager()
    {
        return \Application_LogWriter::getInstance();
    }
    
       
    /**
     * Scrive un messaggio sui file di log
     * 
     * @param String $message    Messaggio da scrivere, ad ogni messaggio sarà preappeso data e ora
     * @param String $type       Tipologia file di log da scrivere, default Application_LogWriter::DEFAULT_LOG_TYPE
     * @param String $mode       Mode scrittura file, default "a+"
     * 
     * @throws Exception_PortalErrorException
     */
    protected static function writeLog($message, $type = null, $mode = null,$newLineReplace=true)
    {            
       try
       {
            $logsManager = self::getLogsManager();/*@var $logsManager Application_LogWriter*/

            if($logsManager)
            {
                 $type = $type ? $type : \Application_LogWriter::DEFAULT_LOG_TYPE;
                 $mode = $mode ? $mode : \Application_LogWriter::FILE_APPEND_CREATE;
                
                 $logsManager->addLogsType($type);
                 
                 if($newLineReplace)
                 {
                   $message = preg_replace("/\n/","",$message);
                 }
                 
                 $logMessage = "\n[". date("Y-m-d H:i:s") ."]  ".(!empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknow')." ".$message;

                 return  $logsManager->setType($type)->write($logMessage,$mode);
            }
       }
       catch (\Exception $e)
       {         
           return false;
       }
       
       return false;
    } 
}






















