<?php


/**
 * MimeType ed estensioni dei file supportati per l'upload, array() vuoto per qualunque file
 * @var Array
 */
define("UPLOADS_ALLOWED_EXTENSIONS",serialize(array(
               'image/gif'                => 'gif',
               'image/png'                => 'png',
               'image/jpeg'               => 'jpg',
               'image/pjpeg'              => 'jpg',
               'image/bmp'                => 'bmp',
               'image/x-png'              => 'gif',
               'application/octet-stream' => 'zip',
               'application/zip'          => 'zip'
)));