<?php

/**
 * Action Object invocata dagli ActionController quando si deve processare un httpStatus particolare
 */
class Basic_ActionObjectHttpStatus extends \Abstract_ActionObject
{
    /**
     * HTTP Status
     * @var Int
     */
    protected $_http_status;
    
    /**
     * Contenuto mostrato
     * @var String
     */
    protected $_content = 'Http status';
  
    /**
     * Imposta lo status HTTP
     * 
     * @param Int $httpStatus HTTP Status
     * 
     * @return \Abstract_ActionObject
     */
    public function setHttpStatus($httpStatus)
    {
        $this->_http_status = $httpStatus;
        return $this;
    }
    
    /**
     * Imposta il content
     * 
     * @param String $content Content
     * 
     * @return \Basic_ActionObjectHttpStatus
     */
    public function setContent($content)
    {
        $this->_content = $content;
        return $this;
    }
    
    
    public function getFallbackMethodName()
    {
        return 'doProcessMe';
    }
    
    public function __construct($httpStatus,$content = null)
    {
       $this->setHttpStatus($httpStatus);
       
       if(!is_null($content))
       {
          $this->setContent($content);
       }
    }
    
    /**
     * Processa la closure
     * 
     * @param \Application_ActionRequestData $requestData
     * 
     * @return \Abstract_ActionObject
     */
    public function doProcessMe(\Application_ActionRequestData $requestData)
    {
       if($this->_http_status == self::HTTP_ERROR_REDIRECT)
       {
           return $this->doRedirect($requestData->getActionParameters()->getVal('url'));
       }
       
       if($this->_http_status == self::HTTP_STATUS_OK)
       {
           return $this->setResponse(response($this->_content,array('Content-type'=>'text/html')));
       }
       
       return $this->showHttpStatus($this->_http_status, $this->_content);
    }
}
