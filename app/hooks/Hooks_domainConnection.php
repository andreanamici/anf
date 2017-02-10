<?php


class Hooks_domainConnection extends Abstract_Hooks
{   
   
   public function isRegistrable()
   {
      return false;
   }
   
   public static function getSubscriberConfiguration() 
   {
      return array(
          
           self::HOOK_TYPE_PRE_ACTION => array(
                                           array('onPreAction' => 100)
                                        ),
          
           self::HOOK_TYPE_KERNEL_END => array(
                                           array('onKernelClose' => 100)
                                        )
      );
   }
   
   public function __construct() 
   {
      $this->initMe(self::HOOK_TYPE_SUBSCRIBER,self::getDefaultName(),100);
   }
   
   public function onPreAction(Application_HooksData $hookData) 
   {
      return $hookData;
   }
  
   
   public function onKernelClose(Application_HooksData $hookData) 
   {         
      return $hookData;
   }
  
   
}        