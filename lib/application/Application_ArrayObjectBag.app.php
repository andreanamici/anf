<?php

/**
 * Estende l'Application_ArrayBag fornendo un interfaccia con funzionalità estese con le funzioni native array_*
 * 
 * @method array array_merge  Merge one or more arrays
 * @method array array_change_key_case($case = 'CASE_LOWER') Changes the case of all keys in an array
 * @method int   array_rand($num = 1) Pick one or more random entries out of an array
 * @method array array_unique($sort_flags = 'SORT_STRING') emoves duplicate values from an array
 * @method array array_intersect(array $array2,array $_ = null) Computes the intersection of arrays
 * @method array array_intersect_key(array $array2, array $_ = null) Computes the intersection of arrays using keys for comparison
 * @method array array_uintersect(array $array2, array $_ = null, callable $value_compare_func) Computes the intersection of arrays, compares data by a callback function
 * @method array array_intersect_assoc(array $array2, array $_ = null) Computes the intersection of arrays with additional index check
 * @method array array_uintersect_assoc(array $array2, array $_ = null, callable $value_compare_func) Computes the intersection of arrays with additional index check, compares data by a callback function
 * @method array array_intersect_uassoc(array $array2, array $_ = null, callable $key_compare_func) Computes the intersection of arrays with additional index check, compares indexes by a callback function
 * @method array array_uintersect_uassoc(array $array2, array $_ = null, callable $value_compare_func, callable $key_compare_func) Computes the intersection of arrays with additional index check, compares data and indexes by a callback functions
 * @method array array_diff(array $array2, array $_ = null) Computes the difference of arrays
 * @method array array_diff_key(array $array2, array $_ = null) Computes the difference of arrays using keys for comparison
 * @method array array_diff_ukey(array $array2, array $_ = null, callable $key_compare_func) Computes the difference of arrays using a callback function on the keys for comparison
 * @method array array_udiff(array $array2, array $_ = null, callable $value_compare_func) Computes the difference of arrays by using a callback function for data comparison
 * @method array array_diff_assoc(array $array2, array $_ = null) Computes the difference of arrays with additional index check
 * @method array array_udiff_assoc(array $array2, array $_ = null, callable $value_compare_func) Computes the difference of arrays with additional index check, compares data by a callback function
 * @method array array_diff_uassoc(array $array2, array $_ = null, callable $key_compare_func) Computes the difference of arrays with additional index check which is performed by a user supplied callback function
 * @method array array_udiff_uassoc(array $array2, array $_ = null, callable $value_compare_func, callable $key_compare_func) Computes the difference of arrays with additional index check, compares data and indexes by a callback function
 * @method number  array_sum() Calculate the sum of values in an array
 * @method number  array_product() Calculate the product of values in an array
 * @method array array_filter(callable $callback = null) Filters elements of an array using a callback function
 * @method array array_map(callable $callback, array $array1, array $_ = null) Applies the callback to the elements of the given arrays
 * @method array array_chunk(array $array, $size, $preserve_keys = false) Split an array into chunks
 */
class Application_ArrayObjectBag extends Application_ArrayBag
{   
   /**
    * Estende gli ArrayBag con le funzionalità native delle function array_*
    * 
    * @param array $array                Dati da gestire
    * @param Array $offsetsCallabacks    Array callable invocate in base alle operazioni effettuate sull'ArrayObject (1 sola callable alla volta per tipologia)
    * 
    * @return Boolean
    */
   public function __construct(array $array = array(),array $offsetsCallabacks = array())
   {
      parent::__construct($array,$offsetsCallabacks);
   }
   
   /**
    * Permette di chiamare tutte le funzini array_* su questo oggetto, omettendo l'array soggetto della function
    * 
    * @param String $func Nome del metodo
    * @param Array  $argv Parametri
    * 
    * @return Mixed
    * 
    * @throws BadMethodCallException
    */
   public function __call($func, $argv)
   {
        if (!is_callable($func) || substr($func, 0, 6) !== 'array_')
        {
            throw new BadMethodCallException(__CLASS__.'->'.$func);
        }
        
        if($func == 'array_key_exists')
        {
           throw new BadMethodCallException(__CLASS__.'->'.$func);
        }
        
        if($func == 'array_combine')
        {
           throw new BadMethodCallException(__CLASS__.'->'.$func);
        }
        
        return call_user_func_array($func, array_merge(array($this->getAll()), $argv));
   }
   
}

