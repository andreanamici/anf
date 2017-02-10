<?php

/**
 * File di configurazione principale per gli application-* confs
 */

require_once 'controller.php';
require_once 'httpstatus.php';


/**
 * Output compression di default, specificare a "off" per disabiltiare
 */
define("APPLICATION_OUTPUT_BUFFERING_DEFAULT","ob_gzhandler");

/**
 * Path in cui cercare le risorse di default
 */
define("APPLICATION_RESOURCES_DEFAULT_PATH",APPLICATION_APP_PATH . DIRECTORY_SEPARATOR . 'resources');

/**
 * Nome directory in cui ricercare i template di default
 */
define("APPLICATION_RESOURCES_ASSETS_DIR_NAME","public");

/**
 * Path directory in cui cercare i template di default
 */
define("APPLICATION_RESOURCES_ASSETS_DIRECTORY_PATH",APPLICATION_RESOURCES_DEFAULT_PATH . DIRECTORY_SEPARATOR . APPLICATION_RESOURCES_ASSETS_DIR_NAME);

/**
 * Url relativo in cui ricercare gli assets a partire dalla document root
 */
define("APPLICATION_RESOURCES_ASSETS_RELATIVE_URL","assets");

/**
 * Nome directory in cui ricercare i template di default
 */
define("APPLICATION_RESOURCES_TEMPLATE_DIR_NAME","views");

/**
 * Path directory in cui cercare i template di default
 */
define("APPLICATION_RESOURCES_TEMPLATE_DIRECTORY_PATH",APPLICATION_RESOURCES_DEFAULT_PATH . DIRECTORY_SEPARATOR . APPLICATION_RESOURCES_TEMPLATE_DIR_NAME);