<?php

require_once dirname(__FILE__).'/Application_RoutingData.app.php';

/**
 * Classe che gestisce i dati restituiti dal processamento degli hook
 * 
 * Questa classe estende gli ArrayObject, con la possibilità di accedere alle proprietà come se fosse un array associativo
 * 
 */
final class Application_HooksResponseData extends Application_HooksData
{  
   
   /**
    * Classe che gestisce i dati delle Rotte processate
    * @param Array $array
    */
   public function __construct($array = Array()) {      
      return parent::__construct($array);
   }   
}
