<?php

require_once 'controller.php';

/**
 * Path in cui trovare i file per gestire le viste degli errori e le eccezioni
 * @var String
 */
define("EXCEPTION_ERROR_VIEWS_PATH", ROOT_PATH. DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR .'views'. DIRECTORY_SEPARATOR. 'errors');

/**
 * Path pagina errore generica default: 'app/resources/views/errors/error.php'
 * @var String
 */
define("EXCEPTION_ERROR_PAGE",EXCEPTION_ERROR_VIEWS_PATH . DIRECTORY_SEPARATOR . APPLICATION_TEMPLATING_ERROR_FILENAME);

