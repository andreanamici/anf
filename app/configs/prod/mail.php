<?php

define("EMAIL_MAILER_SMTP",'smtp');
define("EMAIL_MAILER_MAIL",'mail');
define("EMAIL_MAILER_SENDMAIL",'sendmail');

define("EMAIL_MAILER_SMTP_AUTH",false) ;  //Indica se SMTP richiede autenticazione
define("EMAIL_MAILER_SMTP_HOST","");      //Host SMTP
define("EMAIL_MAILER_SMTP_ENC","");       //ssl,tsl
define("EMAIL_MAILER_SMTP_USER", "");     //SMTP - Username
define("EMAIL_MAILER_SMTP_PASSWORD", "");     //SMTP - Password
define("EMAIL_MAILER_SMTP_PORT", 25);     //SMTP - Porta

define("EMAIL_MESSAGE_MAILER",EMAIL_MAILER_MAIL);
define("EMAIL_MESSAGE_SUBJECT_PREFIX", "");
define("EMAIL_MESSAGE_FROM_EMAIL", "noreply@".SITE_DOMAIN);
define("EMAIL_MESSAGE_FROM_NAME", SITE_DOMAIN);