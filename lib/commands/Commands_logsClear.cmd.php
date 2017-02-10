<?php



class Commands_logsClear extends Abstract_Commands
{
   
   public function getName()
   {
      return 'logs:clear';
   }
   
   
   public function doProcessMe()
   {
      if($this->getLogsManager()->clearAll())
      {
         return $this->setResponse(true);
      }
      
      return $this->setResponse(false);
   }
   
   
}


