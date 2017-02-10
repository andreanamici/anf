<?php


$function_override_container = array();

/**
 * Effettua l'override di una function
 * 
 * @param String  $functionName     Nome della function da sovrascrivere
 * @param Closure $closureFunction  Callback da applicare
 * 
 * @return Boolean
 */
function function_override($functionName,Closure $closureFunction)
{       
    global $function_override_container;
    
    if(isset($function_override_container[$functionName]))
    {
        unset($function_override_container[$functionName]);
    }
    
    $function_override_container[$functionName] = $closureFunction;
    
    return true;
}


/**
 * Esegue una function, controllando che non sia stata "sovrascritta" tramite function_override().
 * Se la function non è stata mai sovrascritta ma esiste, la esegue lo stesso
 * 
 * @param String $functionName Nome della function
 * @param ...    Parametri da passare alla function
 * 
 * @return Mixed
 */
function function_override_call()
{
    global $function_override_container;
    
    $params       = func_get_args();
    $functionName = $params[0];
    $functionParameters = array_slice($params, 1,count($params)-1);
    $function           = null;
    
    if(isset($function_override_container[$functionName]))
    {
       $function = $function_override_container[$functionName];
    }
    else if(function_exists($functionName))
    {
       $function = $functionName;
    }
    
    if($function)
    {
        return call_user_func_array($function,$functionParameters);
    }
    
    return getApplicationKernel()->throwNewException(90239723039, 'La function '.$functionName.' non esiste e non è stata mai sovrascritta con function_override()');
}