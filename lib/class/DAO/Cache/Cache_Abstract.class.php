<?php

abstract class Cache_Abstract implements Interface_CacheMethod
{
    public function __toString()
    {
        return get_called_class();
    }
}