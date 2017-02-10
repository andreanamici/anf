<?php

/**
 * Interfaccia per la gestione delle stringhe di traduzioni
 */
interface Interface_ApplicationLanguages
{
    /**
     * Nome del file di catalogo cachato 
     * @return String
     */
    const APPLICATION_LANGUAGE_CATALOGUE_FILE_NAME = 'application-language-catalogue-%s';
    
    /**
     * Dominio di ricerca delle stringhe di default
     * @var String
     */
    const LANGUAGES_LOCALE_DEFAULT_DOMAIN  = LANGUAGES_LOCALE_DEFAULT_DOMAIN;
       
    /**
     * Path in cui sono storate le traduzioni nei rispettivi formati
     * @var String
     */
    const LANGUAGES_LOCALE_DEFAULT_PATH    = LANGUAGES_LOCALE_PATH_DEFAULT;
}