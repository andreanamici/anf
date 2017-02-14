<?php

/**
 * Gestore dei servizi interni dell'applicazione
 * 
 * Sfruttando l' Application_Services è possibile ottenere i riferimenti agli oggetti regisrati,
 * senza dover invocare manualmente l'oggetto, ma richiando il metodo $this->getService()
 * 
 */
class Application_Services
{
    
    use Trait_ApplicationKernel,
            
        Trait_ApplicationConfigs,   
            
        Trait_ApplicationPlugins,    
        
        Trait_ObjectUtilities,
            
        Trait_Singleton;
    
    const SERVICE_STRING_PATTERN = '/^@([A-z\-\_]+)([\.A-z0-9]+){0,}/';
    
    /**
     * Indica se di default i servizi sono univoci, quindi una volta registrati non sono sovrascrivibili
     * @var Boolean
     */
    const DEFAULT_SERVICE_UNIQUE     = true;
        
    /**
     * Indica se di default i servizi sono lazy, attivati solamente se ricercati
     * @var Boolean
     */
    const DEFAULT_SERVICE_LAZY       = true;
    
    /**
     * Nome del file di configurazione dei servizi
     * @var String
     */
    const CONFIGS_SERVICES_FILE_NAME = 'application-services';
        
    /**
     * Nome del metodo da invocare sugli oggetti per ottenere il relativo singleton
     * @var String
     */
    const SINGLETON_METHOD_NAME      = 'getInstance';
    
    /**
     * Contenitori dei servizi
     * @var ArrayIterator
     */
    protected $_SERVICES_ITERATOR = null;
    
    /**
     * Gestore dei servizi interni dell'applicazione
     * Sfruttando il ServicesContainer è poissibile ottenere i riferimenti agli oggetti inizializzati automaticamente dal Kernel al suo avvio,
     * senza dover invocare manualmente l'oggetto, ma richiando il metodo $this->getServiceInstance() 
     */
    public function __construct()
    {
        $this->_SERVICES_ITERATOR = new ArrayIterator();                
    }
    
    /**
     * Invoco il destruct su tutti i services alla chiusura di questo oggetto
     * 
     * @return Boolean
     */
    public function __destruct()
    {
        if($this->_SERVICES_ITERATOR && $this->_SERVICES_ITERATOR->count() > 0)
        {
            foreach($this->_SERVICES_ITERATOR as $service)
            {
                if(is_object($service) && method_exists($service, '__destruct'))
                {
                    $service->__destruct();
                }
            }
        }
        
        return true;
    }
    
    /**
     * Restituisce tutti i services registrati
     * 
     * @return ArrayIterator
     */
    public function getAllServices()
    {
        return $this->_SERVICES_ITERATOR;
    }
    
    /**
     * Mostra tutti i servizi
     * 
     * @return string
     */
    public function getAllServicesString()
    {
        $string = "";
        
        foreach($this->_SERVICES_ITERATOR as $serviceName => $service)
        {
            $string.= "\n - ".$service;
        }
        
        return $string;
    }
    
    /**
     * Restituisce il servizio tramite il suo nome di registrazione
     * 
     * @param String $serviceName Service
     * @param Array  $parameters  [OPZIONALE] Parametri aggiuntivi da passare al servizio
     * 
     * @return Mixed
     * 
     * @throws Exception_PortalErrorException
     */
    public function getService($serviceName,array $parameters = array())
    {
        if($this->_SERVICES_ITERATOR->offsetExists($serviceName))
        {
            $appServiceInstance  =  $this->_SERVICES_ITERATOR->offsetGet($serviceName);/*@var $appServiceInstance \Application_ServicesInstance*/
            
            $serviceInstance = $appServiceInstance->getServiceInstance($parameters); //Chiamo il service
                        
            if($this->getApplicationKernel()->isDebugActive())
            {
                $this->writeLog('Servizio restituito: '.$appServiceInstance,'services');
            }
        
            return $serviceInstance;
        }
        
        return self::throwNewException(9239402393248349, 'Non è possibile trovare il service richiesto: '.$serviceName);
    }
    
