<?php

require_once 'application.php';

/**
 * Abilita i plugins
 */
define("APPLICATION_PLUGINS_ENABLE",true);


/**
 * Paths in cui ricercare le cartelle dei plugins
 */
define("APPLICATION_PLUGINS_PATHS",serialize(Array(
                ROOT_PATH.'/lib/plugins',
                ROOT_PATH.'/app/plugins'
)));