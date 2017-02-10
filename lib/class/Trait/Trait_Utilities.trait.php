<?php

/**
 * Trait per la gestione delle classi Utility dell'applicazione
 */
trait Trait_Utilities
{    
    /**
     * Restituisce un instanza dell'utilityCommonFunctions Class
     * 
     * @return Utility_CommonFunction
     */
    protected static function getUtility()
    {            
       return \ApplicationKernel::getInstance()->get('@utility');
    }
    
    
    /**
     * Restituisce un instanza dell'utility Upload manager
     * 
     * @return Utility_Upload
     */
    protected static function getUtilityUpload()
    {
       return \ApplicationKernel::getInstance()->get('@utility.upload');
    }
}