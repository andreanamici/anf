<?php

abstract class Abstract_CacheManager implements Interface_CacheManager
{    
    const CACHE_DEFAULT_TTL = 3600;
   
    /**
     * Salva il dato in cache
     * @param String $key         Chiave oggetto
     * @param Mixed  $value       Valore dell'oggetto cachato
     * @param Int    $ttl         Time to live, durata chiave
     * @param Int    $compressed  Compression Mode
     */
    abstract public function store($key,$value,$ttl,$compressed);
    
    /**
     * Ricerca una chiave, se la trova restituisce il valore cachato
     * @param String $key
     */
    abstract public function fetch($key);
       
    /**
     * 
     * @param type $key
     */
    abstract public function delete($key);
}

