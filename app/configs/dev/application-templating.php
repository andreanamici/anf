<?php

 //VIEW CONTROLLER  ////////////////////////////////////////////////////////////////////////
 ///////////////////////////////////////////////////////////////////////////////////////////

 /**
  * Nome directori in cui sono presenti tutti i package dell'applicazione
  */
 define("APPLICATION_TEMPLATING_PACKAGE_DIRECTORY_NAME","package");
 
 /**
  * Path a partire dal package attuale nella quale ricercare le risorse assets
  */
 define("APPLICATION_TEMPLATING_ASSETS_PATH","resources".DIRECTORY_SEPARATOR."public");
 
 /**
  * Path a partire dal package attuale nella quale ricercare i template
  */
 define("APPLICATION_TEMPLATING_TPL_PATH","resources".DIRECTORY_SEPARATOR."views");
 
 /**
  * Directory template errori, relativa al package
  */
 define("APPLICATION_TEMPLATING_TPL_DIR_ERROR","resources".DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR."errors");
 
 /**
  * Nome file php per gestire gli errori di default, qualora l'errore specifico non sia disponibile
  */
 define("APPLICATION_TEMPLATING_ERROR_FILENAME","error.php");
 
 
 /**
  * Default Charset pagine web
  */
 define("APPLICATION_TEMPLATING_DEFAULT_CHARSET",SITE_CHARSET);
 
 /**
  * Indica se utilizzare cache anche per i template elaborati. Settando questo a false, non verrà creata una copia .html del  TPL compilato. ( sempre se il template engine specificato è diverso da php! )
  */
 define("APPLICATION_TEMPLATING_TPL_CACHE_ENABLE",false);
 
 
 /**
  * Durata in secondi dei file cache per i template
  */
 define("APPLICATION_TEMPLATING_TPL_CACHE_TIME_EXPIRE",-1);
 
 
 /**
  * Path directory cache template dalla rootsite del progetto
  */
 define("APPLICATION_TEMPLATING_TPL_CACHE_DIR","var".DIRECTORY_SEPARATOR."cache".DIRECTORY_SEPARATOR."views");
 
 
 
 /**
  * Indicare il nome del service del template engine da utilizzare
  * 
  * Disponibili:
  * 
  * <ul>
  *   <li>templating.rain</li>
  *   <li>templating.smarty</li>
  *   <li>templating.twig</li>
  * </ul>
  * 
  */
 define("APPLICATION_TEMPLATING_TPL_ENGINE_SERVICE","");
 
 
 /**
  * Estenzione file template
  */
 define("APPLICATION_TEMPLATING_TPL_FILE_EXTENSION","php");
  
 ///////////////////////////////////////////////////////////////////////////////////////////
 ///////////////////////////////////////////////////////////////////////////////////////////
 ///////////////////////////////////////////////////////////////////////////////////////////
 