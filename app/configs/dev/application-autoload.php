<?php

require_once 'application.php';
require_once 'application-hooks.php';
require_once 'application-commands.php';

/**
 * Path in cui sono presenti le libraries
 */
define("APPLICATION_AUTOLOAD_LIBRARIES_DIRECTORY", ROOT_PATH . DIRECTORY_SEPARATOR . 'lib');


/**
 * Path in cui cercare le classi di default
 */
define("APPLICATION_AUTOLOAD_CLASS_DIRECTORY", APPLICATION_AUTOLOAD_LIBRARIES_DIRECTORY . DIRECTORY_SEPARATOR . 'class');


/**
 * Estenzione classi default
 */
define("APPLICATION_AUTOLOAD_CLASS_DEFAULT_EXTENSION",'class.php');


/**
 * Space in cui ricercare i file delle classi che utilizzano un namespace valido psr-4
 */
define("APPLICATION_AUTOLOAD_NAMESPACE_SRC",serialize(array(
       'app',
       'src',
       'package',
       'lib',
)));


/**
 * Mappatura percorsi e classi per Application_Autoload per namespace "/"
 */
define("APPLICATION_AUTOLOAD_MAP",serialize(Array( 
                  
                  'Application'     => array(
                                           array( 'path'    => APPLICATION_AUTOLOAD_LIBRARIES_DIRECTORY . DIRECTORY_SEPARATOR . 'application','extension'  => 'app.php'),
                                           array( 'path'    => APPLICATION_AUTOLOAD_LIBRARIES_DIRECTORY . DIRECTORY_SEPARATOR . 'application'),
                                       ),
    
                  'Controllers'     => array(
                                           array( 'path'    => APPLICATION_AUTOLOAD_LIBRARIES_DIRECTORY . DIRECTORY_SEPARATOR . 'controllers','extension'  => 'cnt.php','prefix' => false)
                                       ),
        
                  'Commands'        => array(
                                           array( 'path'    => APPLICATION_COMMANDS_DEFAULT_PATH,'extension'  => 'cmd.php')
                                       ),
    
                  'Hooks'           => array(
                                           array('path'     => APPLICATION_HOOKS_DEFAULT_DIRECTORY,'extension'  => 'hook.php')
                                       ),
    
                  'Exception'      => array(
                                           array('path'     => APPLICATION_AUTOLOAD_CLASS_DIRECTORY . DIRECTORY_SEPARATOR . 'Exception','extension'  => APPLICATION_AUTOLOAD_CLASS_DEFAULT_EXTENSION)
                                       ),
    
                  'Interface'       => array(
                                           array('path'    =>  APPLICATION_AUTOLOAD_CLASS_DIRECTORY . DIRECTORY_SEPARATOR .'Interface','extension' => 'int.php')
                                       ),
    
                  'Trait'           => array(
                                           array('path'     => APPLICATION_AUTOLOAD_CLASS_DIRECTORY . DIRECTORY_SEPARATOR . 'Trait','extension'  => 'trait.php')
                                       ),
    
                  'Abstract'        => array(
                                           array('path'     => APPLICATION_AUTOLOAD_CLASS_DIRECTORY . DIRECTORY_SEPARATOR . 'Abstract','extension'   => 'abs.php')
                                       ),

                  'Basic'           => array(
                                           array('path'     => APPLICATION_AUTOLOAD_CLASS_DIRECTORY . DIRECTORY_SEPARATOR . 'Basic','extension'  => APPLICATION_AUTOLOAD_CLASS_DEFAULT_EXTENSION)
                                       ),
    
                  'DAO'             => array(
                                           array('path'     => APPLICATION_AUTOLOAD_CLASS_DIRECTORY . DIRECTORY_SEPARATOR . 'DAO','extension'  => APPLICATION_AUTOLOAD_CLASS_DEFAULT_EXTENSION)
                                       ),
    
                  'Entities'        => array(
                                           array('path'     => APPLICATION_AUTOLOAD_CLASS_DIRECTORY . DIRECTORY_SEPARATOR . 'Entities','extension'  => 'ent.php')
                                       ),
        
                  'EntitiesManager' => array(
                                           array('path'     => APPLICATION_AUTOLOAD_CLASS_DIRECTORY . DIRECTORY_SEPARATOR . 'EntitiesManager','extension'  => 'man.php')
                                       ),
    
                  'Utility'         => array(
                                           array('path'     => APPLICATION_AUTOLOAD_CLASS_DIRECTORY . DIRECTORY_SEPARATOR . 'Utility','extension'  => APPLICATION_AUTOLOAD_CLASS_DEFAULT_EXTENSION)
                                       ),
    
                  'Portal'           => array(
                                           array('path'     => APPLICATION_AUTOLOAD_CLASS_DIRECTORY . DIRECTORY_SEPARATOR . 'Portal','extension'  => APPLICATION_AUTOLOAD_CLASS_DEFAULT_EXTENSION)
                                       ),
    
                  'Mail'           => array(
                                           array('path'     => APPLICATION_AUTOLOAD_CLASS_DIRECTORY . DIRECTORY_SEPARATOR . 'Mail','extension'  => APPLICATION_AUTOLOAD_CLASS_DEFAULT_EXTENSION)
                                       ),
    
                  'Graph'           => array(
                                           array('path'     => APPLICATION_AUTOLOAD_CLASS_DIRECTORY . DIRECTORY_SEPARATOR . 'Graph','extension'  => APPLICATION_AUTOLOAD_CLASS_DEFAULT_EXTENSION)
                                       ),
    
                  'Payment'           => array(
                                           array('path'     => APPLICATION_AUTOLOAD_CLASS_DIRECTORY . DIRECTORY_SEPARATOR . 'Payment','extension'  => APPLICATION_AUTOLOAD_CLASS_DEFAULT_EXTENSION)
                                       ),
    
                  'Cache'           => array(
                                           array('path'     => APPLICATION_AUTOLOAD_CLASS_DIRECTORY . DIRECTORY_SEPARATOR . 'DAO/Cache','extension'  => APPLICATION_AUTOLOAD_CLASS_DEFAULT_EXTENSION)
                                       ),
    
                  'Form'            => array(
                                           array('path'     => APPLICATION_AUTOLOAD_CLASS_DIRECTORY . DIRECTORY_SEPARATOR . 'Form','extension'  => 'php')
                                       ),
    
                  'Facade'          => array(
                                            array('path'     => APPLICATION_AUTOLOAD_CLASS_DIRECTORY . DIRECTORY_SEPARATOR . 'Facade','extension'  => APPLICATION_AUTOLOAD_CLASS_DEFAULT_EXTENSION)
                                      ),
    
                  'Action'          => array(
                                            array('path'     => ROOT_PATH. DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'action','extension'  => 'php')
                                      ),
    
                  ''                => array(
                                            array( 'path'    => APPLICATION_APP_PATH.DIRECTORY_SEPARATOR.'/controllers','extension' => 'php' ),
                                            array( 'path'    => APPLICATION_AUTOLOAD_CLASS_DIRECTORY . DIRECTORY_SEPARATOR . 'Facade','extension'  => 'php')
                                       )
        )
));