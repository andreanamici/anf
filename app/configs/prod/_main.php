<?php

/**
 * Questo file permette di richiamare nell'ordine neccessario eventuali file che hanno priorità rispetto agli altri.
 * 
 * Questi file potrebbero definire delle costanti riutilizzate in altri file di configurazione
 */
require_once 'site.php';
require_once 'session.php';
