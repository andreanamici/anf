<?php

/**
 * Array lingue e locale disponibili
 */
define("LANGUAGES_LOCALE_AVAILABLE",serialize(array(
    
            "it" => array( "locale"   => "it_IT",
                           "name"     => "Italiano",
                           "default"  => true  
                    ),

            "en" => array( "locale" => "en_US",
                            "name"  => "English"
                    ),

//            "fr" => array( "locale" => "fr_FR",
//                           "name"   => "Franca"
//                    ),
//
//            "es" => array( "locale" => "es_ES",
//                            "name"  => "Espa&ntilde;ol"
//                    ),
//
//            "de" => array( "locale" => "de_DE",
//                            "name"  => "Deutsch"
//                    ),
//
//            "ch" => array( "locale" => "ch_CN",
//                           "name"   => "中文(简体)"
//                    ),
)));


/**
 * Dominio di default delle stringhe di traduazione
 */
define("LANGUAGES_LOCALE_DEFAULT_DOMAIN","messages");

/**
 * Percorso di default dei file di locale, sia .mo, che .php, che yml
 */
define("LANGUAGES_LOCALE_PATH_DEFAULT",ROOT_PATH."/app/locale");