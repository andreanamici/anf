<?php

/**
 * Manager default
 */
define("DB_MANAGER_CONFIG_DEFAULT", "default");

/**
 * Configurazione Parametri connessione al Database
 */
define("DB_MANAGER_CONFIGS", serialize(Array(
    'default' => Array(
        'user'     => 'root',
        'password' => '',
        'port'     => '3306',
        'dbname'   => 'anf',
        'host'     => '127.0.0.1',
        'driver'   => 'mysql',
        'charset'  => 'utf8',
        'persistent' => true,
        'writelog' => LOGS_ENABLE,
        'table_prefix' => ''
    ),
    'sqlite_memory' => Array(
        'dbname' => ':memory:',
        'driver' => 'sqlite',
        'writelog' => LOGS_ENABLE,
        'table_prefix' => ''
    ),
//     'other'    => Array(
//                      'user'         => '',
//                     'password'     => '',
//                     'port'         => '3306',
//                     'dbname'       => '',
//                     'host'         => '',
//                     'driver'       => 'mysql',
//                     'charset'      => 'utf8',
//                     'persistent'   => true,
//                     'writelog'     => false,
//                     'table_prefix' => 'waid'
//                  ),
)));
