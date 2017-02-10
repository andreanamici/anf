<?php

/**
 * Interfaccia per la gestione degli hook, utilizzata dal gestore degli hook stessi
 */
interface Interface_ApplicationHooks extends Interface_HooksType
{
   /**
    * Nome della classe invocata per gestire gli hook closure o tramite nome da generare
    * @var String
    */
   const HOOK_CLOSURE_OBJECT_NAME = 'Basic_HookClosure';
   
   /**
    * Nome della classe astratta che deve essere estesa da ogni hook figlio
    * @var String
    */
   const HOOK_ABSTRACT_CLASS_NAME = '\Abstract_Hooks';
   
   /**
    * Nome del file delle configurazioni degli hooks dei package
    * @var STring
    */
   const HOOK_CONFIGS_FILENAME    = APPLICATION_HOOKS_CONFIGS_FILE_NAME;
   
   
   public function registerHook($hook,$hookType,$hookPriority = self::HOOK_PRIORITY_MIN);
   
   public function removeHookByName($hookName,$hookType = null);
   
   public function getHook($hookName,$hookType = null);
      
   public function hasHook($hookName,$hookType = null);
   
   public function processAll();
   
   public function processHookByName($hookName,$hookType = null);
   
}

