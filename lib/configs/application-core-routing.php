<?php

      /* 
         ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 
                                                            ROUTING DEFAULT       
                                                         
          Queste rotte sono quelle che di default permettono diversi meccanismi nativi del framework, come la generazione degli url  
          <action>/<method>, dello switch language etc..  
                                                                         
         ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ 
      */

return array(
        
            '_language_action_method'           => Array('path'        => '/(:action)/(:method)',
                                                         'host'        => '(:lang).'.SITE_DOMAIN,
                                                         'action'      => '{action}',
                                                         'method'   => '{method}',
                                                         'params'      => Array(
                                                             'lang'    => '(:lang)'
                                                         )
                                                      ),
                
            '_language_action'                     => Array('path'        => '/(:action)',
                                                            'host'        => '(:lang).'.SITE_DOMAIN,
                                                            'action'      => '{action}',
                                                            'params'      => Array(
                                                                 'lang'    => '(:lang)'
                                                            )
                                                      ),
                                          
            '_language_domain'                    => Array( 'path'        => '/',
                                                            'host'        => '(:lang).'.SITE_DOMAIN,
                                                            'action'      => ACTION_CNT_ACTION_DEFAULT,
                                                            'params'      => Array(
                                                                'lang'    => '(:lang)'
                                                            )
                                                      ),

    // Action / Method Routing  **********************************************************
    
            '_action_method_param'              => Array(   'path'    => '/(:action)/(:method)',
                                                            'action'  => '{action}',
                                                            'method'  => '{method}',
                                                            'params'  => array(
                                                                'params' => '(:any)'
                                                            )
                                                   ), 
    
            '_action_method'                    => Array( 'path'    => '/(:action)/(:method)',
                                                          'action'  => '{action}',
                                                          'method'  => '{method}'
                                                   ),
        
             '_action'                          => Array(
                                                            'path'        => '/(:action)',
                                                            'action'      => '{action}'
                                                   ),
                                                   
             '_action_index'                      => Array(
                                                                'path'        => '/',
                                                                'action'      => ACTION_CNT_ACTION_DEFAULT
                                                   ),
    
    // HTTP Errors    **********************************************************
    
    
           '_http_status'                          => Array( 'path'         => '/apache/httpstatus/{httpStatus}',
                                                             'action'       => '{httpStatus}',
                                                             'params'       => Array(
                                                                 'httpStatus'     => '(:[numeric])'
                                                             )
                                                      ),
    

    // ANY Request 404 **********************************************************
    
            '_any'                                 => Array(
                                                              'path'          => '(:any)',
                                                              'last'          => true,
                                                              'action'        => 404,
                                                              'position'      => 9999999999
                                                      )
);