    /**
     * Restituisce il servizio ricercandolo tramit il nome della sua classe di dichiarazione
     * 
     * @param string $className Nome della classe
     * 
     * @return Mixed
     * 
     * @throws \Exception
     */
    public function getServiceByClassName($className)
    {
        if($this->_SERVICES_ITERATOR->count() > 0)
        {
            foreach($this->_SERVICES_ITERATOR as $serviceName => $service)
            {
                if($service->getClassName() == $className)
                {
                    return $this->getService($serviceName);
                }
            }
        }
        
        return self::throwNewException(9239402393248349, 'Non è possibile trovare il service tramite la classe: '.$className);
    }
    
    
    /**
     * Interpreta una service string, che sfrutta i services registrati sui quali è possibile richiamare metodi/attributi
     * 
     * <b>Esempi di serviceString</b>
     * <ul>
     *    <li>@service.foo.bar</li>
     *    <li>@service->method({.JSON.})</li>
     *    <li>@service.foo.method({.JSON.})</li>
     *    <li>@service->attribute</li>
     * </ul>
     * 
     * @param String $serviceString Stringa da interprepare
     * @param Mixed  $default       Valore da restituire di default
     * @param Array  $arguments     Argomenti da passare (per ottenere i singoli argonemnti, richiamarli con $1 o $nome
     * 
     * @return Mixed risultato
     * 
     * @throws \Exception
     */
    public function callServiceString($serviceString,$default = false, $args = array())
    {
        if(!is_array($args))
        {
            $args = array($args);
        }
        
        $return    = $default;
        $delimiter = '.';        
       
        /**
         * Passo base, se esiste il serivice lo restituisco
         */
        $service = str_replace("@","",$serviceString);
        
        if($this->hasService($service))
        {
            return $this->getService($service,$args);
        }
        
        //1 cercare tutte le parentesi e sostituirle internamente con un alias
        //2 quando vado a cercare i parametri, per ogniuno di esso, verificare che non sia una serviceString, e se lo fosse in ricorsione richiamare il metodo callServiceString
        //3 ottenere i serviceString token, facendo l'explode per il "."
        //4 per ogni serviceString toke, invocare il metodo/proprietà con i parametri trovati tramite l'alias
        
        //5 testare le stringhe:
        //   @httprequest.getIndex(["test",@httprequest.getIndex(["testAlternative",10000])])
        //   @httprequest.test
        //   @session.getAll.test
        //
        
        if(preg_match(self::SERVICE_STRING_PATTERN,$serviceString,$matches))
        {             
            /**
             * Certo tutte le parentesi tonde e le rimpiazzo con un replace
             */
            $paramsAliasIterator = new \ArrayIterator();
            $serviceString = preg_replace_callback('/\((.*)\)/', function($matches) use($paramsAliasIterator){

                $replaceParamsKey = '#'.$paramsAliasIterator->count();
                $paramsAliasIterator->offsetSet($replaceParamsKey,$matches[1]);
                return '('.$replaceParamsKey.')';

            }, $serviceString);
            
            $serviceToken = array_filter(explode($delimiter,$serviceString),function($value){ return !empty($value); });
            $serviceToken = array_values($serviceToken);            
            
            $serviceName  = $serviceToken[0] =  str_replace('@','',$serviceToken[0]); // Il primo serviceToken è un service dal quale partire
            $serviceToken = array_slice($serviceToken, 1);
            
            if($this->hasService($serviceName))
            { 
                $return = $object  = $this->getService($serviceName,$args);

                if(count($serviceToken) > 0)
                {
                    $lastKey     = count($serviceToken)-1;
                    $currentKey  = 0;

                    foreach($serviceToken as $key => $method)
                    {   
                        if(is_array($object))
                        {
                            $dotNotation =  implode(".",array_slice($serviceToken,$currentKey,$lastKey));

                            if($key == $lastKey)
                            {
                                $return =  array_dot_notation($object,$dotNotation);
                            }
                            else
                            {
                                $object =  array_dot_notation($object,$arrayDot);
                            }
                        }
                        else if(is_object($object))
                        {
                            $parameters = array();
                            
                            /**
                             * Ricerco se sono stati passati parametri al metodo
                             */
                            if(preg_match('/([A-z\_]+)\(([0-9\#]+)\)/',$method,$matches))
                            {
                               $method          = $matches[1];
                               $parametersAlias = $matches[2];

                               /**
                                * Ricerco i parametri Alias creati nella serviceString
                                */
                               if($paramsAliasIterator->offsetExists($parametersAlias))
                               {
                                   $jsonParameters = $paramsAliasIterator->offsetGet($parametersAlias);
                                   $parameters     = json_decode($jsonParameters,true);

                                   if(is_null($parameters))
                                   {
                                      return self::throwNewException(93459386483459,'Questa serviceString "'.$serviceString.'" contiene i parametri "'.$jsonParameters.'" passati al metodo "'.$method.'" che non sono una stringa JSON valida!');
                                   }

                                   if(!is_array($parameters))
                                   {
                                       return self::throwNewException(123047039710974,'Questa serviceString "'.$serviceString.'" contiene i parametri "'.$jsonParameters.'" passati al metodo "'.$method.'" che non sono un array. Devi passare un array con la lista dei parametri da passare al metodo. (vedi call_user_func_array)');
                                   }
                                   
                                   foreach($parameters as $i => $value)
                                   {
                                       if(strstr($value, '@') !== false)
                                       {
                                           $parameters[$i] = $this->callServiceString($value,null,$args);
                                       }
                                       else if(strstr($value,'$') !== false)
                                       {
                                           if(preg_match('/\$([A-z0-9\_\-]+)/', $value,$matches))
                                           {
                                               $parameters[$i] = $args[$matches[1]];
                                           }                                           
                                       }
                                   }      
                               }                               
                            }
                            else if($args)
                            {
                                $parameters = $args;
                            }
                            
                            /**
                             * Sto navigando come array un oggetto che non contiene il metodo indicato ma che supporta l'interfaccia Interface_ArrayTraversable
                             */
                            if($object instanceof \Interface_ArrayTraversable && !method_exists($object,$method))   
                            {     
                               $dotNotation =  implode(".",array_slice($serviceToken,$currentKey,$lastKey+1));

                               if($key == $lastKey)
                               {
                                  $return      =  $object->getIndex($dotNotation);
                               }
                               else
                               {
                                  $object      =  $object->getIndex($dotNotation);
                               }
                            }
                            else
                            {
                                $reflectionObject = new ReflectionObject($object);
                                $property         = false;
                                
                                if($reflectionObject->hasMethod('getProperty'))
                                {
                                    try
                                    {
                                        $property  = $reflectionObject->getMethod('getProperty')->invoke($object,$method);
                                    }
                                    catch(\Exception $e)
                                    {
                                        $property = false;
                                    }
                                }
                                else if($reflectionObject->hasProperty($method))
                                {
                                    $property  = $reflectionObject->getProperty($method);
                                    $property->setAccessible(true);
                                    $property = $property->getValue($object);
                                }

                                if($property)
                                {
                                    if($key == $lastKey)
                                    {
                                        $return = $property;
                                    }
                                    else
                                    {
                                        $object = $property;
                                    }
                                }
                                else
                                {
                                    if(method_exists($object, $method))
                                    {
                                        $reflectionObject = new ReflectionObject($object);

                                        if($reflectionObject->hasMethod($method))
                                        {
                                            $reflectionMethod = $reflectionObject->getMethod($method);
                                    
                                            if($key == $lastKey)
                                            {
                                                $callable = array($object,$method);

                                                if($reflectionMethod->isStatic())
                                                {
                                                    $callable = $reflectionObject->getName().'::'.$method;
                                                }

                                                $return = call_user_func_array($callable,$parameters);
                                            }
                                            else
                                            {
                                                $callable = array($object,$method);

                                                if($reflectionMethod->isStatic())
                                                {
                                                    $callable = $reflectionObject->getName().'::'.$method;
                                                }

                                                $object = call_user_func_array($callable,$parameters);
                                            }
                                        }
                                    }
                                    else if(method_exists($object, '__get'))
                                    {
                                        $parameters = array($method);
                                        
                                        if($key == $lastKey)
                                        {
                                            $callable = array($object,'__get');
                                            $return = call_user_func_array($callable,$parameters);
                                        }
                                        else
                                        {
                                            $callable = array($object,'__get');
                                            $object = call_user_func_array($callable,$parameters);
                                        }
                                    }
                                    else
                                    {
                                        return self::throwNewException(28975982656823079,'Questa serviceString "'.$serviceString.'" ricerca per l\'oggetto "'.get_class($object).'" l\'atttributo/metodo "'.$method.'" che non è definito');
                                    }
                                }
                            }
                        }
                        else
                        {
                            $return = $object;
                        }

                        $currentKey++;
                    }
                }
            }
        }
        else
        {
            $serviceName     = str_replace("@","",$serviceString);
            
            if($this->hasService($serviceName))
            {
                return $this->getService($serviceName,$args);
            }
        }

        return $return;
    }
    
