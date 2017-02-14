<?php

return array(
 
    '_welcome_method_generic' => array(
        'path'    => '/welcome/do/(:method)',
        'action'  => 'controllers\WelcomeController::do{method}'
    ),
    
    '_welcome_last' => array(
        'path'      => '/welcome/last',
        'action'    => 'controllers\WelcomeController::helloName',
        'defaults'  => array(
            'name' => '@session.welcome_name',  //service string
        ),
        'params'    => array(
            'name' => '(:[string])'
        )
    ),
        
    '_welcome_name' => array(
        'path'      => '/welcome/{name}',
        'action'    => 'controllers\WelcomeController::helloName',
        'defaults'  => array(
            'name' => 'guest',
        ),
        'params'    => array(
            'name' => '(:[string])'
        )
    ),
    
    
);