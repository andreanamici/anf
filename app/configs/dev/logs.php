<?php

/**
 * Indica se è abilitato il log su file
 */
define("LOGS_ENABLE",true);

/**
 * Path cartella dei file di log
 */
define("LOGS_DIRECTORY",ROOT_PATH."/var/logs");

/**
 * Dimensione massima file di log, indicare in byte: 5Mb => 5000000
 */
define("LOGS_MAX_FILE_SIZE",5000000);


/**
 * Lista delle tipologie di file di log gestite
 */
define("LOGS_FILE_TYPES",serialize(Array(
                    "stacktrace",
                    "error",
                    "exception",
                    "notice",
                    "info",
                    "querylog",
                    "cachelog",
                    "actionresponse"
)));
