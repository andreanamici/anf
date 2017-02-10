<?php

/**
 * @Route({
 *   
 *   "_anfjs": {
 *        "path": "/_anfjs/{packageName}",
 *        "action": "Action_anfjs",
 *        "method": "main",
 *        "params": {
 *             "packageName": "(:[string])"
 *        },
 *        "defaults": {
 *             "packageName": ""
 *        }
 *   },
 * 
 *   "_anfjs_httprequest": {
 * 
 *         "path": "/_anfjs/httprequest",
 *         "action": "Action_anfjs",
 *         "method": "httprequest"
 *   },
 * 
 *   "_anfjs_session": {
 * 
 *         "path": "/_anfjs/session/{sessionIndex}",
 *         "action": "Action_anfjs",
 *         "method": "session",
 *         "params": {
 *             "sessionIndex": "(:[string])"
 *         },
 *         "defaults": {
 *              "sessionIndex": ""
 *         }
 *   },
 * 
 *   "_anfjs_routing": {
 * 
 *         "path": "/_anfjs/routing/{packageName}",
 *         "action": "Action_anfjs",
 *         "method": "routing",
 *         "params": {
 *             "packageName": "(:[string])"
 *         },
 *         "defaults": {
 *              "packageName": ""
 *         }
 *   },
 * 
 *   "_anfjs_languages": {
 * 
 *         "path": "/_anfjs/languages/{locale}/{domain}",
 *         "action": "Action_anfjs",
 *         "method": "languages",
 *         "params": {
 *             "locale": "(:[string])",
 *             "domain": "(:[string])"
 *         },
 *         "defaults": {
 *              "locale": "",
 *              "domain": ""
 *         }
 *   },
 * 
 *   "_anfjs_configs": {
 * 
 *         "path": "/_anfjs/configs/{locale}/{domain}",
 *         "action": "Action_anfjs",
 *         "method": "configs",
 *         "params": {
 *             "locale": "(:[string])",
 *             "domain": "(:[string])"
 *         },
 *         "defaults": {
 *              "locale": "",
 *              "domain": ""
 *         }
 * 
 *   },
 * 
 *   "_anfjs_mobile": {
 *        "path": "/_anfjs/mobile",
 *        "action": "Action_anfjs",
 *        "method": "mobile"
 *   },
 * 
 * 
 *   "_anfjs_assets": {
 *        "path": "/_anfjs/assets",
 *        "action": "Action_anfjs",
 *        "method": "assets"
 *   }
 * })
 * 
 * Questa action si occupa di generare i contenuti dinamici javascript
 * dell plugin anfjs, per utilizzare funzionalità del framework client side
 * 
 */
class Action_anfjs extends \Abstract_ActionObject
{
      
   public function __construct() 
   {
      $this->initMe("",self::ACTION_TYPE_ALL,false);      
      $this->setTemplateEngine('templating.rain')
           ->setTemplateFileExtension('tpl');
   }
   
   
   public function __doOnPostProcess(array $responseAdapted) 
   {
      if($responseAdapted && $this->getActionController()->isMainAction())
      {
         /**
          * Cambio l'headers di output cosi da poter stampare a video un text/javascript anche se questo actionObject non nasce per questa tipologia di headers
          */
         $this->registerHook(array($this,'onPreResponse'),Application_Hooks::HOOK_TYPE_PRE_RESPONSE);
      }
      
      return parent::__doOnPostProcess($responseAdapted);
   }
   
   
   public function onPreResponse(Application_HooksData $hookData)
   {                       
       $controllerResponse      = $hookData->getData(); /*@var $controllerResponse Application_ControllerResponseData*/
       $charset                 = strtolower($hookData->getKernel()->get('templating')->getCharset());
       $controllerResponseData  = $hookData->getData();
       $headers                 = $controllerResponseData->getHeaders();
       $headers->offsetSet('Content-type','text/javascript;charset='.strtoupper($charset));
       $controllerResponseData->setHeaders($headers->getArrayCopy());
       $hookData->setData($controllerResponseData);
   }