    /**
     * Verifica che un servizio sia esistente tra quelli registrati
     * 
     * @param String $serviceName Nome del service
     * 
     * @return Boolean
     */
    public function hasService($serviceName)
    {   
        return $this->_SERVICES_ITERATOR->offsetExists($serviceName);
    }
    
    /**
     * Registra un servizio
     * 
     * @param String  $serviceName    Nome servizio
     * @param Mixed   $service        Mixed servizio, \Application_ServicesInstance, \Closure, Array con la configurazione del servizio
     * @param Boolean $force          Indica se sovrascrivere il service precedentemente dichiarato, default true
     * 
     * @return Application_Services
     * 
     * @throws \Exception
     */
    public function registerService($serviceName,$service,$force = true)
    {
        
        if($force)
        {
            $this->unregisterService($serviceName);
        }
        
        /**
         * Registro i servizi in base al tipo fornito
         */
        switch(gettype($service))
        {
            case 'array': 
                
                return $this->_registerServiceByConfiguration($serviceName, $service);
                
            break;
              
            case 'object' && ($service instanceof \Application_ServicesInstance): 
                
                return $this->_registerService($service);
                
            break;
        
            case 'object':
                
                if(!$service instanceof \Closure)
                {
                    $service = function() use($service) { return $service; };
                }
                
                return $this->_registerServiceByClosure($serviceName,$service);
                
            break;
        
        }
        
        return $this->throwNewException(7823465982374, 'Non è possibile registrare il servizio '.$serviceName.' questo oggetto non è un servizio valido registrabile. ');
    }
    
