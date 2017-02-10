<?php

class Commands_sessionClear extends Abstract_Commands
{
   
   public function getName()
   {
      return 'session:clear';
   }
   
   public function doProcessMe()
   {      
      $this->getApplicationKernel()->get('session')->clearData();
      return $this->setResponse("<pre>Session clear ok</pre>");
   }
   
   
}

