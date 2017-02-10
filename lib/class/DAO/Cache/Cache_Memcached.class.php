<?php

class Cache_Memcached extends Cache_Abstract
{

    /**
     * Memcached 
     * @var Memcached
     */
    public $connection;

    /**
     * Cache key prefix
     * 
     * @var String
     */
    private $cacheKeyPrefix = CACHE_MEMCACHE_KEYPREFIX;

    public function __construct()
    {
        if (class_exists('Memcached'))
        {
            $this->connection = new Memcached();
            $this->loadConfiguration();
        }
    }

    public function check()
    {
        if (!extension_loaded('memcached'))
        {
            return false;
        }

        return true;
    }

    public function store($key, $data, $ttl)
    {
        $key = $this->adjustKeyPrefix($key);

        return $this->connection->set($key, $data, $ttl);
    }

    public function replace($key, $data, $ttl)
    {
        $key = $this->adjustKeyPrefix($key);

        return $this->connection->replace($key, $data, $ttl);
    }

    public function fetch($key)
    {
        $key = $this->adjustKeyPrefix($key);

        return $this->connection->get($key);
    }

    public function delete($key, $time = 0)
    {
        $key = $this->adjustKeyPrefix($key);

        return $this->connection->delete($key, $time);
    }

    public function flush()
    {
        return $this->connection->flush();
    }

    public function deleteByPrefix($prefix = null)
    {
        $keys = $this->connection->getAllKeys();

        $prefix = is_null($prefix) ? $prefix : $this->cacheKeyPrefix;

        if (strlen($prefix) == 0)
        {
            return false;
        }

        $nrDeleted = 0;

        if (is_array($keys) && count($keys) > 0)
        {
            foreach ($keys as $index => $key)
            {
                if (strpos($key, $prefix) == 0)
                {
                    if ($this->delete($key))
                    {
                        $nrDeleted++;
                    }
                }
            }
        }

        return $nrDeleted;
    }

    public function addServer($host, $port = 11211, $weight = 10)
    {
        return $this->connection->addServer($host, $port, $weight);
    }

    private function loadConfiguration()
    {
        $servers = unserialize(CACHE_MEMCACHE_SERVER);
        $check = false;

        foreach ($servers as $server)
        {
            $res = $this->addServer($server['host'], $server['port']);
            if ($res)
                $check = true;
        }

        return $check;
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
