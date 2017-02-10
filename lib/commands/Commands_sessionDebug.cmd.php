<?php

class Commands_sessionDebug extends Abstract_Commands
{
   
   public function getName()
   {
      return 'session:debug';
   }
   
   public function doProcessMe()
   {      
      $session = $this->getApplicationKernel()->get('session')->getAll();
      
      return $this->setResponse("<pre>".htmlentities(print_r($session,true))."</pre>");
   }
   
   
}

