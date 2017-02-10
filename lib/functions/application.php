<?php

if(!function_exists('anf'))
{
    /**
     * Entry point dell'applicazione, permette di accedere direttamenete al Kernel o di ottenere i services registrati
     * 
     * @param String $service nome servizio / service string
     * @param Array  $params  parametri aggiuntivi
     * 
     * @return Mixed
     */
    function anf($service = null,array $params = array())
    {
       if(!$service)
       {
           return getApplicationKernel();
       }
       
       return getApplicationKernel()->get($service,$params);
    }
}

if(!function_exists('getApplicationKernel'))
{
    /**
     * Restituisce il Kernel principale dell'applicazione
     * 
     * @return \Application_Kernel
     */
    function getApplicationKernel()
    {
       return \ApplicationKernel::getInstance();
    }
}

if(!function_exists('getApplicationClassInstance'))
{
    /**
     * Restituisce l'instanza della classe specificata.
     * 
     * Tale classe sarà restituita invocando il metodo ::getInstance() sulla classe indicata.
     * 
     * Verra lanciata un eccezione qualora non sia possibile invocare la classe
     * 
     * @return Mixed
     * 
     * @throws \Exception
     */
    function getApplicationClassInstance()
    {
        return call_user_func_array(array(anf('@autoload'),'getLoadClassInstance'), func_get_args());
    }
}

if(!function_exists('getApplicationService'))
{
    /**
     * Restituisce un service dell'applicazione
     * 
     * @param String $service       Nome servizio
     * @param Array  $parameters    [OPZIONALE] Array parametri da passare al service, default Array()
     * 
     * @return Mixed
     */
    function getApplicationService($service,array $parameters = array())
    {
        return anf($service,$parameters);
    }
}

if(!function_exists('getApplicationPlugin'))
{
    /**
     * Include e/o restituisce un plugin
     * 
     * @param String  $plugin       Nome del plugin dell'applicazione
     * @param array   $options      Opzioni da passare al plugin
     * @param Boolean $includeOnly  Indica se includerlo solamente
     * 
     * @return Mixed
     */
    function getApplicationPlugin($plugin,array $options = array(),$includeOnly = false)
    {
        $appPlugin = anf('@plugin'); /*@var $appPlugin \Application_Plugins*/

        if($includeOnly)
        {
            return $appPlugin->includePlugin($plugin,$options);
        }

        return $appPlugin->getPluginInstance($plugin,$options);
    }
}

if(!function_exists('getApplicationSessionManager'))
{
    /**
     * Restituisce il gestore della sessione
     * 
     * @return Application_SessionManager
     */
    function getApplicationSessionManager()
    {
        return anf('@session');
    }
}

if(!function_exists('getApplicationCookieManager'))
{
    /**
     * Restituisce il gestore dei cookie
     * 
     * @return Application_CookieManager
     */
    function getApplicationCookieManager()
    {
       return anf('@cookie');
    }
}


if(!function_exists('set_cookie'))
{
    /**
     * Setta un cookie nel browser del client.
     * 
     * <b>NB: se l'header è già partito questo metodo restituirà sempre FALSE!</b>
     * 
     * @param String  $name         Nome del cookie
     * @param String  $value        Valore del cookie
     * @param Array   $options      Opzioni da passare per gestire l'expire, il path, il domain etc..
     * 
     * @return Boolean Restituisce TRUE se settato correttamente, FALSE altrimenti
     */
    function set_cookie($name,$value,array $options = array())
    {
        return anf('@cookie')->addIndex($name, $value, $options);
    }
}


if(!function_exists('getApplicationConfigs'))
{
    /**
     * Restituisce l'Application Configs
     * 
     * @return \Application_Configs
     */
    function getApplicationConfigs()
    {
       return getApplicationService('config');
    }
}

if(!function_exists('getConfigValue'))
{
    /**
     * Restituisce il valore di una configurazione
     * 
     * @param String $configName     Configurazione
     * @param Mixed  $defaultValue   Valore di default , default FALSE
     * 
     * @return Mixed
     */
    function getConfigValue($configName,$defaultValue = false)
    {
       return anf('@config')->getConfigsValue($configName,$defaultValue);
    }
}


if(!function_exists('render'))
{
   /**
    * Renderizza la response elaborata dall'action indicato, sfruttando il controller attualmente utilizzato
    * questa response valida per il Kernel puà essere anche printata, utile per sub-action
    * 
    * @param Mixed   $action         Action da processare, stringa, callable
    * @param Array   $params         [OPZIONALE] Parametri passati all'action
    * @param Boolean $controllerType [OPZIONALE] Indica il tipo di controller da utilizzare, default quello attualmente usato nel kernel
    * 
    * @return \Application_ControllerResponseData
    */
    function render($action,array $params = array(), $controllerType = null)
    {
        return anf('@controller')->forwardActionControllerResponse($action, $params, $controllerType);
    }
}

