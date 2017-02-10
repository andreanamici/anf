<?php

/**
 * Yaml parsing/dumping functions
 */
require_once dirname(__FILE__).'/Spyc.php';
   

if(!function_exists('yaml_load'))
{
   /**
    * Return Array from yaml
    * 
    * @param String $yamlString  yaml content
    * 
    * @return Array
    */
   function yaml_load($yamlString)
   {
      return spyc_load($yamlString);
   }
}

if(!function_exists('yaml_load_file'))
{
   /**
    * Return Array from yaml file
    * 
    * @param String $yamlFile file yaml
    * 
    * @return Array
    */
   function yaml_load_file($yamlFile)
   {
      if(!file_exists($yamlFile))
      {
          return null;
      }
      
      $array = spyc_load_file($yamlFile);
      
      if(isset($array["imports"]))
      {
         foreach($array["imports"] as $resource)
         {
            $resourceAbsolutePath = dirname($yamlFile).'/'.$resource["resource"];
            
            if(!file_exists($resourceAbsolutePath))
            {
               return getApplicationKernel()->throwNewException(193123924892423, 'Questa risorsa '.$resource["resource"].' indicata nel file '.$yamlFile.' non è valida!');
            }
            
            $array = array_merge(yaml_load_file($resourceAbsolutePath),$array);
         }
         
         unset($array["imports"]);
      }
      
      return $array;
   }
}


if(!function_exists('yaml_dump'))
{
   /**
    * Return yaml
    * 
    * @param Mixed $data data to convert yaml 
    * 
    * @return String
    */
   function yaml_dump($data)
   {
      $yamlString =  spyc_dump($data);
      return $yamlString;
   }
}


if(!function_exists('yaml_dump_file'))
{
   /**
    * Write file from yaml Data
    * 
    * @param String $filePath file 
    * 
    * @return Array
    */
   function yaml_dump_file($data,$filePath)
   {      
      if(!file_exists($filePath))
      {
          mkdir($filePath,0777,true);
      }
      
      if(is_dir($filePath))
      {
          $filePath = $filePath.'/'.uniqid().'.yml';
      }
      
      return file_put_contents($filePath, yaml_dump($data));
   }
}