ANF ROUTING: Advanced
=======

You can add manualy routing rules directly to the **Application_Routing** services:

```php

    anf('routing')->addRoutingMap('_myroute',array(

            'path'      => 'path/to/my/route',
            'action'    => 'foo',
            'method'    => 'bar'

    ), true)

```php

In this example, we are putting a route inside the service, the thrid parameter is the flag "store cache", to TRUE

