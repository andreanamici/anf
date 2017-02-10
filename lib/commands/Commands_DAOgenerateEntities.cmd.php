<?php

/**
 * Questo command genera un entity da una tabella mysql
 */
class Commands_DAOgenerateEntities extends Abstract_Commands
{
   
   public function getName() 
   {
      return 'dao:generate:entities';
   }
   
   public static function getParametersSchema()
   {
      return array('table','package');
   }
      
   
   public function doProcessMe() 
   {
       $dbManager           = $this->getDatabaseManager();
       $tablePrefix         = $dbManager->getConfigurationValue("table_prefix");
       $table               = $this->getParam('table');
       $package        = $this->getParam('package');
       $force               = $this->getOption('force', false);
       
       $mapInfo             = $this->getApplicationAutoload()->getMapInfo('Entities');
      
       $response = "";
       
       if($dbManager->getConfigurationValue('driver')!='mysql')
       {
          return self::throwNewException(90238490238434, 'Questo command è disponibile solamente se si utilizza il driver mysql!');
       }
       
       if(!$table)
       {
          return self::throwNewException(30294023502450234, 'Il parametro table non è definito!');
       }
       
       if(!$mapInfo)
       {
          return self::throwNewException(30294023502450234, 'Non è possibile trovare un path valido in cui storare le Entities');
       }
      
       if(count($mapInfo) > 0 )
       {
         $mapInfo = $mapInfo[0];
       }
       
       if($package)
       {
          $mapInfo = array(
              'path'       => $this->getApplicationKernel()->getPackageInstance($package)->getLibrariesPath().'/Entities',
              'extension'  => $mapInfo["extension"]
          );
       }
                     
       $entityName         = $this->getEntityName($table,$tablePrefix);
      
       $entityPath         = $mapInfo["path"].'/'.$entityName.'.'.$mapInfo["extension"];
               
       if(file_exists($entityPath) && !$force)
       {
          return $this->setResponse('Questa '.$entityName.' è già presente in '.$entityPath);
       }
       
       $entityFields       = $dbManager->getTableFields($table);
       
       $staticfields       = implode("\",\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\"",array_keys($entityFields));
       
       $publicSetFunctions = "";
       $publicGetFunctions = "";
       $properties         = "";
       
       foreach($entityFields as $field => $info)
       {
          $setfunctionname    = $this->getBuildMethodName($field,'set');
          $getfunctionname    = $this->getBuildMethodName($field,'get');
          
          $propertyComment    = "/**";
          $setMethodComment   = "/**";
          $getMethodComment   = "/**";
          
          if(strlen($info['column_comment']) > 0)
          {
             $propertyComment.="\n   * ".$info['column_comment'];
             $setMethodComment.="\n   * Imposta ".$info['column_comment'];
             $getMethodComment.="\n   * Restituisce ".$info['column_comment'];
          }
          else
          {
             $propertyComment.="\n   * campo ".$field;
             $setMethodComment.="\n   * Imposta il campo ".$field;
             $getMethodComment.="\n   * Restituisce il campo ".$field;
          }
          
$properties.=<<<EOF
  
  {$propertyComment}
   * @var {$info['data_type']}
   */
   protected \${$field} = self::NULL;
  
EOF;
          
$publicSetFunctions.= <<<EOF
  
  {$setMethodComment}
   * @param {$info['data_type']} \$value  {$info['column_comment']}
   * @return {$entityName}
   */
   public function {$setfunctionname}(\$value)
   { 
      \$this->{$field} = \$value;
      return \$this;
   }
     
EOF;

$publicGetFunctions.= <<<EOF
  
   {$getMethodComment}
    * @return {$info['data_type']}
    */
    public function {$getfunctionname}() 
    {
        return \$this->{$field};
    }
                            
EOF;

      }

      $entitySourceFile = <<<EOF
<?php

/**
 * Questa entity gestisce la tabella {$table}
 */
class {$entityName} extends Abstract_Entities
{
   public static \$_class_name       = __CLASS__;
   
   public static \$_table_name       = "{$table}";

   protected static \$_fields        = Array( "{$staticfields}" );
      
   {$properties}
         
   {$publicSetFunctions}
         
   {$publicGetFunctions}
   
   /**
    * Entità {$entityName}, gestisce la tabella {$table}
    */
   public function __construct() 
   {
      return \$this->init();
   }
}   

EOF;
   
      if(file_put_contents($entityPath, $entitySourceFile)!==false)
      {
         $response =' >> entità generata in '.$entityPath;         
      }
      else
      {
         $response = 'Non è possibile generare l\'entity per la tabella '.$table;
      }
      
      return $this->setResponse($response);
   }
   
   
   
   private function getEntityName($table,$tablePrefix)
   {
        $tmpArr    = explode("_",str_replace($tablePrefix,"",$table));
        $entName   = "";

        if(is_array($tmpArr) && count($tmpArr)>0)
        {
           foreach($tmpArr as $value){
              $entName.=ucfirst($value); 
           }
        }
        else{
           $entName.=ucfirst($field); 
        }

        return "Entities_".$entName;
   }


   private function getBuildMethodName($field,$type)
   {
        $tmpArr    = explode("_",$field);
        $setMethod = $type;

        if(is_array($tmpArr) && count($tmpArr)>0)
        {
           foreach($tmpArr as $value)
           {
              $setMethod.=ucfirst($value); 
           }
        }
        else
        {
           $setMethod.=ucfirst($field); 
        }

        return $setMethod; 
   }
    
   private function getValueString($value)
   {
          $stringValue    = "NULL";
          
          switch(gettype($value))
          {
             case 'int':
             case 'double':   
                                 $stringValue = $value; 
                              break;
                           
             case 'boolean':     $stringValue = $value ? "true" : "false";
                              break;
             case 'NULL':
                                 $stringValue = "NULL";
                              break;
             case 'string':
                                 
                               $stringValue = is_numeric($value) ? $value : "'{$value}'";
                                 
                              break;   
          }
          
          return $stringValue;
   } 
}