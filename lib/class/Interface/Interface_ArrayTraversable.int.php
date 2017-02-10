<?php


/**
 * Intefaccia per la gestione di oggetti che permettono l'uso di array traversable
 */
interface Interface_ArrayTraversable
{
    public function getIndex($index);
    
    public function addIndex($index,$value,array $options = array());
    
    public function exists($index);
    
    public function removeIndex($index);
    
    public function getAll();
}