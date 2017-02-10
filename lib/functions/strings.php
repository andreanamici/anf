<?php


if(!function_exists("strtolowercase"))
{
    /**
     * Trasforma una stringa in lower_case
     * 
     * @param String $string Stringa
     * 
     * @return String
     */
    function strtolowercase($string) 
    {
        return preg_replace_callback('/(^|[a-z])([A-Z])/', function($matches){
                      return strtolower(strlen($matches[1]) ? $matches[1].'_'.$matches[2] : $matches[2]);
               },$string);
    }
}


if(!function_exists("strtocamelcase"))
{
    /**
     * Trasforma una stringa in camelCase
     * 
     * @param String $string
     * 
     * @return String
     */
    function strtocamelcase($string) 
    {
        return preg_replace('/\s/','',preg_replace_callback('/(^|_)([a-z])/', function($matches){
                      return strtoupper($matches[2]);
               },$string));
    }
}