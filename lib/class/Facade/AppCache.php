<?php

/**
 * @see \DAO_CacheManager
 */
class AppCache extends Abstract_Facades
{
    protected static function getServiceName()
    {
        return 'cache';
    }
}