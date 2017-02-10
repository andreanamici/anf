<?php

/**
 * anframework
 * 
 * [TEST AREA] 
 * 
 * @author Andrea Namici 
 * 
 * @mailto: andrea.namici@gmail.com
 * 
 */   

$kernel = require_once 'app/__bootstrap.php'; /*@var $kernel Application_Kernel*/

/**
 * Kernel Class
 * 
 *  -> inizializzazione delle rotte
 *  -> process Action Controller
 *  -> close kernel
 */
$kernel->initMe('test',true)
       ->run();