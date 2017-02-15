# ANF - Alternative Framework #

**anf** is a php framework born from the experience and the need for a user-friendly, flexible and easily extensible framework.

The framework now allows immediate use out of the box without too much configuration.

You can use "anf" to create small and large applications, even using it as a micro-framework, registering faster routes - actions (Closure supports) to process the 
actions useful for example in an application Restfull

## Supports: ##

* HMVC pattern
* Third party dependency by composer
* Namespaces autoloading support
* Caching Layer, usign APC, memcached o file caching
* Session Handler ready
* Multiple environment, native app_dev.php (for debug mode), app.php (production) and app_test.php (for unit test)
* Multiple database connection management
* Extendible by packages multiple applications
* Plugins support, you can create a single plugin and use in your application
* Core extendend by "hooks"
* i18n support translations with native support of "php", "xml", "yml" or other extensions files
* Routing system
* Services and dependency injection
* Multiple template engine, native support of "RainTPL", "smarty" and "Twig"
* Forms validation by CodeIgniter porting of class "Form_Validation", adapted in anf
* CLI interface via the commands

## Native Packages ##

* webProfiles: package for create a profiles toolbar 
* webUtility: package for use phpmyadmin tools

### Quick start: How does it work?

anf support two ways to process an action and return a response valid to be release by kernel:

* Create an ActionObject
* Create an Controller

____________________________________________________
#### Create an ActionObject class in app/action

```php
<?php

class Action_myaction extends Abstract_ActionObject
{
   public function doProcessMe(\Application_ActionRequestData $requestData)
   {
       return $this->setResponse(array(
                    'foo'    => 'bar'
               ));         
   }
}
```

#### Create a view in app/resources/views/index.php and use variable returned by ActionObject

```php
<html>
    <title>anf - Alternative Framework</title>    
    <body>
        <p>
            Hi <?php echo $foo;?>
        </p>
    </body>
</html>
```

____________________________________________________
#### Creating an ActionController extended core \Application_Controller

Each controller must return these type of data:

* String convertend in HTML response
* \Application_ControllerResponseData

```php
<?php

use plugins\FormValidationEngine\Form\FormValidationEngine;

class MyController extends \Application_Controller
{
   public function doHello(\Application_ActionRequestData $requestData)
   {
       return $this->render('index',array(
                    'foo'    => 'bar'
              ));         
   }

   /**
    *
    * anf support service injection in controller and in an ActionObject, simply set method variable name like service name.
    * If your service name is "foo" the variable name should be "$foo". Type hinting is not required, but can help you with editor autocomplete!
    *
    */
   public function sayGoodbye(\Application_ActionRequestData $requestData, FormValidationEngine $form_validation, \Application_SessionManager $session)
   {
        if($requestData->isMethodPost())        
        {   
            $form_validation->set_rules('name','Nome','required|callback_checkName', 'indicare un nome valido');
            $name = $requestData->getPost()->getIndex('name');
            
            if(!$form_validation->run())
            {
                $session->addFlashMessageError($this->_t('WELCOME_WRONG_DATA'));
            }               
            else
            {
                $session->addFlashMessage($this->_t('WELCOME_CORRECT_DATA', array('{{name}}' => $name)));
            }            
       }

       return $this->render('index',array(
                    'foo'    => 'bar'
              ));         
   } 
}
```

You can create your own routing path and process http response by the ActionObject or ActionController in app/config/application_routing.php

<?php

return array(
 
    '_welcome_last' => array(
        'path'      => '/welcome/last',
        'action'    => 'controllers\WelcomeController::helloName'
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

or in app/application_routing.yml:

```

_welcome_last: 
        path: /welcome/last
        action: "controllers\WelcomeController::helloName"
        params:
            name: "(:[string])"

_welcome_name: 
        path: /welcome/{name}
        action: "controllers\WelcomeController::helloName"
        defaults:
            name: guest
        params:
            name: "(:[string])"
```

## How to access to anf services outside Controller / ActionObject ?

```php

$service = anf(<service_name>)

//Example SessionManager service:

$sessionManager = anf('session');

debug_print_r_die($sessionManager->getAll());

```php


## You can read the [Summary](app/resources/doc/SUMMARY.md) for all examples and user guide


## Environments Requirements:

* php 5.4+
* Lamp (linux) or Windows XAMPP

## Versioning

We use [Git](http://semver.org/) for versioning. 

## Authors and other Contributors

* **Andrea Namici** - *Author* - (https://bitbucket.org/andrea_namici) since 01/2015
* **Luca Tariciotti** - *Controbutor* since 02/2017

## License

This project is licensed under the MIT License - see the [license.txt](license.txt) file for details