<?php

Interface Interface_FileWriter
{
   public  function file_open($file,$mode);
   
   public  function file_delete();
   
   public  function file_write($content);
   
   public  function file_close();
   
   public  function file_exists();
}
