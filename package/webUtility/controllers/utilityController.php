<?php

namespace webUtility\controllers;

/**
 * Controller per la gestione delle utility
 */
class utilityController extends \Application_Controller
{    
    
    public function phpMyAdmin(\Application_ActionRequestData $requestData,$kernel)
    {
        $kernel->hooks->setHookTypeDisabled('preresponse');

        $connectionManager  =  $kernel->get('@database.getConfiguration');

        @session_write_close();
        @session_name('phpMyAdmin');
        @session_start();

        foreach($connectionManager as $key => $value)
        {
            $_SESSION['anframework'][$key] = $value;
        }

        $packageName = $this->getApplicationRoutingCurrentRouteData()->getPackage();
        
        return \AppTemplating::response('phpmyadmin',array(

            'phpmyadm_iframe_src' => HTTP_ROOT . 'package' . DIRECTORY_SEPARATOR . $packageName . '/phpmyadmin/index.php',

        ),array(),$packageName,'php');
    }
    
}