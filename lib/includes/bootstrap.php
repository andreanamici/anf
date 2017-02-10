<?php

/**
 * Questo file include tutte le librerie necessari necessarie per inizializzare il Kernel ed eseguire il bootstrap
 */

/**
 * Includo tutti i Trait Application*
 */
require_once ROOT_PATH.'/lib/class/Trait/Trait_ApplicationKernel.trait.php';
require_once ROOT_PATH.'/lib/class/Trait/Trait_ApplicationRouting.trait.php';
require_once ROOT_PATH.'/lib/class/Trait/Trait_ApplicationCommands.trait.php';
require_once ROOT_PATH.'/lib/class/Trait/Trait_ApplicationHooks.trait.php';
require_once ROOT_PATH.'/lib/class/Trait/Trait_ApplicationLanguages.trait.php';
require_once ROOT_PATH.'/lib/class/Trait/Trait_ApplicationConfigs.trait.php';
require_once ROOT_PATH.'/lib/class/Trait/Trait_ApplicationPlugins.trait.php';
require_once ROOT_PATH.'/lib/class/Trait/Trait_ApplicationServices.trait.php';
require_once ROOT_PATH.'/lib/class/Trait/Trait_ApplicationLogsManager.trait.php';
require_once ROOT_PATH.'/lib/class/Trait/Trait_Application.trait.php';

/**
 * Importo tutti gli altri Traits
 */
foreach(glob(ROOT_PATH.'/lib/class/Trait/*.trait.php') as $traitFileClass)
{
   require_once ($traitFileClass);       
}

/**
 * Vendor Autoload
 */
if(file_exists(ROOT_PATH."/vendor/autoload.php"))
{
   require_once ROOT_PATH."/vendor/autoload.php";
}
