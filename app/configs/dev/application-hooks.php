<?php

require_once 'application.php';

/**
 * Indica se gli hooks sono abilitati
 */
define("APPLICATION_HOOKS_ENABLE",true);

/**
 * Permette agli hooks dei package di autoregistrarsi senza che sia specificato un file di configurazione specifico
 */
define("APPLICATION_HOOKS_REGISTER_WITHOUT_FILE",false);

/**
 * Permette agli hooks con lo stesso nome di potersi registrare più volte
 */
define("APPLICATION_HOOKS_NAME_UNIQUE",true);

/**
 * Path assoluto in cui trovare gli hooks del core
 */
define("APPLICATION_HOOKS_DEFAULT_DIRECTORY",APPLICATION_CORE_PATH.'/hooks');

/**
 * Path assoluto in cui trovare gli hooks dell'applicazione
 */
define("APPLICATION_HOOKS_APP_DIRECTORY",APPLICATION_APP_PATH.'/hooks');

/**
 * Paths in cui trovare gli hooks
 */
define("APPLICATION_HOOKS_PATHS", serialize(array(
        APPLICATION_HOOKS_DEFAULT_DIRECTORY,
        APPLICATION_HOOKS_APP_DIRECTORY
)));

/**
 * Nome del file di configurazione degli hooks nei package (senza estenzione)
 */
define("APPLICATION_HOOKS_CONFIGS_FILE_NAME","application-hooks");

/**
 * Estenzione file hooks, es: "hook.php", o "php"
 */
define("APPLICATION_HOOKS_FILE_EXTENSION","php");

/**
 * Metodo di default eseguito negli hooks
 */
define("APPLICATION_HOOKS_MAIN_METHOD_NAME","doProcessMe");