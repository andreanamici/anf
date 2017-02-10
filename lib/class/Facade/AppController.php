<?php

/**
 * Facade del controller
 */
class AppController extends \Abstract_Facades
{
    
    protected static function getServiceName()
    {
        return 'controller';
    }
    
    public static function response($content,array $headers = array())
    {
        return self::getService()->generateControllerResponse($content, $headers);
    }
    
    public static function jsonResponse(array $data,array $headers = array())
    {
        return self::getService()->generateControllerResponse(json_encode($data),$headers);
    }
}