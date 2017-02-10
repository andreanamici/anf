<?php

/**
 * Abilita la protezione al Cross-site request forgery 
 */
define("FORM_CSRF_PROTECTION",false);
        
/**
 * Indica il nome del token
 */
define("FORM_CSRF_TOKEN_NAME","_token");

/**
 * Indica il nome del cookie
 */
define("FORM_CSRF_COOKIE_NAME","_token");

/**
 * Indica la durata del token
 */
define("FORM_CSRF_LIFETIME",7200);

/**
 * Indica se rigenerare il cookie ad ogni submit
 */
define("FORM_CSRF_REGENERATE_EACH_SUBMISSION",false);