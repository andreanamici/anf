<?php



class Commands_hooksDebug extends Abstract_Commands
{
   
   public function getName()
   {
      return 'hooks:debug';
   }
   
   
   public function doProcessMe()
   {      
      $hooksStackString = $this->getApplicationHooks()->getHooksStackIteratorToString();
      return $this->setResponse("<pre>".htmlentities($hooksStackString)."</pre>");
   }
}

