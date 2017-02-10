<?php



class Commands_routeDebug extends Abstract_Commands
{
   
   public function getName()
   {
      return 'route:debug';
   }
   
   public static function getParametersSchema() 
   {
      return array('compiled');
   }
   
   
   public function doProcessMe()
   {
      $compiled     = $this->getParam('compiled') ? true : false;
            
      $allRoutes    = $compiled ? $this->getApplicationRouting()->getRoutingMapsCompiled() : $this->getApplicationRouting()->getRoutingMaps();
      $responseHtml = "<pre>".htmlentities(print_r($allRoutes,true))."</pre>";
      
      return $this->setResponse($responseHtml);
   }
}

