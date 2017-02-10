<?php

/**
 * Questa classe permette l'utilizzo del design pattern "facade"
 */
abstract class Abstract_Facades
{
    use Trait_ApplicationServices;
    
    protected static function getServiceName()
    {
        throw new \Exception('La classe facciata deve utilizzare un servizio e deve ridefinire il metodo '.__METHOD__);
    }
    
    public static function __callStatic($method, $args)
    {
        $service = static::getService();

        if(!method_exists($service, $method)) 
        {
            throw new \Exception('Il servizio ' . static::getServiceName() . ' non ha il metodo ' . $method);
        }

        return call_user_func_array(array($service, $method), $args);
    }
    
    /**
     * Restituisce il service utilizzato da questo facade
     * 
     * @return Mixed
     */
    protected static function getService()
    {
        return self::getApplicationServices()->getService(static::getServiceName());
    }
}