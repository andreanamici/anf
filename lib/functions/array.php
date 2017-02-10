<?php


if(!function_exists('array_strpos'))
{
   /**
    * Controlla che la stringa, o parte di essa  ricercata sia presente nell'array
    * 
    * @param Array   $array  Array sul quale effettuare la ricerca
    * @param String  $needle Stringa ricercata
    * 
    * @return Boolean
    */
   function array_strpos(array $array,$needle)
   {

      if(strlen($needle)==0){
         return false;
      }

      if(is_array($array) && count($array)>0)
      {
         foreach($array as $key => $value)
         {                  
            if(strpos(strtolower($needle),strtolower($value))!==false || strpos(strtolower($value),strtolower($needle))!==false){
               return true;
            }
         }
      }

      return false;
   }
}


if(!function_exists('array_dot_notation'))
{
    /**
     * Naviga l'array sfruttando la "dot" notation, es: key1.key2.key3
     * 
     * @param array  $array         Array
     * @param String $dotNotation   Notation
     * @param Mixed  $default       Valore di default
     * 
     * @return Mixed
     */
    function array_dot_notation(array $array,$dotNotation,$default = false)
    {
        $indexs     = strstr($dotNotation,".")!==false ? explode(".",$dotNotation) : array($dotNotation);
        $keytotal   = count($indexs);
        $keycount   = 0;
        
        foreach($indexs as $key)
        {
           $keycount++;
           
           if(is_array($array))
           {
               if(isset($array[$key]))
               {
                  if($keycount==$keytotal)
                  {
                    return $array[$key];
                  }
                  else
                  {
                     $array = $array[$key];
                  }
               }
               else
               {
                  return $default;
               }
           }
        }
        
        return $default;
    }
}

if(!function_exists('array_dot_notation_set'))
{
    /**
     * Naviga l'array sfruttando la "dot" notation, es: key1.key2.key3 ed aggiorna il valore
     * 
     * @param array  $array         Array
     * @param String $dotNotation   Notation
     * @param Mixed  $value         Valore aggiornato
     * 
     * @return Mixed
     */
    function array_dot_notation_set(array &$array,$dotNotation,$value)
    {
        $indexs     = strstr($dotNotation,".")!==false ? explode(".",$dotNotation) : array($dotNotation);
        $keytotal   = count($indexs);
        $keycount   = 0;

        foreach($indexs as $key)
        {
           $keycount++;
           
           if(is_array($array))
           {
               if(isset($array[$key]))
               {
                  if($keycount==$keytotal)
                  {
                     $array[$key] = $value;
                  }
                  else
                  {
                     $array = $array[$key];
                  }
               }
               else
               {
                   $array[$key] = $value;
               }
           }
        }
        
        return false;
    }
}

if(!function_exists('array_extend'))
{
   /**
    * Estende due array tra di loro estendendo l'array_merge nativo di php
    * 
    * @param array $a array da estendere
    * @param array $b array che contiene i valori estesi
    * 
    * @return array
    */
   function array_extend(array $a, array $b) 
   {
        foreach($b as $k=>$v) 
        {
            if( is_array($v) ) 
            {
                if( !isset($a[$k]) ) 
                {
                    $a[$k] = $v;
                }
                else 
                {
                    $a[$k] = array_extend($a[$k], $v);
                }
            }
            else 
            {
                $a[$k] = $v;
            }
        }
        
        return $a; 
   }
}