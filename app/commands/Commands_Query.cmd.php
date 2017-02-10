<?php

/**
 * Questo command esegue una sql query
 */
class Commands_Query extends \Abstract_Commands
{
    
    public function getName()
    {
        return 'dbal:query';
    }
    
    public function getDescription()
    {
        return 'Esegue una query: indicare l\'opzione "query" e usare eventuali placeholder della query utilizzando i parametri del command es: dbal:query --query="select * from table where field = ? and field2 = ? and field3 = ? " param1 param2 param3 ';
    }
    
    public function doProcessMe()
    {        
        try
        {
            $query       = $this->getOption('query',$this->getOption('q',false));
            
            if(!$query)
            {
                return $this->throwNewException(349076903450345, 'Nessuna query specificata!');
            }
            
            $database =  $this->getApplicationKernel()->database;
                        
            $resultQuery = $database->exeQuery($query, $this->getParams()->getArrayCopy());
            
            $response    = $resultQuery ? "Query OK" : "Query KO";
                        
            if($this->getOption('fetch_rows'))
            {
                $response.=" <pre>Records: ".print_r($database->fetchArrayResultSet(),true).'</pre>';
            }            
        }
        catch(\Exception $e)
        {
            $response = "Query KO: ".$e->getMessage();
        }
        
        return $this->setResponse($response);
    }
    
}