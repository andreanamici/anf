<?php

/**
 * Trait da utilizzare per ereditare i metodi shortcut per la gestione dei flash Messages della session
 * 
 * @see Application_SessionManager
 */
trait Trait_FlashMessages
{         
   /**
    * [ALIAS]
    * 
    * Questo metodo è uno shortuct relativo al metodo "addFlashMessage" del Session Manager
    * Imposta un messaggio flash che sarà disponibile nelle viste una sola volta
    * 
    * @param String $message messaggio da settare
    * 
    * @return Object Oggetto che utilizza il trait
    */
   protected function setFlashMessage($message)
   {
      \ApplicationKernel::getInstance()->get('@session')->addFlashMessage($message);
      return $this;
   }
   
   /**
    * [ALIAS]
    * 
    * Questo metodo è uno shortuct relativo al metodo "addFlashMessageWarning" del Session Manager
    * Imposta un messaggio di warning flash che sarà disponibile nelle viste una sola volta
    * 
    * @param String $message messaggio da settare
    * 
    * @return Object Oggetto che utilizza il trait
    */
   protected function setFlashMessageWarning($message)
   {
      \ApplicationKernel::getInstance()->get('@session')->addFlashMessageWarning($message);
      return $this;
   }
   
   /**
    * [ALIAS]
    * 
    * Questo metodo è uno shortuct relativo al metodo "addFlashMessageError" del Session Manager
    * Imposta un messaggio di error flash che sarà disponibile nelle viste una sola volta
    * 
    * @param String $message messaggio da settare
    * 
    * @return Object Oggetto che utilizza il trait
    */
   protected function setFlashMessageError($message)
   {
      \ApplicationKernel::getInstance()->get('@session')->addFlashMessageError($message);
      return $this;
   }
      
}