<?php

require_once 'site.php';
require_once 'logs.php';

/**
 * Cache info
 */
define("CACHE_ACTIVE",false);
define("CACHE_DEFAULT_TTL",86400);

define("CACHE_WRITE_LOG",LOGS_ENABLE);
define("CACHE_LOG_FILENAME","cachelog");
define("CACHE_DIRECTORY",ROOT_PATH."/var/cache");
define("CACHE_KEYPREFIX",PROJECT_NAME."_");


/**
 * File Cache
 */
define("CACHE_FILE_ENGINE_NAME","file");
define("CACHE_FILE_DIRECTORY",CACHE_DIRECTORY."/data");
define("CACHE_FILE_GARBAGE_FILENAME","cgi.cache");
define("CACHE_FILE_GARBAGE_TIMEOUT",86400);


/**
 * Memcached
 */
define("CACHE_MEMCACHE_ENGINE_NAME","memcached");
define("CACHE_MEMCACHE_KEYPREFIX",CACHE_KEYPREFIX);
define("CACHE_MEMCACHE_SERVER",serialize(array( 
    
    array( "host"  => "localhost",
           "port"  => 11211
    )
    
)));


/**
 * APC 
 */
define("CACHE_APC_ENGINE_NAME","apc");
define("CACHE_APC_KEYPREFIX",CACHE_KEYPREFIX);


/**
 * Imposta il tipo di caching da utilizzare
 */
define("CACHE_ENGINE",CACHE_APC_ENGINE_NAME); //file,memcache,apc
