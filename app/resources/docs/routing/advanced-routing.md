ANF ROUTING: Advanced
=======

You can add manualy routing rules directly to the **Application_Routing** services:

```

    anf('routing')->addRoutingMap('_myroute',array(

            'path'      => 'path/to/my/route',
            'action'    => 'foo',
            'method'    => 'bar'

    ), true)

```

In this example, we are putting a route inside the service, the thrid parameter is the flag "store cache", to TRUE

