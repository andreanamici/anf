<?php

class Cache_APC extends Cache_Abstract
{

    const CACHE_DEFAULT_TTL = 4600;

    private $cacheKeyPrefix = CACHE_APC_KEYPREFIX;
   
    public function __construct()
    {
        return true;
    }

    public function check()
    {
        if (!extension_loaded('apc'))
        {
            return false;
        }

        return true;
    }

    public function fetch($key)
    {
        $key = $this->adjustKeyPrefix($key);
        return apc_fetch($key);
    }

    public function store($key, $data, $ttl, $compres = 0)
    {
        $key = $this->adjustKeyPrefix($key);
        return apc_store($key, $data, $ttl);
    }

    public function delete($key)
    {
        $key = $this->adjustKeyPrefix($key);
        return apc_delete($key);
    }

    public function deleteByPrefix($prefix)
    {
        return false;
    }

    public function flush()
    {
        apc_clear_cache();
        apc_clear_cache('user');
        apc_clear_cache('opcode');

        return true;
    }

    /**
     * Aggiunge il prefisso alla chiave qualora ne sia sprovvista
     * 
     * @param string $key chiave
     * 
     * @return boolean|string
     */
    private function adjustKeyPrefix($key)
    {
        if (strlen($key) == 0)
        {
            return false;
        }

        if (strpos($key, $this->cacheKeyPrefix) !== 0)
        {
            $key = $this->cacheKeyPrefix . $key;
        }

        return $key;
    }

}
