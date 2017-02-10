<?php

if(!function_exists('resource_url'))
{
    /**
     * Restituisce l'url assoluto di una risorsa in base all'attuale package processato
     * 
     * @param String    $resource_url url risorsa relativa alla cartella public
     * @param String    $package      package di riferimento, default quello usato nella vista
     * @param Boolean   $absolute     indica se il path restituito sarà assoluto o relativo, default relativo
     * 
     * @return String
     */
    function resource_url($resource_url,$package = null, $absolute = false)
    {
        return getApplicationService('templating')->getResourceUrl($resource_url,$package,$absolute);
    }
}

if(!function_exists('resource_path'))
{
    /**
     * Restituisce il path assoluto di una risorsa in base all'attuale package processato
     * 
     * @param String    $resource_url url risorsa relativa alla cartella public
     * @param String    $package      package, default quello usato dal templating
     * @return void
     */
    function resource_path($resource_url, $package = null)
    {
        return getApplicationService('templating')->getResourcePath($resource_url,$package);
    }
}

if(!function_exists('resource_view'))
{
    /**
     * Restituisce il path assoluto di una vista in base all'attuale package processato
     * 
     * @param String    $resource_url url risorsa relativa alla cartella public
     * 
     * @return void
     */
    function resource_view($view,$package = null)
    {
        return getApplicationService('templating')->getViewPath($view,$package);
    }
}


if(!function_exists('render_view'))
{
    
    /**
     * Effettua il render di una vista e restituisce l'output
     * 
     * @param String $view               Path relativo della vista
     * @param Array  $parameters         Parametri passati alla vista
     * @param String $package            Nome del package, default '' (quello attualmente usato)
     * @param String $templateExtension  estensione dei file dei template, default null
     * 
     * @return String
     */
    function render_view($view, array $parameters = array(), $package = '',$templateExtension = null)
    {
        return getApplicationService('templating')->renderView($view,$parameters,$package,$templateExtension);
    }
    
}