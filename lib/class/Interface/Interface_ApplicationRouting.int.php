<?php


/**
 * Interfaccia del gestore del Routing
 * 
 * @author andrea.namici
 */
interface Interface_ApplicationRouting extends Interface_HttpStatus
{
    /**
     * Pattern per determinare se il nome della rotta è valido
     * @var String
     */
    const ROUTING_NAME_PATTERN              = '/^\_[a-z\_-]+$/';

    /**
     * Nome del file di configurazione delle rotte non compilate
     * @var String
     */
    const ROUTING_CONFIG_MAP_FILE_NAME      = 'application-routing';

    /**
     * Nome del file di configurazioni delle rotte
     * @var String
     */
    const ROUTING_CONFIG_COMPILED_FILE_NAME = 'application-routing-compiled';

    /**
     * Stringa di test
     * @var String
     */
    const ROUTING_TEST_REGEXPR_STRING       = 'loremipsumstring';

    /**
     * Pattern per convertire una callableString in un array callable reale
     * @var String
     */
    const ACTION_CALLABLE_STRING_PATTERN    = '/([A-z\_\\\{\}]+)\:\:([A-z\_\{\}]+)/';
   
    /**
     * Host della rotta
     * @var String
     */
    const ROUTE_PART_HOST = 'host';
    
    /**
     * Path della rotta
     * @var String
     */
    const ROUTE_PART_PATH = 'path';
    
    /**
     * Valore di default qualora il parametro di default sia vuoto
     * Tale stringa sarà utile per settare il valore a NULL
     */
    const ROUTE_DEFAULT_PARAMETER_EMPTY = '__EMPTY__';
    
    public function elaborateRequestRouting();
    
    
    public function generateUrl($where,array $routeData = Array(),$absolute = false);
    
}
