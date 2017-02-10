<?php

if(!function_exists('url'))
{
    /**
     * [FUNCTION ALIAS]
     * 
     * Genera l'url assoluto sfruttando il routing o il pseudoRoute
     * 
     * @param  String                   $where      Nome della rotta, pseudo-route o url
     *                                              <ul>
     *                                                <li>Route:           _example_route         </li>
     *                                                <li>PseudoRoute:     action/method       </li>
     *                                                <li>Url:             http://....            </li>
     *                                             </ul>
     * 
     * @param  Array                    $routeData  [OPZIONALE] Array contenente le informazioni utili per generare la rotta, passato al costruttore dell'oggetto Application_RoutingData, passati eventualmente in queryString
     * 
     * @return String
     * 
     * @see Application_Routing::generateUrl
     * 
     * @throws Exception
     */
   function url($where,$params = array())
   {  
      return getApplicationService('routing')->generateUrl($where, $params, true);
   }
}


if(!function_exists('path'))
{
    /**
     * [FUNCTION ALIAS]
     * 
     * Genera l'url relativo sfruttando il routing o il pseudoRoute
     * 
     * @param  String                   $where      Nome della rotta, pseudo-route o url
     *                                              <ul>
     *                                                <li>Route:           _example_route         </li>
     *                                                <li>PseudoRoute:     action/method       </li>
     *                                                <li>Url:             http://....            </li>
     *                                             </ul>
     * 
     * @param  Array                    $routeData  [OPZIONALE] Array contenente le informazioni utili per generare la rotta, passato al costruttore dell'oggetto Application_RoutingData, passati eventualmente in queryString
     * 
     * @return String
     * 
     * @see Application_Routing::generateUrl
     * 
     * @throws Exception
     */
   function path($where, $params = array())
   {
      return getApplicationService('routing')->generateUrl($where, $params, false);
   }
}
