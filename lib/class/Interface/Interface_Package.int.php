<?php


/**
 * Interfaccia package objects
 */
interface Interface_Package extends Interface_ApplicationConfigs
{
   public function isEnable();
   
   public function onLoad();      
}