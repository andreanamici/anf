<?php

/**
 * Classe che contiene i dati di response elaborati dal controller per il Kernel
 *
 */
class Application_ControllerResponseData
{

   /**
    * Content-type HTML
    * @return String
    */
   const DEFAULT_CONTENT_TYPE_HTML       = 'text/html, charset=utf-8';
   
   /**
    * Content-type Javascripts files
    * @return String
    */
   const DEFAULT_CONTENT_TYPE_JAVASCRIPT = 'text/javascript, charset=utf-8';
   
   
   /**
    * Content-type JSON/JSONP
    * @return String
    */
   const DEFAULT_CONTENT_TYPE_JSON       = 'application/json, charset=utf-8';
   
   
   /**
    * Dati Content response, HTML,Json,Jsonp, altri
    * 
    * @var String
    */
   protected $_content = null;
   
   
   /**
    * Headers attualmente elaborati
    * 
    * @var ArrayObject
    */
   protected $_headers = null;
   
   
   /**
    * Headers di default per la response attuale
    * 
    * @var Array
    * 
    */
   public static $_HEADERS_DEFAULT  = Array(
       'Content-type'   => self::DEFAULT_CONTENT_TYPE_HTML,
       'Cache-Control'  => 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0',
       'Expires'        => 'Thu, 19 Nov 1981 08:52:00 GMT',
       'Pragma'         => 'no-cache'
   );
   
   
   /**
    * Classe che gestisce i dati passati dall'actionObject quando viene processato correttamente
    * 
    * @param Mixed   $content       Contenuto pagina / status code
    * @param Array   $headers       Headers, default array()
    * @param Int     $statusCode    Codice status, default \Interface_HttpStatus::HTTP_STATUS_OK
    * 
    * @return Boolean
    */
   public function __construct($content,array $headers = array(),$statusCode = null) 
   {      
      $this->setContent($content);
      
      $headers = is_array($headers) && count($headers) > 0 ? $headers : static::$_HEADERS_DEFAULT;
      
      if(is_numeric($content) && $content > 0)
      {
         $this->setContent('');
         $headers = array($statusCode => null) + array('Status' => $content) + $headers;
      }
      else if($statusCode > 0)
      {
         $headers = array($statusCode => null) + array('Status' => $statusCode) + $headers;
      }
      
      $this->setHeaders($headers);
      
      return true;
   }

   /**
    * Imposta il content da mostrare
    * 
    * @param String $content Contenuto response
    * 
    * @return Application_ResponseData
    */
   public function setContent($content) 
   {
      $this->_content = $content;
      return $this;
   }
   
   
   /**
    * Restituisce il contento da mostrare a video
    * 
    * @return String
    */
   public function getContent() 
   {
      return $this->_content;
   }
   
   
   /**
    * Imposta i dati headers
    * 
    * @param array $headers
    * 
    * @return Application_ControllerResponseData
    */
   public function setHeaders(array $headers)
   {
      $this->_headers = new ArrayObject($headers);
      return $this;
   }
   
   
   /**
    * Sovascrive i dati headers attuali
    * 
    * @param String $key    Chiave header
    * @param Mixed  $value  Valore header
    * 
    * @return Application_ControllerResponseData 
    */
   public function replaceHeader($key,$value)
   {
      if(is_array($key))
      {
          return $this->replaceHeaders($key);
      }
      
      $this->_headers->offsetSet($key, $value);
      return $this;
   }
   
   /**
    * Sovascrive i dati headers attuali
    * 
    * @param Array $headers    $headers headers attuali
    * 
    * @return Application_ControllerResponseData 
    */
   public function replaceHeaders(array $headers)
   {
      $currentHeaders = $this->_headers->getArrayCopy();
      $this->_headers = new ArrayObject(array_merge($currentHeaders,$headers));
      return $this;
   }
   
   
   /**
    * Restituisce gli headers da includere nel Kernel
    * 
    * @return ArrayObject
    */
   public function getHeaders()
   {
      return $this->_headers;
   }
   
   /**
    * Restituisce il content-type della response
    * 
    * @return String
    */
   public function getContentType()
   {
       return $this->_headers->offsetExists('Content-type') ? $this->_headers->offsetGet('Content-type') : self::$_HEADERS_DEFAULT['Content-type'];
   }
   
   /**
    * Restituisce lo status Code
    * 
    * return Int
    */
   public function getStatusCode()
   {
      return $this->_headers->offsetExists('Status')  ? $this->_headers->offsetGet('Status') : \Interface_HttpStatus::HTTP_STATUS_OK;
   }
   
   /**
    * Verifica che uno dei parametri presenti nell'attuale headers match la condizione 
    * 
    * @param String  $headerParam  Valore Header
    * @param RegExpr $pattern      RegolarExpression pattern
    * 
    * @return boolean
    */
   public function headerMatch($headerParam, $pattern)
   {
      if($this->_headers->offsetExists($headerParam))
      {
         $headerValue = $this->_headers->offsetGet($headerParam);
         if(preg_match($pattern,$headerValue))
         {
            return true;
         }
      }
      
      return false;
   }
   
   /**
    * Effettua il merge della response
    * 
    * @param \Application_ControllerResponseData $response Response
    * 
    * @return \Application_ControllerResponseData
    */
   public function merge(\Application_ControllerResponseData $response)
   {
       if($this->getContentType() != $response->getContentType())
       {
          throw new \Exception('Non è possibile effettuare il merge delle response poichè il content-type non coincide', 874598345734895);
       }
       
       $headers = $response->getHeaders();
       $content = $response->getContent();
       
       $mergeContent = $this->getContent().''.$content;
       $mergeHeaders = array_merge($this->getHeaders()->getArrayCopy(),$headers->getArrayCopy());
       
       $this->__construct($mergeContent,$mergeHeaders);
       return $this;
   }
   
   /**
    * Restituisce la response in formato string
    * @return String
    */
   public function __toString()
   {
      return (string) $this->getContent();
   }
}
