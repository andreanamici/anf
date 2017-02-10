<?php


interface Interface_Commands
{
      
   /**
    * Restiuisce il nome del comando
    * @return null
    */
   public function getName();
   
   /**
    * Elabora questo comando
    * 
    * <b>Questo metodo va sovrascritto per l'elaborazione del comando figlio</b>
    * 
    */
   public function doProcessMe();
}


