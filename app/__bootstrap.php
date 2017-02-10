<?php

/**
 * anframework
 * 
 * @author Andrea Namici 
 * 
 * @mailto: andrea.namici@gmail.com
 * 
 * Questo file carica il kernel e lo restituisce
 * 
 */

/**
 * Path assoluto del progetto, in cui sono caricati i file del front-controller
 * 
 * @var String
 */
define("ROOT_PATH", realpath( dirname(__FILE__) . '/../' ));

/**
 * Path assoluto che contiene le librerie del framework
 */
define("APPLICATION_CORE_PATH",realpath( dirname(__FILE__) . '/../lib'));

/**
 * Path assoluto delle classi core dell'applicazione
 */
define("APPLICATION_LIBRARY_CORE_PATH",realpath( dirname(__FILE__) . '/../lib/application' ));

/**
 * Path in cui sono definiti i file di includes del core
 */
define("APPLICATION_INCLUDES_CORE_PATH",APPLICATION_CORE_PATH.'/includes');

/**
 * Path in cui è caricato il file bootstrap che carica e restituisce il Kernel
 */
define("APPLICATION_APP_PATH",realpath( dirname(__FILE__) . '/' ));

/**
 * Path assoluto dove trovare le classi che estendono quelle del core dell'applicazione
 * 
 * @var String
 */
define("APPLICATION_LIBRARY_PATH",realpath( dirname(__FILE__) .'/lib/application'));

/**
 * Classe che gestisce il kernel dell'applicazione, default \Application_Kernel
 * 
 * @var String
 */
define("APPLICATION_KERNEL_CLASS",'\ApplicationKernel');


// **************  CHECK ENVIRONMENT    *********************************
require_once APPLICATION_INCLUDES_CORE_PATH . '/checkenvironment.php';
// **********************************************************************

// *************** BOOTSTRAP LOAD ***************************************
require_once APPLICATION_INCLUDES_CORE_PATH . '/bootstrap.php';
// **********************************************************************

// *************** CORE KERNEL *******************************************
require_once APPLICATION_LIBRARY_CORE_PATH. '/Application_Kernel.app.php';
// ***********************************************************************

// *************** APP KERNEL ********************************************
require_once APPLICATION_LIBRARY_PATH . '/ApplicationKernel.php';
// *************** APP KERNEL ********************************************

// *************** KERNEL INSTANCE ***************************************
$kernel = \ApplicationKernel::getInstance();
// ***********************************************************************

if(!$kernel || !($kernel instanceof \Application_Kernel))
{
   die("Instanza del kernel non trovata!");
   exit(0);
}

return $kernel;