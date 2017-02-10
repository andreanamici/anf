<?php

/**
 *  Area test di anf
 * 
 *  @author Andrea Namici 
 */

namespace
{    
    $kernel = require_once 'app/__bootstrap.php';/*@var $kernel \Application_Kernel*/
    
    $kernel->initMeCLI('dev',true);
        
    $kernel->routing->unregisterAllRoutes()
                    ->addRoutingMap('_index', array(

                                'path'          => '/',
                                'action'        => function($kernel)
                                {
                                        var_dump( $kernel);
        
                                        die("ok!");
                                }
                    ));

        
    $kernel->run();
}
//
//namespace webDefault\Controllers
//{
//    class WelcomeController extends \Abstract_ActionObject
//    {        
//        public function doHello()
//        {
//            echo $this->getApplicationKernel()->get('templating.smarty')->drawString('hello my name is {$name}',array('name'=>'john'));
//            
//            $this->setResponse(array('name'=>'andrea!'));
//            
//            die("ok!");
//            
//        }
//    }
//
//}
//
//namespace webMobile\Controllers
//{
//    class TestController extends \Abstract_ActionObject
//    {   
//        public function hello()
//        {
//            echo "hello test!";
//            die();
//        }
//    }
//}
//
//namespace
//{   
//    $kernel->get('@routing')->unregisterAllRoutes()
//            
//           ->addRoutingMap('_action', array(
//
//                        'path'          => '(:action)',
//                        'package'       => false,
//                        'action'        => '{action}'
//            ))
//            
//            ->addRoutingMap('_action_subaction', array(
//
//                        'path'          => '(:action)/(:subaction)',
//                        'package'       => false,
//                        'action'        => '{action}/{subaction}'
//            ))
//            
//           ->addRoutingMap('_hello', array(
//
//                        'path'          => '/hello',
//                        'package'       => false,
//                        'action'        => 'webDefault\Controllers\WelcomeController::hello'
//            ))
//
//            ->addRoutingMap('_hello_mobile', array(
//
//                        'path'          => '/hellomobile',
//                        'package'       => false,
//                        'action'        => 'webMobile\Controllers\TestController::hello'
//
//            ))
//            
//            ->addRoutingMap('_hello_name', array(
//
//                        'path'          => '/hello{slug}{name}',
//                        'package'       => false,
//                        'action'        => function(\Application_ActionRequestData $actionRequestData)
//                        {
//                            $templating = $this->getService('templating.rain');/*@var $templating \TemplateEngine_RainTpl*/
//                            
//                            $templating->setTemplateDirectory(ROOT_PATH.'/app/resources');
//                            $templating->setTemplateToLoad(array('hello'))
//                                       ->setTemplateFileExtension('tpl')
//                                       ->setTemplateParams(array(
//                                           'name' => $actionRequestData->getVal('name')
//                                       ))
//                                       ->configureTplEngine();
//                            
//                            $templating->drawTemplate();
//
//                            $templating->view();
//                            
//                            $this->getService('kernel')->closeKernel();
//                        },
//                        'params'        => array(
//                            'name'  => '(:[string])',
//                            'slug'  => '[\/{0,1}]'
//                        ),
//                        'defaults'      => array(
//                            'name'  => 'who are you?',
//                            'slug'  => '/'
//                        )
//
//            ))
//
//            ->addRoutingMap('_any', array(
//
//                        'path'          => '(:any)',
//                        'package'       => false,
//                        'action'        => 302,
//                        'where'         => '_hello_name'
//
//            ));
//  
//                        
//  $kernel->run();
//}