    /**
     * Deregistra un service
     * 
     * @param String $serviceName Nome del servizio
     * 
     * @return \Application_Services
     */
    public function unregisterService($serviceName)
    {
        if($this->_SERVICES_ITERATOR->offsetExists($serviceName))
        {
            $this->_SERVICES_ITERATOR->offsetUnset($serviceName);
        }
        
        return $this;
    }
    
    
    /**
     * Registra una lista di services
     * 
     * @param array $servicesConfiguration Array di servizi (nome => configurazione)
     * 
     * @return \Application_Services
     * 
     * @throws \Exception
     */
    public function registerServices(array $servicesConfiguration)
    {
        if(count($servicesConfiguration) > 0)
        {
           foreach($servicesConfiguration as $serviceName => $serviceInfo)
           {
               $this->registerService($serviceName,$serviceInfo);
           }  
        }   
        
        return $this;
    }
    
    /**
     * Registra i servizi del package, ricercando le configurazioni su file
     * 
     * @param \Abstract_Package $package package instance
     * 
     * @return Application_Services
     */
    public function registerServicesForPackage(\Abstract_Package $package)
    {
         $appConfigs            = $this->getApplicationConfigs();
         $servicesConfiguration = array();
         
         if($appConfigs->isConfigsChangeForPackage(self::CONFIGS_SERVICES_FILE_NAME, $package->getName()))
         {
            $servicesConfiguration = $appConfigs->getParseConfigsForPackage(self::CONFIGS_SERVICES_FILE_NAME, $package->getName(),$package->getConfigsFileExtension());
         }
         else
         {
            $servicesConfiguration = $appConfigs->getConfigsFromCacheForPackage(self::CONFIGS_SERVICES_FILE_NAME, $package->getName());
         }
         
         
         if($servicesConfiguration && is_array($servicesConfiguration) && count($servicesConfiguration) > 0)
         {
             $this->registerServices($servicesConfiguration);
         }
         
         return $this;
    }
    
    public function getServiceNameByVariable($serviceName)
    {
        $serviceName = str_replace('_','.',$serviceName);
        
        return $serviceName;
    }
    