if(!function_exists('render_route'))
{
   /**
    * Renderizza la response elaborata dall'action indicato, sfruttando il controller attualmente utilizzato indicando una rotta registrata al routing
    * questa response valida per il Kernel puà essere anche printata, utile per sub-action
    * 
    * @param String   $route      Rotta da processare
    * @param Array    $params     [OPZIONALE] Parametri passati all'action
    * @param Boolean $controllerType [OPZIONALE] Indica il tipo di controller da utilizzare, default quello attualmente usato nel kernel
    * 
    * @return \Application_ControllerResponseDatas
    */
    function render_route($route,array $params = array(), $controllerType = null)
    {
        return anf('@controller')->forwardActionControllerResponseByRoute($route, $params,$controllerType);
    }
}
   

if(!function_exists('redirect'))
{
   /**
    * Effettua il redirect rilasciando l'header di redirect 302
    * 
    * @param String $url        url di redirect, assoluto, relativo
    * @param String $method     Redirect method	 'auto', 'location' or 'refresh'
    * @param Int    $code       HTTP Response status code
    * 
    * @throws Exception Se header già rilasciato
    * 
    * @return boolean
    */
    function redirect($url, $method = 'auto', $code = NULL)
    {
        $routing = anf('@routing');/*@var $routing \Application_Routing*/
        
        if($routing->isRouteExists($url))
        {
            return redirect_route($url,array(),true,$method,$code);
        }
        
        return $kernel->redirect($url,$method,$code);
    }
}

if(!function_exists('redirect_route'))
{
   /**
    * Effettua il redirect tramite una rotta registrata nel routing, rilasciando l'header di redirect 302
    * 
    * @param String   $routeName  nome della rotta
    * @param Array    $params     [OPZIONALE] parametri, default array()
    * @parma Boolean  $absolute   [OPZIONALE] indica se assoluto, default false
    * @param String   $method     Redirect method	 'auto', 'location' or 'refresh'
    * @param Int      $code       HTTP Response status code
    * 
    * @return boolean
    */
    function redirect_route($route,array $params = array(),$absolute = false,$method = 'auto', $code = NULL)
    {
        redirect(anf('@routing')->generateUrl($route,$params,$absolute),$method,$code);
    }
}

if(!function_exists('response'))
{
   /**
    * Restituisce una response valida da fornire al kernel
    * 
    * @param Mixed  $content Contenuto della response / Http Status
    * @param Array  $headers [OPZIONALE] http headers da rilasciare, default NULL
    * 
    * @return \Application_ControllerResponseData
    */
    function response($content,array $headers = array())
    {
        return anf('@controller')->generateControllerResponse($content, $headers);
    }
}

if(!function_exists('response_404'))
{
   /**
    * Mostra una pagina 404
    * 
    */
    function response_404()
    {
       return response(\Interface_HttpStatus::HTTP_ERROR_PAGE_NOT_FOUND);
    }
}


if(!function_exists('response_403'))
{
   /**
    * Mostra una pagina 404
    * 
    */
    function response_403()
    {
       return response(\Interface_HttpStatus::HTTP_ERROR_FORBIDDEN);
    }
}

if(!function_exists('response_500'))
{
   /**
    * Mostra una pagina 500
    * 
    */
    function response_500()
    {
       return response(\Interface_HttpStatus::HTTP_ERROR_INTERNAL_SERVER_ERROR);
    }
}

if(!function_exists('response_json'))
{
   /**
    * Restituisce una response valida da fornire al kernel
    * 
    * @param Array  $data    Dati json
    * @param Array  $headers [OPZIONALE] http headers da rilasciare, default NULL
    * 
    * @return \Application_ControllerResponseData
    */
    function response_json(array $data,array $headers = array())
    {
        $headers['Content-Type'] = 'application/javascript';
        return response(json_encode($data),$headers);
    }
}

if(!function_exists('response_route'))
{
   /**
    * Restituisce una response valida da fornire al kernel
    * 
    * @param String $route                  Rotta
    * @param Array  $routeParameters        [OPZIONALE] Parametri passati alla rotta
    * @param String $actionControllerType   [OPZIONALE] Tipologia di controller, default quello usato dal kernel
    * 
    * @return \Application_ControllerResponseData
    */
    function response_route($route,array $routeParameters = array(),$actionControllerType = null)
    {
        return anf('@controller')->forwardActionControllerResponseByRoute($route, $routeParameters,$actionControllerType);
    }
}



if(!function_exists('cdefine'))
{
    /**
     * Definisce una costante verificando che questa prima non sia definita
     * 
     * @param String $constantName Costante
     * @param Mixed  $value        Valore
     * 
     * @return boolean
     */
    function cdefine($constantName,$value)
    { 
       if(defined($constantName))
       {
          return false;
       }

       return define($constantName, $value);
    }
}