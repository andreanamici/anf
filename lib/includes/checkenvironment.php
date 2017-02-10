<?php

/**
 * In questo file sono allocati i controlli basilari per determinare se l'applicazione può essere eseguita tramite la configurazione server attualmente in uso
 */

define("ANFRAMEWORK_PHP_MIN_VERSION",'5.4.0');

if(!version_compare(PHP_VERSION,ANFRAMEWORK_PHP_MIN_VERSION , '>='))
{
   die('Versione di php minima richiesta: '.ANFRAMEWORK_PHP_MIN_VERSION);
   exit(0);
}

if(ini_get('magic_quote_gpc')!==false &&  ini_get('magic_quote_gpc') == 1)
{
   die('Disabilitare i Magic quotes nel file php.ini: "magic_quote_gpc" ');
   exit(0);
}

ini_set('always_populate_raw_post_data',-1);