    /**
     * Registra un servizio tramite configurazione
     * 
     * @param String $serviceName    Nome servizio
     * @param Mixed  $service        Array con la configurazione del servizio
     * 
     * @return Application_Services
     * 
     * @throws \Exception
     */
    private function _registerServiceByConfiguration($serviceName,array $serviceConfiguration)
    {
        
       $serviceAlias        = isset($serviceConfiguration["service"])             ? $serviceConfiguration["service"]              : false;
       $serviceClass        = isset($serviceConfiguration["class"])               ? $serviceConfiguration["class"]                : false;
       $serviceFilePath     = isset($serviceConfiguration["path"])                ? $serviceConfiguration["path"]                 : false;
       $arguments           = isset($serviceConfiguration["arguments"])           ? $serviceConfiguration["arguments"]            : array();
       $plugin              = isset($serviceConfiguration["plugin"])              ? $serviceConfiguration["plugin"]               : false;
       $pluginIncludeOnly   = isset($serviceConfiguration["includeonly"])         ? $serviceConfiguration["includeonly"]          : false;
       $registerCallback    = isset($serviceConfiguration["register_callback"])   ? $serviceConfiguration["register_callback"]    : null;
       $callback            = isset($serviceConfiguration["callback"])            ? $serviceConfiguration["callback"]             : null;
       $unique              = isset($serviceConfiguration["unique"])              ? $serviceConfiguration["unique"]               : self::DEFAULT_SERVICE_UNIQUE;
       $lazy                = isset($serviceConfiguration["lazy"])                ? $serviceConfiguration["lazy"]                 : self::DEFAULT_SERVICE_LAZY;
       $call                = isset($serviceConfiguration["call"])                ? $serviceConfiguration["call"]                 : false;       
       $closure             = isset($serviceConfiguration["closure"])             ? $serviceConfiguration["closure"]              : false;       
       
       
       /**
        * Sto definendo una "call", ovvero un insieme di serviceString che lavorano tra di loro per restituire qualcosa
        */
       if($call)
       {
           if(empty($call['with']))
           {
               return self::throwNewException(92374092743902, 'Non è possibile registare il servizio '.$serviceName.' poichè non è stata indicata l\'attributo "with"');
           }
           
           $appService = $this;
           
           $closure    = function($params) use ($appService,$serviceName,$call,$arguments)
           {
               $flushServiceResult = array();
               
//               function __buildServiceResultName($serviceName,$serviceResultName)
//               {
//                   return "__".$serviceName."_".str_replace("@","",$serviceResultName);
//               }
               foreach($call['with'] as $lineId => $elemToCall)
               {
                  $service         = $elemToCall['service'];
                  $serviceParams   = array();                  
                  
                  if(isset($elemToCall['params']))
                  {
                      $elemToCall['params'] = is_array($elemToCall['params']) ? $elemToCall['params'] : array($elemToCall['params']);
                      
                      foreach($elemToCall['params'] as $key => $param)
                      {
                          $serviceParams[$param] = isset($params[$param]) ? $params[$param] : '__EMPTY__';

                          if($serviceParams[$param] == '__EMPTY__' && (isset($call["defaults"]) && array_key_exists($param,$call["defaults"])))
                          {
                              $serviceParams[$param] = $call["defaults"][$param];
                          }
                              
                          if($serviceParams[$param] == '__EMPTY__')
                          {
                              
                              $appService->throwNewException(49530947603,'Il service "'.$serviceName.'" richiede il parametro "'.$param.'" che non è stato fornito');
                          }
                      }
                  }
                  else
                  {
                      if(isset($call["defaults"]))
                      {
                          $serviceParams = $call["defaults"];
                      }
                  }
                  
                  $result        = $appService->callServiceString($service,null,$serviceParams);
                  
                  if(isset($elemToCall['result']))
                  {
//                     $serviceResultName    = __buildServiceResultName($appService,$serviceName,$elemToCall['result']);
                     $serviceResultName    = $elemToCall['result'];
                     $serviceResult        = $appService->registerService($serviceResultName,function()use($result){ return $result;  },true);
//                     $flushServiceResult[] = $serviceResultName;
                  }
                  
                  if(!empty($elemToCall['if']))
                  {
                      print_r($elemToCall['if']);
                      die();
                  }
               }
               
//               $serviceReturnName = "@".__buildServiceResultName($appService,$serviceName,$call['return']);
               $serviceReturnName = $call['return'];
               $return = $appService->callServiceString($serviceReturnName,false,$params);
              
               foreach($flushServiceResult as $serviceToFlush)
               {
                   $appService->unregisterService($serviceToFlush);
               }
               
               return $return;
           };
       }
       
       /**
        * Se il servizio non è una closure function (a differenza dei lazy che sono classi risvegliate da una closure function
        */
       if(!$closure && !$serviceAlias)
       {
            if($plugin && !$serviceFilePath)
            {
                $appPlugin = $this->getApplicationPlugins();
                $serviceFilePath = $appPlugin->getPluginPath($plugin);
            }

            if(!$plugin && !$serviceClass)
            {
                return self::throwNewException(92374092743902, 'Non è possibile registare il servizio '.$serviceName.' poichè non è stata indicata la classe (parametro class) ');
            }
         
            if(!is_object($serviceClass) && !$serviceFilePath && !class_exists($serviceClass))
            {
                return self::throwNewException(8126902374920734, 'Non è possibile registare il servizio '.$serviceName.' poichè la classe ricercata "'.$serviceClass.'" non è stata trovata');
            }

            if($callback && !is_callable($callback))
            {
                return self::throwNewException(92340923427902, 'Non è possibile registare il servizio '.$serviceName.' poichè la callback indicata non è una callable valida!');
            }

            if($serviceFilePath)
            {
               if(!file_exists($serviceFilePath))
               {
                  return self::throwNewException(0923742743972349, 'Non è possibile registare il servizio '.$serviceName.' poichè il path fornito non è valido: '.$serviceFilePath);
               }
            }
       }
       else //Se il service è una closure function solamente, modifico i parametri di default
       {
           $lazy              = false;
           $unique            = true;
           $class             = false;
           $serviceFilePath   = false;
           $plugin            = false;
           $pluginIncludeOnly = false;
           $callback          = false;
           $registerCallback  = false;
       }

       if($serviceAlias)
       {
           $lazy = true;
       }
       
       $serviceData = array(
           
           'serviceAlias'        => $serviceAlias,
           'name'                => $serviceName,
           'class'               => $serviceClass,
           'serviceFilePath'     => $serviceFilePath,
           'arguments'           => $arguments,
           'plugin'              => $plugin,
           'lazy'                => $lazy,
           'closure'             => $closure,
           'registerCallback'    => $registerCallback,
           'unique'              => $unique,
           'pluginIncludeOnly'   => $pluginIncludeOnly,
           'callback'            => $callback
               
       );
       
       /**
        * Costruisco l'instanza del servizio in base al parametro lazy, che dermina se il servizio sarà
        * dormiente, oppure sarà subito instanziato ed associato a questo Application_ServicesInstance
        */
       $serviceInstance = !$lazy ? $this->_buildServiceInstance($serviceData) : $this->_buildServiceInstanceLazy($serviceData);
           
       /**
        * Servizio
        */
       $applicationServiceInstance =  new \Application_ServicesInstance($serviceInstance, $serviceData);
       
       /**
        * Registra il servizio
        */
       $this->_registerService($applicationServiceInstance);

       return $this;
    }
      
