<?php

class Entities_Generic extends Abstract_Entities
{
   public static $_class_name       = __CLASS__;
   
   protected static $_fields        = Array();
   
   public function __construct()
   {
      static::$_fields = array_keys($this->toArray());
      $this->init(true,true,__CLASS__);
   }
}



