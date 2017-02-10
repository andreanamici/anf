<?php

/**
 * Dominio di validità dei cookie
 * @var String
 */
define("COOKIE_DOMAIN",".".SITE_DOMAIN);

/**
 * Path di appartenza dei cookie
 * @var String
 */
define("COOKIE_PATH_SPACE",HTTP_ROOT);

/**
 * Indica se utilizzare i cookie solamente in HTTP
 * @var Boolean
 */
define("COOKIE_HTTP_ONLY",false);

/**
 * Prefix dei cookie
 * @var String
 */
define("COOKIE_PREFIX","");

/***
 * Indica se utilizzare i cookie solamente in https
 */
define("COOKIE_SECURE",false);


/**
 * Lifetime cookie
 */
define("COOKIE_LIFETIME",0);