    /**
     * Registra i servizi di default se presenti
     * 
     * @return Application_Services
     */
    public function registerDefaultServices()
    {
        if(defined("APPLICATION_SERVICES"))
        {
            $defaultServices = unserialize(APPLICATION_SERVICES);
            $this->registerServices($defaultServices);
        }
                
        return $this;
    }
    
    /**
     * Registra un serivizo all'applicazione
     * 
     * @param \Application_ServicesInstance $appServiceInstance Servizio
     * 
     * @return Application_ServicesContainer
     */
    private function _registerService(\Application_ServicesInstance $appServiceInstance)
    {
        $serviceName = $appServiceInstance->getName();
        
        if($this->_SERVICES_ITERATOR->offsetExists($serviceName) && $appServiceInstance->getIsUnique())
        {
           return $this;
        }
                        
        $this->_SERVICES_ITERATOR->offsetSet($serviceName,$appServiceInstance);
        
        if(function_exists('strtolowercase') && function_exists('strtocamelcase'))
        {
            $serviceNameLower    = strtolowercase($serviceName);
            $serviceNameCamel    = strtocamelcase($serviceName);
            $serviceNameCamel[0] = strtolower($serviceNameCamel[0]);
            
            $this->_SERVICES_ITERATOR->offsetSet($serviceNameLower,$appServiceInstance);
            $this->_SERVICES_ITERATOR->offsetSet($serviceNameCamel,$appServiceInstance);
        }

        $appServiceInstance->onRegister();
        
        if($this->getApplicationKernel()->isDebugActive())
        {
            if($this->hasService('logger'))
            {
                $this->writeLog('Servizio registrato: '.$serviceName,'services');
            }
        }
        
        return $this;
    }
    
