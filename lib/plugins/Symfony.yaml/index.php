<?php

/**
 * Yaml parsing/dumping functions
 */

use \Symfony\Component\Yaml\Yaml;

if(!function_exists('sf_yaml_load'))
{
   /**
    * Return Array from yaml
    * 
    * @param String $yamlString  yaml content
    * 
    * @return Array
    */
   function sf_yaml_load($yamlString)
   {      
      $fileName        = uniqid().'.yml';
      $configsCacheDir = getApplicationKernel()->getApplicationConfigs()->getConfigsCacheDirPath();
      $filePath        = $configsCacheDir.'/'.$fileName;
      
      if(!file_exists($configsCacheDir))
      {
          mkdir($configsCacheDir,0777,true);
      }
      
      file_put_contents($filePath, $yamlString);
      
      $array    = Yaml::parse($filePath);
      
      unlink($filePath);
      
      return $array;
   }
}

if(!function_exists('sf_yaml_load_file'))
{
   /**
    * Return Array from yaml file
    * 
    * @param String $yamlFile file yaml
    * 
    * @return Array
    */
   function sf_yaml_load_file($yamlFile)
   {  
      $array =  Yaml::parse($yamlFile);
      
      if(isset($array["imports"]))
      {
         foreach($array["imports"] as $resource)
         {
            $resourceAbsolutePath = dirname($yamlFile).'/'.$resource["resource"];
            
            if(!file_exists($resourceAbsolutePath))
            {
               return getApplicationKernel()->throwNewException(193123924892423, 'Questa risorsa '.$resource["resource"].' indicata nel file '.$yamlFile.' non è valida!');
            }
            
            $array = array_merge(sf_yaml_load_file($resourceAbsolutePath),$array);
         }
         
         unset($array["imports"]);
      }
      
      return $array;
   }
}


if(!function_exists('sf_yaml_dump'))
{
   /**
    * Return yaml
    * 
    * @param Mixed $data data to convert yaml 
    * 
    * @return String
    */
   function sf_yaml_dump($data)
   {     
     return Yaml::dump($data);
   }
}


if(!function_exists('sf_yaml_dump_file'))
{
   /**
    * Write file from yaml Data
    * 
    * @param String $filePath file 
    * 
    * @return Array
    */
   function sf_yaml_dump_file($data,$filePath)
   {
      if(!file_exists($filePath))
      {
          mkdir($filePath,0777,true);
      }
      
      if(is_dir($filePath))
      {
          $filePath = $filePath.'/'.uniqid().'.yml';
      }
      
      return file_put_contents($filePath, sf_yaml_dump($data));
   }
}