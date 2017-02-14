<?php

/**
 * Controller di base per processare le action dell'applicazione
 */
class Application_Controller extends Abstract_ActionMagicMethods implements \Interface_Controllers
{
    use Trait_Singleton,Trait_ObjectUtilities;
    
    use Trait_ApplicationKernel,
            
        Trait_ApplicationHooks,
            
        Trait_ApplicationLanguages,
            
        Trait_ApplicationPlugins,
            
        Trait_ApplicationRouting,
            
        Trait_ApplicationServices,
            
        Trait_ApplicationTemplating,
            
        Trait_ApplicationConfigs,
            
        Trait_ApplicationHttpRequest;
    
        
    /**
     * ROOT PATH del progetto, percorso fisico directory 
     * 
     * @var String
     */
    protected  $_root_path    = ROOT_PATH;
    
    
    /**
     * Action Processata
     * 
     * @var String
     *
     */
    protected  $_action       = null;
    
    
    /**
     * Method Processata
     * 
     * @var String
     *
     */
    protected  $_method    = null;
    
    
    /**
     * Tipologia di Action Processata
     * 
     * @var String 
     */
    protected  $_action_type  = null;
    
    
    
    /**
     * Array Object da passare all'actionObject 
     * @var Application_ArrayBag
     */
    protected  $_action_parameters = null;
    
    
    /**
     * Indica se gli hooks sono abilitati all'interno dei controller
     * 
     * @var Boolean
     */
    protected $_hooks_enable       = false;
    
    
    /**
     * Lista degli hooks disabilitati temporaneamente
     * 
     * @var Array
     */
    protected $_hooks_disabled_list = Array();
    
    /**
     * Impsta l'action da processare
     * 
     * @param String $action Action da lavorare
     * 
     * @return Boolean
     */
    public function setAction($action)
    {
       $this->_action = $action;
       return $this;
    }
    
    /**
     * Impsta il tipo di action da processare
     * 
     * @param String $action Tipo di action da lavorare
     * 
     * @return Boolean
     */
    public function setActionType($actionType)
    {
       $this->_action_type = $actionType;
       return $this;
    }
    
    
    
    /**
     * Crea l'istanza del controller Index principale
     * 
     * @return Boolean
     */
    public function __construct()
    {
        $this->_hooks_disabled_list = array();   
        return true;
    }

    
    public function  __destruct()
    {
        unset($this);
        return true;
    }
    
    
    /**
     * Forza l'applicazione a generare una pagina con il codice di errore HTTP specificato
     * 
     * @param Int          $httpError HTTP error nr
     * @param ArrayObject  $params    Parametri utili per eventuali elaborazioni interne
     * 
     * @throws Exception   Eccezione o classe figlia
     * 
     */
    public final static function triggerHttpError($httpError,ArrayObject $params = null)
    {
       
       switch($httpError)
       {
          case self::HTTP_ERROR_REDIRECT: 
                                             $url   = $params->offsetExists('url')   ? $params->offsetGet('url') : false;
                                             
                                             if(!$url)
                                             {
                                                return self::throwNewException(32420347298374298347,'Impossibile effettuare il redirect, parametro url non fornito');
                                             }
                                          
                                             return self::throwNewExceptionRedirect($url);
                                                                                    
                                          break;
                                          
                                          
          default:                           return self::throwNewExceptionHttpStatus($httpError);
             
                                          break;
       }
       
       
    }

   
    /**
     * Restituisce una response valida da fornire al kernel
     * 
     * @param String $responseContent  Contenuto risposta in formato stringa
     * @param array  $headers          [OPZIONALE] Headers, default NULL, Application_ControllerResponseData::$_HEADERS_DEFAULT
     * 
     * @return Application_ControllerResponseData
     */
    public function generateControllerResponse($responseContent,array $headers = null)
    {
       if(is_null($headers))
       {
          $headers = Application_ControllerResponseData::$_HEADERS_DEFAULT;
       }
       
       return new Application_ControllerResponseData($responseContent, $headers);
    }
    
    /**
     * Effettua il reder di un template, restituendo una response valida al kernel
     * 
     * @param Mixed                               $templates   Path del template, lista di template
     * @param Array                               $parameters  [OPZIONALE] parametri da passare
     * @param String                              $package     [OPZIONALE] package dove ricercare la vista, default '' (quello usato dalla rotta)
     * @param \Application_ControllerResponseData $response    [OPZIONALE] Controller Response base da unire, default NULL
     * 
     * @return \Application_ControllerResponseData
     */
    public function render($templates,array $parameters = array(),$package = '',\Application_ControllerResponseData $response = null)
    {        
       $responseContent = $this->getService('templating')->renderView($templates,$parameters,$package);
       $headers         = $response ? $response->getHeaders() : null;
               
       return $this->generateControllerResponse($responseContent, $headers);
    }
    
    
    /**
     * Redirect ad una rotta specifica
     * 
     * @param String  $routeName    nome della rotta
     * @param Array   $params       parametri da passare per generare la rotta
     * @param Boolean $absolute     indica se assoluto, default false
     * 
     * @return void
     */
    protected function redirectToRoute($routeName,$params = array(), $absolute = false)
    {
        $url = $this->getService('routing')->generateUrl($routeName,$params,$absolute);
        
        return self::redirect($url);
    }
        
   /**
    * [ALIAS]
    * 
    * Lancia un exception di redirect verso il kernel
    * 
    * @param String $url Redirect Url
    * 
    * @return Boolean
    * 
    */
    public static function redirect($url)
    {
       return self::throwNewExceptionRedirect($url);
    }
}