<?php

require_once 'application.php';

/**
 * Indica se i comandi sono abilitati
 */
define("APPLICATION_COMMANDS_ENABLE",true);

/**
 * Permette ai commands dei package di autoregistrarsi senza che sia specificato un file di configurazione specifico
 */
define("APPLICATION_COMMANDS_REGISTER_WITHOUT_FILE",true);

/**
 * Path di default dell'application
 */
define("APPLICATION_COMMANDS_DEFAULT_PATH",ROOT_PATH."/lib/commands");

/**
 * Paths assoluti in cui trovare i comandi
 */
define("APPLICATION_COMMANDS_PATHS",serialize(array( 
            APPLICATION_COMMANDS_DEFAULT_PATH,
            ROOT_PATH."/app/commands"
)));

/**
 * Estenzione file commands
 */
define("APPLICATION_COMMANDS_FILE_EXTENSION","cmd.php");
