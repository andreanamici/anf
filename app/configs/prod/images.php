<?php

/**
 * Dimensione Massima della larghezza delle immagini.
 */
define("IMAGES_WIDTH",640);        //in Pixel

/**
 * Dimensione Massima dell'altezza  delle immagini.
 */
define("IMAGES_HEIGHT",480);       //in Pixel

/**
 * Dimensione Minima della larghezza delle immagini.
 */
define("IMAGES_WIDTH_MIN",400);        //in Pixel

/**
 * Qualità immagine, default 75
 */
define("IMAGES_QUALITY",90);

/**
 * Dimensione Minima dell'altezza  delle immagini.
 */
define("IMAGES_HEIGHT_MIN",250);       //in Pixel

/**
 * Nome cartella che contiene le immagini/foto del portale
 */
define("IMAGES_DIRECTORY_NAME","pictures");


/**
 * MimeType ed estensioni file supportati per il caricamento delle immagini
 */
define("IMAGES_UPLOAD_ALLOWED_EXTENSION",serialize(array(
    
                     'image/gif'                => 'gif',
                     'image/png'                => 'png',
                     'image/jpeg'               => 'jpg',
                     'image/pjpeg'              => 'jpg',
                     'image/bmp'                => 'bmp',
                     'image/x-png'              => 'gif'
    
)));

/**
 * Path assoluto Directory Upload Immagini. 
 * 
 * In questa cartella saranno create tutte le sottodictory relative agli object (sqltablename) che ne richiederranno il caricamento
 */
define("IMAGES_UPLOAD_DIRECTORY",ROOT_PATH."/var/".IMAGES_DIRECTORY_NAME);

/**
 * Se settato a true, il portale unirà la foto con una 'firma' (immagine)
 */
define("IMAGES_HAVE_WATERMARK",false);

/**
 * Se settato a true, il portale unirà la foto con una 'firma' (immagine)
 */
define("IMAGES_WATERMARK_PATH",IMAGES_UPLOAD_DIRECTORY.'__watermark.png');

/**
 * Se settato a false, il portale non creerà thumbnails alle immagini
 */
define("IMAGES_HAVE_THUMBNAILS",true);

/**
 * Size Thumbnails. Questi size sono utilizzati da EntitiesManager_Picture
 */
define('IMAGES_THUMBNAILS',serialize(Array( 
                                            "tns"   => array("w"=>80,  "h"=>80),
                                            "tnm"   => array("w"=>140, "h"=>140),
                                            "tnl"   => array("w"=>250, "h"=>250),
                                            "tn"    => array("w"=>220, "h"=>220)  //Default Thumb
                                          )));

/**
 * Peso massimo immagine
 */
define("IMAGES_MAX_FILE_SIZE",5000000);

/**
 * Path relativo dell'immagine http
 */
define("IMAGES_NOT_AVAIBLE_RELATIVE_PATH",HTTP_ROOT.'images/avatar.png');

/**
 * Path immagine non disponibile assoluto
 */
define("IMAGES_NOT_AVAIBLE_ABSOLUTE_PATH",ROOT_PATH."/user/web-default/resourcers/images/avatar.png");