   /**
    * Carica anfjs e le dipendenze in un unico file js
    * 
    * @param \Application_ActionRequestData $requestData    requestData
    * @param String                         $packageName    Nome del package
    * 
    * @return \Application_ControllerResponseData
    */
   public function doMain(\Application_ActionRequestData $requestData, $packageName)
   {
        $anfjsPath    = resource_path('js/anf.min.js','none');
        $anfjsContent = "\n".file_get_contents($anfjsPath);
        
//        $anfjsContent.= "\n\nanf = new anf('".$this->getKernelEnvironment()."',".($this->getKernelDebugActive() ? 'true':'false').");";
                
        //Carico l'httpRequest
        $anfjsContent.= "\n\n".$this->forwardActionControllerResponseByRoute('_anfjs_httprequest',array('packageName' => $packageName))->getContent();
        
        //Carico le configurazioni
        $anfjsContent.= "\n\n".$this->forwardActionControllerResponseByRoute('_anfjs_configs',array('packageName' => $packageName))->getContent();
                
        //Carico le traduzioni
        $anfjsContent.= "\n\n".$this->forwardActionControllerResponseByRoute('_anfjs_languages',array('packageName' => $packageName))->getContent();
        
        //Carico le informazioni di routing
        $anfjsContent.= "\n\n".$this->forwardActionControllerResponseByRoute('_anfjs_routing',array('packageName' => $packageName))->getContent();
        
        //Carico le sessioni
        $anfjsContent.= "\n\n".$this->forwardActionControllerResponseByRoute('_anfjs_session',array('packageName' => $packageName))->getContent();
        
        //Carico il componente di gestione degli assets
        $anfjsContent.= "\n\n".$this->forwardActionControllerResponseByRoute('_anfjs_assets',array('packageName' => $packageName))->getContent();
        
        //Carico il mobile detector
        $anfjsContent.= "\n\n".$this->forwardActionControllerResponseByRoute('_anfjs_mobile',array('packageName' => $packageName))->getContent();
        
        $anfjsContent = str_replace('\\n','',$anfjsContent);
        
        //$templating->setTemplateEngine($templateEngine);
        
        return response($anfjsContent,array(
                        'content-type' => 'application/javascript'
               ));
   }
   
   /**
    * Carica in anfjs l'httprequest attualmente utilizata
    * 
    * @param Application_ActionRequestData $requestData
    * 
    * @return Action_javascript
    */
   public function doHttpRequest(Application_ActionRequestData $requestData)
   {
       $response = array(
                    "httprequestData" => json_encode(array(    
                            "GET"    => $requestData->getGet()->getAll(false),
                            "POST"   => $requestData->getPost()->getAll(false),
                    ))
                 );
      
     
       return $this->setTemplateList('anfjs/httprequest')
                   ->setResponse($response);
   }
   
