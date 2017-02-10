<?php

/**
 * Intefaccia EntitiesManager
 * 
 * Questa interfaccia stabilisce i metodi basilari che ogni Manager di Entità deve avere
 */
Interface Interface_EntitiesManager
{
    function add($info);
   
    function update($info,$id);
    
    function search($conditionArr,$limit_start,$limit_end,$orderBy,$orderMode);
    
    function count($conditionArr);
    
}


