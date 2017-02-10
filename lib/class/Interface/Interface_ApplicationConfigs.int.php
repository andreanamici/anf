<?php

interface Interface_ApplicationConfigs
{
   
   /**
    * Estensione dei file di configurazione YAML
    * @var String
    */
   const CONFIGS_FILE_EXTENSION_YAML    = 'yml';
   
   
   /**
    * Estensione dei file di configurazione "PHP"
    * @var String
    */
   const CONFIGS_FILE_EXTENSION_PHP     = 'php';
   
   
   /**
    * Estensione dei file di cache creati a partire da quelli sorgenti di formati diversi
    * @var String
    */
   const CONFIGS_FILE_CACHE_EXTENSION   = 'cache';
   
   
   /**
    * Nome del file principale ricercato nelle cartelle di configurazioni, puà essere utile per stabile eventuali dipendenze
    * durante l'inclusione dei file dalla classe
    * 
    * @var String
    */
   const CONFIGS_FILE_MAIN               = '_main';
   
   
   /**
    * Estensione di default dei file di configurazione
    * 
    * @var String
    */
   const CONFIGS_FILE_EXTENSION_DEFAULT = self::CONFIGS_FILE_EXTENSION_PHP;
   
   
   /**
    * Nome del file di configurazione del package (senza estenzione, solo nome file)
    */
   const CONFIGS_FILE_NAME_PACKAGE = 'application-configs';
   
   /**
    * Plugin che verrà incluso contenente le function yaml_* utili per il parse/dump dei file yaml
    * 
    * @var String
    */
   const YAML_PLUGIN_NAME               = 'Spyc';
}