   /**
    * Genera un javascript di configurazione dinamico
    * 
    * @param Application_ActionRequestData $requestData
    * 
    * @return Action_javascript
    */
   public function doConfigs(Application_ActionRequestData $requestData,$locale = null,$domain = null)
   {  
      $HTTPS         = $requestData->isHttps() ? 1 : 0;
      $HTTP_SITE     = $requestData->getBaseUrl();
      $HTTP_ROOT     = $requestData->getPath();
      
      $locale = $locale ? $locale : $requestData->getVal('locale',$this->getApplicationLanguages()->getPortalLocale());
      $domain = $domain ? $domain : $requestData->getVal('domain',$this->getApplicationLanguages()->getDefaultDomain());      
      $lang   = $this->getApplicationLanguages()->getLanguageSmallByLocale($locale);
      
      $sessionManager = $this->getSessionManager();
      
      if($this->getApplicationTemplating()->getPackage() != $this->getApplicationTemplating()->getPackageDefault())
      {
         $HTTP_ROOT  = $HTTP_ROOT.$sessionManager->getIndex('package');
      }
      
      $action     = $sessionManager->getIndex('action',Controllers_ActionController::DEFAULT_ACTION);
      $method     = $sessionManager->getIndex('method')  !== Controllers_ActionController::DEFAULT_SUBACTION   ? $sessionManager->getIndex('method')   : '';
      $actionType = $sessionManager->getIndex('actiontype') !== false                                             ? $sessionManager->getIndex('actiontype')  : self::DEFAULT_ACTION_TYPE;
      $referer    = $sessionManager->getFlashData("referer","javascript:history.back();");
      
      $configs = array(

         "http_root"  => $requestData->getPath(),
         "http_site"  => $requestData->getBaseUrl(),
         "ishttps"    => $requestData->isHttps(),
         "lang"       => $lang,
         "locale"     => $locale,
         "action"     => $action,
         "method"     => $method,
         "actiontype" => $actionType,
         "referer"    => $referer,
                 
         "jquery_configs"   =>array(
             
            //Date and Time Piker
            "datetime"=>  array(

                 "date_default"        => "dd/mm/yy",
                 "date_format"         => "yyyy-MM-dd",
                 "datetime_default"    => "AAAA-MM-GG HH=>MM",
                 "datetime_format"     => "yyyy-MM-dd hh=>mm",
                 "dateFormatChars"     => "dMyhHmsa",
                 "month_list"          => [translate("MONTH01"),translate("MONTH02"),translate("MONTH03"),translate("MONTH04"),translate("MONTH05"),translate("MONTH06"),translate("MONTH07"),translate("MONTH08"),translate("MONTH09"),translate("MONTH10"),translate("MONTH11"),translate("MONTH12")],
                 "month_list_short"    => [translate("MONTH01_SHORT"),translate("MONTH02_SHORT"),translate("MONTH03_SHORT"),translate("MONTH04_SHORT"),translate("MONTH05_SHORT"),translate("MONTH06_SHORT"),translate("MONTH07_SHORT"),translate("MONTH08_SHORT"),translate("MONTH09_SHORT"),translate("MONTH10_SHORT"),translate("MONTH11_SHORT"),translate("MONTH12_SHORT")],
                 "day_list"            => [translate("DAY01"),translate("DAY02"),translate("DAY03"),translate("DAY04"),translate("DAY05"),translate("DAY06"),translate("DAY07")],
                 "day_list_short"      => [translate("DAY01_SHORT"),translate("DAY02_SHORT"),translate("DAY03_SHORT"),translate("DAY04_SHORT"),translate("DAY05_SHORT"),translate("DAY06_SHORT"),translate("DAY07_SHORT")],
                 "day_list_min"        => [translate("DAY01_MIN"),translate("DAY02_MIN"),translate("DAY03_MIN"),translate("DAY04_MIN"),translate("DAY05_MIN"),translate("DAY06_MIN"),translate("DAY07_MIN")],
                 "hourText"            => translate("DATE_HOURS"),
                 "minuteText"          => translate("DATE_MINUTES"),
                 "secondText"          => translate("DATE_SECONDS"),
                 "currentText"         => translate("DATE_NOW"),
                 "closeText"           => translate("DATE_OK"),
                 "timeOnlyTitle"       => translate("DATE_SELECT")

            )
        )

     );
               
     $response = array(
                    "env"         => $this->getKernelEnvironment(),
                    "debug"       => $this->getKernelDebugActive(),
                    "configsData" => json_encode($configs)
                 );
      
     
     return $this->setTemplateList('anfjs/configs')
                 ->setResponse($response,true);
   }
   