   /**
     * Registra un servizio tramie una closure
     * 
     * @param String   $serviceName     Nome del servizio
     * @param \Closure $service         Service
     * 
     * @return \Application_Services
     */
    private function _registerServiceByClosure($serviceName,\Closure $service)
    {
        return $this->_registerServiceByConfiguration($serviceName, array(
                        'closure' => $service
               ));
    }
    
    /**
     * Restituisce  l'instanza del servizio
     * 
     * @param $serviceData Array dei dati utili per instanziare il servizio
     *
     * @return Mixed
     */
    private function _buildServiceInstance(array $serviceData)
    {
        $name               = $serviceData["name"];
        $serviceClass       = $serviceData["class"];
        $plugin             = $serviceData["plugin"];
        $arguments          = $serviceData["arguments"];
        $pluginIncludeOnly  = $serviceData["pluginIncludeOnly"];
        $closure            = $serviceData["closure"];
        $serviceAlias       = $serviceData["serviceAlias"];
        $callback           = $serviceData["callback"];
        
        $serviceInstance    = null;
        
        if($arguments)
        {
            foreach($arguments as $key => $val)
            {
                $arguments[$key] = $this->callServiceString($val);
            }
        }
        
        if($serviceAlias)
        {
           $serviceInstance = $this->callServiceString($serviceAlias,false,$arguments); 
        }
        else
        /**
         * Il Servizio è una closure
         */
        if($closure)
        {
            if(!($closure instanceof \Closure))
            {
                return $this->throwNewException(982367576238483, 'Non è possibile registrare il servizio'.$name.' poiché la closure non è valida!');
            }
            
            $serviceInstance = $closure;
        }
        /**
         * Il servizio è una classe 
         */
        else if(!$plugin)
        {
             $reflectionClass = new \ReflectionClass($serviceClass);
             $serviceInstance = $reflectionClass->newInstanceWithoutConstructor();

             if(method_exists($serviceInstance,self::SINGLETON_METHOD_NAME))
             {
                 $serviceInstance = call_user_func_array(array($serviceClass,self::SINGLETON_METHOD_NAME),$arguments);
             }
             else
             {
                 $serviceInstance = $reflectionClass->newInstanceArgs($arguments);
             }
        }
        else //Il Servizio è un plugin
        {
             if($pluginIncludeOnly)
             {
                 $this->getApplicationPlugins()->includePlugin($plugin,$arguments);
                 $serviceInstance = new stdClass();
             }
             else
             {
                 $serviceInstance = $this->getApplicationPlugins()->getPluginInstance($plugin,$arguments);
             }
        }
        
        return $serviceInstance;
    }
    
    /**
     * Restituisce un'instanza di \Closure con il quale verra generata l'instanza reale del servizio quando questo verra richiamato
     * 
     * @param $serviceData Array dei dati utili per instanziare il servizio
     * 
     * @return \Closure
     */
    private function _buildServiceInstanceLazy(array $serviceData)
    {
        $applicationService = $this;/*@var $applicationService \Application_Services*/
        
        /**
         * Questa closure sara invocata alla chiamata sel service
         */
        $serviceLazyInstance = function() use ($applicationService,$serviceData)
        {            
            $reflectionApplicationService = new \ReflectionClass($applicationService);
            $reflectionApplicationService->getMethod('_buildServiceInstance')->setAccessible(true);
            $service = $applicationService->_buildServiceInstance($serviceData);
            return $service;
        };
     
        return $serviceLazyInstance;
    }
    
    /**
     * Mostra a video tutti i services
     */
    public function __toString()
    {
        return $this->getAllServicesString();
    }
    
    
    public function __get($name)
    {
        return $this->getService($name);
    }
    
    
    public function __set($name, $value) 
    {
        return $this->registerService($name, $value);
    }
}