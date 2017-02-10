<?php


if(!function_exists('rrmdir'))
{
   /**
    * Elimina una directory ricorsivamente
    * 
    * @param String $dir Path directory
    * 
    * @return Boolean
    */
   function rrmdir($dir) 
   { 
      return Utility_CommonFunction::rrmdir($dir);
   }
}