   /**
    * Stampa a video un javascript con i locale in formato json
    * 
    * @param Application_ActionRequestData $requestData
    * 
    * @return Action_javascript
    */
   public function doLanguages(Application_ActionRequestData $requestData,$locale = null, $domain = null)
   { 
      $locale = $locale ? $locale : $requestData->getVal('locale',$this->getApplicationLanguages()->getPortalLocale());
      $domain = $domain ? $domain : $requestData->getVal('domain',$this->getApplicationLanguages()->getDefaultDomain());      
      
      $this->getApplicationLanguages()->setLocaleDomain($domain)->changeLocale($locale);

      $jsonLocale = Array();
      
      try
      {
          $jsonLocale     = $this->getApplicationLanguages()->exportLocale($locale,$domain,true);
      }
      catch(Exception $e)
      {
          $jsonLocale      = json_encode($jsonLocale,true);
      }
      
      return $this->setTemplateList('anfjs/languages')
                  ->setResponse(array(
                            'jsonLocale' => $jsonLocale,
                            'lang'       => $this->getApplicationLanguages()->getPortalLanguage(),
                            'locale'     => $this->getApplicationLanguages()->getPortalLocale()
                  ));
   }
   
   
   /**
    * Stampa a video un javascript con i locale in formato json
    * 
    * @param Application_ActionRequestData $requestData
    * 
    * @return Action_javascript
    */
   public function doSession(Application_ActionRequestData $requestData,$sessionIndex)
   { 
      if(!$sessionIndex){
        $response['sessionData'] = json_encode($requestData->getSession()->getArrayCopy());
      }else{
        $response['sessionData'] = json_encode($requestData->getSession()->getIndex($sessionIndex));
      }
            
      return $this->setTemplateList('anfjs/session')
                  ->setResponse($response);
   }
   
   /**
    * Stampa a video tutte le rotte del package indicato
    * 
    * @param Application_ActionRequestData $requestData
    * 
    * @return Action_javascript
    */
   public function doRouting(Application_ActionRequestData $requestData,$packageName)
   { 
      $routes    = array();
      $appRoutes = $this->getApplicationRouting()->getRoutingMaps();

      foreach($appRoutes as $routeName => $routeInfo)
      {
          if(strlen($packageName) == 0 || (isset($routeInfo['package']) && $routeInfo['package'] == $packageName))
          {
              unset($routeInfo['action']);
              unset($routeInfo['method']);
              unset($routeInfo['package']);
              unset($routeInfo['compiled']);
              $routes[$routeName] = $routeInfo;
          }
      }
      
      $response['baseUrl']         = $requestData->getBaseUrl();
      $response['basePath']        = $requestData->getPath();
      $response['protocol']        = $requestData->getProtocol();
      $response['isHttps']         = $requestData->isHttps();
      $response['routes']          = json_encode($routes);
      $response['routingShortcut'] = json_encode($this->getApplicationRouting()->getRoutingShortcut());
      
      return $this->setTemplateList('anfjs/routing')
                  ->setResponse($response);
   }
   
   /**
    * Stampa a video il riconoscimento del browser se mobile / tablet
    * 
    * @param Application_ActionRequestData $requestData
    * 
    * @return Action_javascript
    */
   public function doMobile(\Application_ActionRequestData $requestData)
   {
         $mobileDetector = $this->getService('mobile_detector');/*@var $mobileDetector \Mobile_Detect*/
         return $this->setTemplateList('anfjs/mobile')
                     ->setResponse(array(
                            'mobileData' => json_encode(array(
                                'isMobile' => $mobileDetector->isMobile(),
                                'isTablet' => $mobileDetector->isTablet(),
                                'isIOS'    => $mobileDetector->isIOS(),
                                'isAndroid'=> $mobileDetector->isAndroid(),
                                'isWindowsPhone'=> $mobileDetector->isWindowsPhone(),
                            ))
                    ));
   }
   
   /**
    * Stampa a video componente js per la gestione degli assets
    * 
    * @param Application_ActionRequestData $requestData
    * 
    * @return Action_javascript
    */
   public function doAssets(\Application_ActionRequestData $requestData)
   {
         return $this->setTemplateList('anfjs/assets')
                     ->setResponse(array(
                            'assetsData' => json_encode(array(
                                  'assetsPath' => APPLICATION_RESOURCES_ASSETS_RELATIVE_URL,
                            ))
                    ));
   }
}
