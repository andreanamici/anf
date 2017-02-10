<?php

/**
 * @Route({
 * 
 *    "_index": {
 *       "path":   "/",
 *       "action": "index"
 *    },
 * 
 *    "_hello": {
 *       "path":   "/index/hello/{name}",
 *       "action": "index",
 *       "method": "hello",
 *       "params": {
 *         "name": "(:[string])"
 *       }
 *    }
 * 
 * })
 */
class Action_index extends Abstract_ActionObject
{
    public function doProcessMe(\Application_ActionRequestData $requestData)
    {           
        return $this->setResponse(array(
                    'name'   => 'everyone',
                    'date'   =>  date('d/m/Y')
               ));
    }
    
    
    public function doHello(\Application_ActionRequestData $requestData, $name)
    {       
        return $this->setResponse(array(
               'date'   =>  date('d/m/Y'),
               'name'   =>  $name
        ));
    }
    
}
