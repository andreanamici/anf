ANF ROUTING: How to define a routes?
=======

You can create your own routing path and process http response by the ActionObject or ActionController.

Example using **app/config/application_routing.php**

```

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

```


or in **app/application_routing.yml**:

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

Inside routing you can bind **params** that will pass to your Controller/ActionObject.
When you define a params, you must declare it under "params" and specify a validation:

* (:[string])                 => [A-z0-9]+   This is a string, 
* (:[chars])                  => [A-z\-\_]+  This is signle digit
* (:[numeric])                => This is a number, ^[0-9]+$
* (:[slug])                   => ([\/]),
* (:[string])                 => ([A-z0-9\-\_]+),
* (:[string-lower])           => ([A-z0-9\-\_]+) only available string lowercase,
* (:[string-upper])           => ([A-z0-9\-\_]+) only available string uppercase,
* (:[any])                     => Any type of digit

In this case, the route "_welcome_name", will call class under directory **controllers\WelcomeController**
and execute method "helloName". This method has the first argument the **\Application_ActionRequestData** and the seconth argument will be
the variable $name binded in the routing.


## How to generate Url or Path ? ##

Inside an actionObject/ActionController, if you want to generate an URL / Path for route "_welcome_name"

```

    $urlRelative = $this->generateUrl('_welcome_name', ['name' => 'andrea']);

    $urlAbsolute = $this->generateUrl('_welcome_name', ['name' => 'andrea'], true);

```

Inside an actionObject/ActionController, if you want to generate an URL / Path for route "_welcome_name"

