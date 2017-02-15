ANF ROUTING: Introduction
=======

**anf** Routing service help you to define and manage routes inside the application. 
You can define routes directly in php or yml files, as you preferer, the framework can mix different route source files in a unique routing config file, without
stop you to build fast and scalable routing configurations.

Defauls routes are loaded in these files by the **hook** stored in **app/hooks/Hooks_ApplicationConfigs.php**

 * app/configs/application-configs.php
 * app/configs/application-configs.yml

The hooks mechanism use the **\Application_Routing** and **\Application_Configs** component and load and cache application routes.
In development Enviroment, each change of these files reload the configuration of routes, while in production enviroment routes are cached until you clear the cache using command **cache:clear --env=<env>**

Now you can see [How to define a route](how-to-define-routes.md)



