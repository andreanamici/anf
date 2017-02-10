<?php

/**
 * Singleton Trait
 * 
 * Implementazione del Singleton usabile da tutte le classi
 */
trait Trait_Singleton
{
    private static $_instance = array();
     
    /**
     * Restituisce l'istanza controllando che questa non sia stata già invocata
     * 
     * @return Mixed
     */
    public static function getInstance()
    {
        return static::getInstanceOf( get_called_class(),  func_get_args());
    }
    
    /**
     * Restituisce l'instanza dell'oggetto indicato
     * 
     * @param String $className             Nome reale della classe
     * @param Array  $constructParameters   [OPZIONALE] Parametri del costruttore
     * @param String $classAlias            [OPZIONALE] Nome dell'alias della classe, default NULL
     * 
     * @return Mixed Instanza classe
     * 
     * @throws \Exception
     */
    protected static function getInstanceOf($className,array $constructParameters = array(),$classAlias = null)
    {
        if(!isset(self::$_instance[$className]))
        {
            $reflectionClass = new ReflectionClass($className);
            self::$_instance[$className] = $reflectionClass->newInstanceArgs($constructParameters);
        }
        
        $instance = self::$_instance[$className];
        
        if(!is_null($classAlias))
        {
           self::$_instance[$classAlias] = $instance;
        }
        
        return $instance;
    }
    
    
    protected static function getInstanceWithoutConstructor()
    {
        if(!isset(self::$_instance[$className]))
        {
            $reflectionClass = new ReflectionClass($className);
            self::$_instance[$className] = $reflectionClass->newInstanceWithoutConstructor();
        }
        
        $instance = self::$_instance[$className];
        
        return $instance;
    }
    
    
    public static function __callStatic($method,$args)
    {
        $facadesServices = getApplicationService('services')->getServiceByClassName(__CLASS__);
        
        if($facadesServices)
        {
            return call_user_func_array(array($facadesServices,$method),$args);
        }
        
        throw new \ErrorException('Non è possibile richiamare il metodo '.$method.' per questa classe '.__CLASS__);
    }
}