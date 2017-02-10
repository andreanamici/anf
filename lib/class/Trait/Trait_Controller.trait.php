<?php


/**
 * Trait utile per l'accesso all'ActionController e al viewController
 */
trait Trait_Controller
{   
   /**
    * Restituisce la tipologia di controller Attualmente utilizzata, sfrutta le rotte
    * 
    * @return String
    */
   public static function getControllerType()
   {
      return \ApplicationKernel::getInstance()->getApplicationRouting()->getApplicationRoutingData()->getControllerType();
   }
    
   /**
    * Restituisce il rifermento al controller Action attualmente utilizzato
    * 
    * @return Controllers_ActionController
    */
   public static function getActionController()
   {
      return \ApplicationKernel::getInstance()->getApplicationActionController();
   }
   
}

