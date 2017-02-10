<?php

/**
 * Questa classe si occupa di bilanciare gli indirizzi web per le risorse statiche fornite
 */
final class Portal_StaticContentBalance
{
   
   /**
    * Questa classe si occupa di bilanciare gli indirizzi web per le risorse statiche fornite
    */
    public function  __construct() {
        return true;
    }

    public function  __destruct() {
        unset($this);
        return true;
    }

    /**
     * Carica un javascript nel DOM
     * 
     * @param String  $jsPath       Percorso assoluto/relativo del Javascript
     * @param Boolean $afterDomLoad Determina se caricare lo script dopo o durante il rendering della pagina web.
     * 
     * @return String Javascript string
     */
    public static function LoadJS($jsPath,$afterDomLoad=true,$removeScriptAfterLoad='true')
    {
        $jsRandId = rand(1,time());
        
        if($afterDomLoad)   return " <script type=\"text/javascript\" id=\"js_load_script_{$jsRandId}\">$(document).ready(function(){ if(javascript_load(\"{$jsPath}\") && {$removeScriptAfterLoad}) $(\"#js_load_script_{$jsRandId}\").remove();});</script>";

        return " <script type=\"text/javascript\" id=\"js_load_script_{$jsRandId}\"> if(javascript_load(\"{$jsPath}\") && {$removeScriptAfterLoad}){ $(\"#js_load_script_{$jsRandId}\").remove();}</script>";
    }

    /**
     * Mostra Loader di Attesa per pagine che richiedono particolari elaborazioni. Al termine chiude il div di preload ed elimina il nodo script
     * 
     * generato per il preload
     * 
     * @return String
     */
    public static function LoadingWaitJS()
    {
        return "<script type=\"text/javascript\" id=\"js_load_wait\">init_load();$(document).ready(function(){ destruct_load(); $(\"#js_load_wait\").remove(); });</script>";
    }
    
    /**
     * Restituisce L'url web del file Javascript
     * 
     * @param String $js_filename Nome file
     * 
     * @return String Url
     */
    public static function getJSPath($js_filename)
    {
        return STATIC_URL_JAVASCRIPT."/".self::adjustFileName($js_filename);
    }

    /**
     * Restituisce L'url web del file CSS
     * 
     * @param String $js_filename Nome file
     * 
     * @return String Url
     */
    public static function getCssPath($css_filename)
    {
        return STATIC_URL_CSS."/".self::adjustFileName($css_filename);
    }

    /**
     * Restituisce L'url web di una Immagine
     * 
     * @param String $js_filename Nome file
     * 
     * @return String Url
     */
    public static function getImagePath($img_filename)
    {
        return STATIC_URL_IMAGES."/".self::adjustFileName($img_filename);
    }
    
    
    /**
     * Restituisce L'url web di una foto
     * 
     * @param String $pic Nome file
     * 
     * @return String Url
     */
    public static function getPicturesPath($pic)
    {
        return STATIC_URL_PICTURES."/".self::adjustFileName($pic);
    }

    
    /**
     * Restituisce L'url web del file Flash
     * 
     * @param String $js_filename Nome file
     * 
     * @return String Url
     */
    public static function getFlashPath($flash_filename)
    {
        return STATIC_URL_FLASH."/".self::adjustFileName($flash_filename);;
    }
    
    
    /**
     * Restituisce L'url web del file media
     * 
     * @param String $media_file Nome file
     * 
     * @return String Url
     */
    public static function getMediaPath($media_file)
    {
        return STATIC_URL_MEDIA."/".self::adjustFileName($media_file);;
    }
    
    /**
     * Corregge eventuali "/" sul file name all'inizio della stringa
     * 
     * @param String $fileName
     * 
     * @return String Nome File
     */
    private static function adjustFileName($fileName)
    {
        if(substr($fileName,0,1)=="/" || substr($fileName,0,1)=="\\")
                return substr($fileName,1);
        return $fileName;
    }
    
}