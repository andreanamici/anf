<?php

/**
 * Interfaccia base per i manager di cache
 */
interface Interface_CacheMethod
{
    /**
     * Stora il dato in cache
     * 
     * @param String $key     Chiave
     * @param Mixed  $value   Valore
     * @param Int    $ttl     Time to live della chiave, 0 equivale a chiave senza scadenza
     * 
     * @return Mixed
     */
    public function store($key,$value,$ttl);

    /**
     * Ricerca la chiave indicata in cache
     * 
     * @param String $key Chiave
     * 
     * @return Mixed
     */
    public function fetch($key);

    /**
     * Elimina una chiave specifica
     * 
     * @param String $key Chiave
     */
    public function delete($key);   
    
    /**
     * Elimina tutte le chiavi che hanno il prefisso indicato
     * 
     * @param String $prefix Prefisso della chiave
     */
    public function deleteByPrefix($prefix);
    
    /**
     * Invalida tutte le chiavi
     */
    public function flush();
}
