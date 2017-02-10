<?php

/**
 * Trait per l'ereditarietà dei metodi del Routing
 */
trait Trait_ApplicationRouting
{      
    /**
     * Restituisce il gestore del routing del Kernel
     * 
     * @return Application_Routing
     */
    public static function getApplicationRouting()
    {
       return \ApplicationKernel::getInstance()->getApplicationRouting();
    }
    
    
    /**
     * Restituisce le informazioni della rotta attulmente elaborata
     * 
     * @return Application_RoutingData
     * 
     * @see \Application_Routing::getApplicationRoutingData()
     */
    public static function getApplicationRoutingCurrentRouteData()
    {
       return self::getApplicationRouting()->getApplicationRoutingData();
    }
    
    
    /**
     * Genera l'url sfruttando il routing o il pseudoRoute
     * 
     * @param  String                   $where      Nome della rotta, pseudo-route o url
     *                                              <ul>
     *                                                <li>Route:           _example_route         </li>
     *                                                <li>PseudoRoute:     action/method       </li>
     *                                                <li>Url:             http://....            </li>
     *                                             </ul>
     * 
     * @param  Array                    $routeData  [OPZIONALE] Array contenente le informazioni utili per generare la rotta, passato al costruttore dell'oggetto Application_RoutingData, passati eventualmente in queryString
     * @param  Boolean                  $absolute   [OPZIONALE] Indica se l'url dovrà essere assoluto, default FALSE
     * 
     * @throws Exception
     * 
     * @return String
     * 
     * @see \Application_Routing::generateUrl()
     */
    public static function generateUrl($where,array $routeData = Array() ,$absolute = false)
    {
      return self::getApplicationRouting()->generateUrl($where,$routeData,$absolute);
    }
   
}


