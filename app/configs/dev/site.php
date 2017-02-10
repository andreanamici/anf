<?php

/**
 * Url assoluto del portale, compreso di http prefix
 * @var STring
 */
 define("HTTP_SITE",isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '');
 
 /**
  * HTTP root, default "/"
  * @var String
  */
 define("HTTP_ROOT",'/'); 

 /**
  * Dominio del portale
  * @var String
  */
 define("SITE_DOMAIN",isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '');
 
 /**
  * Dominio di 3 livello di default
  * @var String
  */
 define("SITE_SUBDOMAIN_DEFAULT","www");
 
 
 define("SITE_ROBOTS","index, follow");
 
 define("SITE_KEYWORDS","");
 
 define("SITE_DESCRIPTION","");
 
 define("SITE_GENERATOR","anf framework - powered by Andrea Namici, mailto: andrea.namici@gmail.com");
 
 define("SITE_GOOGLE_VERIFICATION","");
 
 define("SITE_CHARSET", "utf-8");

 define("PROJECT_NAME","anf");
  
 define("ROWXPAGE",20);
 
 define("ROWXPAGE_ADMIN",20);
 
 define("MAX_UPLOAD_PICTURES",5);
 
 define("MAX_FILE_SIZE",25000000);
  