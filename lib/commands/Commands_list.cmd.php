<?php

/**
 * Questo command genera la lista di tutti i commands presenti e registrati nel kernel
 */
class Commands_list extends \Abstract_Commands
{
   
   public function getName()
   {
      return 'commands:list';
   }
   
   public function getDescription()
   {
       return "Restituisce la lista dei commands presenti nel Kernel";
   }
   
   public function doProcessMe()
   {
      
      $appCommands = $this->getApplicationKernel()->get('@commands');/*@var $appCommands \Application_Commands*/
      
      $appCommandsIterator = $appCommands->getAllCommands();
      $commands = Array();      
      
      if($appCommandsIterator->count() > 0 )
      {
          foreach($appCommandsIterator as $command)/*@var $command \Abstract_Commands*/
          {
              $commands[] = $command->getName() ." - ". $command->getDescription();
          }
      }
      
      $newLine      = $this->getApplicationKernel()->isServerApiCLI() ? PHP_EOL : "<br>";
      $commandsList = join(' ',array_map(function($element) use($newLine){ return $newLine . " - " . $element; },$commands));
      
      return $this->setResponse($commandsList);
   }
}