<?php

/**
 * Class Astratta per i manager delle Entità/Array
 * 
 * Questa classe mette a disposizione dei metodi utili per la gestione delle Entities, per l accesso al db e ai sistema di caching integrati
 */
abstract class Abstract_EntitiesManager
{
    
   use Trait_DAO,Trait_Singleton,Trait_ObjectUtilities;
   
   use Trait_ApplicationConfigs,Trait_ApplicationKernel;
      
   /**
    * Nome della classe del manager
    * @var String
    */
   public static $_class_name         = __CLASS__;
   
   /**
    * Nome della tabella sql da gestire
    * @var String
    */
   protected   $_class_sql_table_name = null;
   
   /**
    * Nome id campo tabella sql
    * @var String
    */
   protected   $_class_sql_table_id   = null;
   
   /**
    * Nome della classe Entity gestita
    * @var String
    */
   protected   $_class_ent_name       = null;

   
   /**
    * Imposta la tabella Sql sulla quale Lavorare
    * 
    * @param String $val table
    * 
    * @return Abstract_EntitiesManager
    */
   protected function setClassSqlTableName($val)
   {
        $this->_class_sql_table_name = $val;
        return $this;
   }
   
   /**
    * Imposta il nome della chiave primaria del db
    * 
    * @param String $val id table field name
    * 
    * @return Abstract_EntitiesManager
    */
   protected function setClassSqlTableId($val)
   {
       $this->_class_sql_table_id   = $val;
       return $this;
   }
   
   /**
    * Imposta il nome della classe figlia che sta chiamando questa classe
    * 
    * @param String $val Nome classe entità
    * 
    * @return Abstract_EntitiesManager
    */
   protected function setClassName($val)
   {
       $this->_class_ent_name = $val;
       return $this;
   }
   
   
   /**
    * Class Astratta per i metodi base dei manager Entità
    * 
    * Questa classe Mette a disposizione dei metodi utili ai manager
    *
    * @param String  $childClassName         Nome Classe Entità
    * @param String  $childClassSqlTableName Nome tabella sql classe figlia
    * @param String  $childClassSqlTableId   Nome campo id tabella sql classe figlia
    * 
    * @return boolean 
    */
   protected function init($childClassName,$childClassSqlTableName = null,$childClassSqlTableId = null)
   {
       $this->initDAO();
      
       if(is_null($childClassSqlTableName))
       {
          $childClassSqlTableName = $this->_class_sql_table_name;
       }
       
       if(is_null($childClassSqlTableId))
       {
          $childClassSqlTableId = $this->_class_sql_table_id;
       }
       
       $this->setClassName($childClassName)
            ->setClassSqlTableName($childClassSqlTableName)
            ->setClassSqlTableId($childClassSqlTableId);
       
       return true;
   }
   
   
   
   /**
    * Aggiunge un nuovo record nel DB
    * 
    * @param Array  $insertData   Entità/Array  da inserire
    * @param String $insertMode   Metodo Inserimento, default null. Valori Accettati: IGNORE | REPLACE
    * @param String $priority     Priorità inserimento, default null, Valori Accettati: LOW_PRIORITY | HIGHT_PRIORITY
    * 
    * @return Mixed Id anagrafica db
    */
   protected function add($insertData,$insertMode = null, $priority = null)
   {
       if(!empty($insertData))
       {
          if(is_object($insertData) && ($insertData instanceof Abstract_Entities))
          {
              $insertData->__beforeInsert($this);
              
              $insertData = $insertData->toArray();
          }
          
          $insertData = $this->filterEmptyArrayValues($insertData);
          
          $stmt      = $this->_db->BuildSqlStatementInsert($this->_class_sql_table_name,$insertData,$insertMode,$priority);

          if($this->_db->exeStatement($stmt))
          {
              $newId = $this->_db->getLastInsertId();
              
              if($this->_cc->isActive() && $newId>0){
                 $this->clearAllCacheKey($newId);
              }
              
              if($newId>0){
                 return $newId;
              }
              
              return true;
          }
       }
       
       return self::throwNewException(290348234829034,"Impossibile Aggiungere!");
   }
   
   /**
    * Aggiorna le informazioni nel DB
    * 
    * @param Mixed $id          Id Oggetto
    * @param Mixed $updateData  Entità/Array  da aggiornare
    * 
    * @return Boolean
    */
   protected function update($id,$updateData)
   {
      if(!empty($id) && !empty($updateData))
      {
          if(is_object($updateData) && ($updateData instanceof Abstract_Entities))
          {
             $updateData->__beforeUpdate($this);
             
             $updateData = $updateData->toArray();
          }
          
          $updateData = $this->filterEmptyArrayValues($updateData,true);
          
          $stmt        = $this->_db->BuildSqlStatementUpdate($this->_class_sql_table_name,$updateData,Array($this->_class_sql_table_name.".".$this->_class_sql_table_id => " = \"{$id}\" "));          
          
          if($this->_db->exeStatement($stmt)!==false)
          {
             $this->getUnloadEntities($id);
             return true;
          }
      }
      
      return self::throwNewException(290348234829034,"Impossibile Modificare Info di ".$this->_class_ent_name.", id: ".$id);
   }
   
    
   /**
    * Elimina fisicamente sul DB le informazioni relative al record di id $id
    * 
    * @param Mixed              $id              Id Anagrafica
    * @param Abstract_Entities  $deletedEntity   [OPZIONALE] Entity eliminata, default NULL
    * 
    * @return Boolean
    */
   protected  function delete($id,Abstract_Entities $deletedEntity = null)
   {
      if(!empty($id))
      {
          if(is_object($deletedEntity))
          {
              $deletedEntity->__beforeDelete($this);
          }
          
          $stmt  = $this->_db->BuildSqlStatementDelete($this->_class_sql_table_name,Array($this->_class_sql_table_name.".".$this->_class_sql_table_id=>" = ? "));
          
          if($this->_db->exeStatement($stmt,Array($id)))
          {
             $this->clearAllCacheKey($id);
             return true;
          }
      }
      
      return self::throwNewException(9458209582093483948,"Impossibile Eliminare L'oggetto di ".$this->_class_ent_name.", id: ".$id);
   }
   
   /**
    * Elimina fisicamente i record dal db su base condizioni.
    * <br><b>Questo metodo non svuota cache</b>
    * 
    * @param Array $conditionArr Condizioni sql (no statement)
    * 
    * @return boolean
    */
   protected  function deleteWithCondition($conditionArr)
   {
      if(!empty($conditionArr))
      {
          $stmt  = $this->_db->BuildSqlStatementDelete($this->_class_sql_table_name,$conditionArr);   
          if($this->_db->exeStatement($stmt))
          {
             return true;
          }
      }
      
      return self::throwNewException(9458209582093483948,"Impossibile Eliminare, condzione errata, Object: ".$this->_class_ent_name);
   }

   /**
    * Ricerca per la tabella specifica  nel DB.
    * 
    * @param Array        $conditionArr Array Condizioni SQL di ricerca
    * @param Int          $limit_start LIMIT start
    * @param Int          $limit_end LIMIT end
    * @param String       $orderBy Ordinamento campo
    * @param String       $orderMode  Ordinamento mode
    * @param $returnMode  Modalità ritorno resultSet default DAO_DBManager::FETCH_CLASS
    * 
    * @return Array() ResultSet 
    */
   protected function search($conditionArr = Array(),$limit_start = 0,$limit_end = 10,$orderBy = null,$orderMode = null,$returnMode = DAO_DBManager::FETCH_CLASS)
   {
      
      $sqlSelect = $this->_db->BuildSqlStatementSelect("*",$this->_class_sql_table_name,$conditionArr,null,$orderBy,$orderMode,$limit_start,$limit_end);
      $res       = $this->_db->exeStatement($sqlSelect);
      
      if($res)
      {
         if($this->_db->getNumRows()>0)
         {
            return $this->_db->fetchArrayResultSet($returnMode,$this->_class_ent_name);
         }
      }
      
      return Array();
   }
   
   /**
    * Conta il numero di record su base $conditionArr
    * 
    * @param Array $conditionArr Array condizioni Sql "campo"=>"valore sql query" (no statement)
    * 
    * @return Int 
    */
   protected function count($conditionArr = Array())
   {
      $sqlSelect = $this->_db->BuildSqlStatementSelect("COUNT(".$this->_class_sql_table_id.") as total_record ",$this->_class_sql_table_name,$conditionArr,null,null,null,null,null);
      $res       = $this->_db->exeStatement($sqlSelect);
      
      if($res)
      {
         return $this->_db->fetchResult("total_record",0);
      }   
      
      return self::throwNewException(290348234829034,"Impossibile Contare il numero di record per ".$this->_class_ent_name);
   }
   
   /**
    * Ricerca l'id MAX della tabella specificata per il manager
    * 
    * @return Mixed MAX ID
    */
   public function getMaxId()
   {
      $sqlStmt  = $this->_db->BuildSqlStatementSelect("COALESCE(MAX(".$this->_class_sql_table_id."),0) as maxids", $this->_class_sql_table_name);
      $res      = $this->_db->exeStatement($sqlStmt);
      
      if($res)
      {
          if($this->_db->getNumRows()>0)
          {
             return $this->_db->fetchResult("maxids",0);
          }
          
          return 0;
      }
   }
   
   /**
    * Restituisce il valore Autoincrement per la tabella Specificata
    * 
    * @return Int
    */
   public function getMaxAutoincrement()
   {
      return $this->_db->getAutoIncrementValue($this->_class_sql_table_name);
   }
   
   
   /**
    * Restituisce un istanza dell'entità configurata nel manager.
    * 
    * @param String    $classEntName   Nome classe Entità, default utilizzata quella in uso dalla classe $this->_class_ent_name
    * @param Array     $data           Valori da inizializzare nell'entità. default NULL
    * @param Boolean   $addNewField    Indica se aggiungere il campo all'entità, creando quindi l'attributo
    * 
    * @return Abstract_Entities
    */
   public function getEntitiesObject($classEntName = null,$data = null,$addNewField = false)
   {
       $classEntName = is_null($classEntName) ? $this->_class_ent_name : $classEntName;
       
       if(class_exists($classEntName))
       {
          $entities  =  new $classEntName();
          
          if($data instanceof ArrayObject)
          {
             $data = $data->getArrayCopy();
          }
          
          if(is_array($data) && count($data)>0)
          {
             foreach($data as $fieldName => $value)
             {                
                if($addNewField || isset($entities->{$fieldName}))
                {
                   $entities->{$fieldName} = $value;
                }
             }
          }
          
          return $entities;
       }
       
       return self::throwNewException(09128309189209128482,'Cannot Build Entities: '.$classEntName." with data: ".print_r($data,true));
   }
   
   
   /**
    * Restituisce l'entità configurata nell'init in base all'id fornito. Questo metodo ricerca in cache creando la chiave su base tabella sql - id
    * 
    * @param  Mixed      $id           Id univoco Entità, puà essere un valore intero, stringa, o un array associativo.
    * @param  Boolean    $forceCache   Indica se bypassare cache, default FALSE
    * 
    * @return Object Entity
    */
   protected function getLoadEntities($id,$forceCache = false)
   {
      $sqlFieldId   = $this->_class_sql_table_id;
      $entName      = $this->_class_ent_name;
      $ccKey        = $this->getCacheKeyMethodName(__FUNCTION__,$id);
      
      $entityReturn = new $entName();
      
      if($this->_cc->isActive() && !$forceCache)
      {
         $fetched = $this->_cc->fetch($ccKey);
         if($fetched!==false){
             return $fetched;
         }
      }
      
      $ccStore = false;
      
      $stmt    = $this->_getBuildStatementById($id);
            
      if($stmt !== false)
      {
         if($this->_db->exeStatement($stmt)!==false)
         {
            if($this->_db->getNumRows()>0)
            {
               $entityReturn = $this->_db->fetchResultSet(DAO_DBManager::FETCH_CLASS_LATE,$entName);  //Fetch Entità
               $ccStore      = true;
            }         
         }
      }

      if($this->_cc->isActive() && $ccStore)
      {
         if(!$this->_cc->store($ccKey,$entityReturn))
         {
            return self::throwNewException(90202983847758,"Impossibile salvere su cache!");
         }
      }

      return $entityReturn;      
   }
   
   
   /**
    * Restituisce un Array di Entities configurata nell'init in base all'id fornito. Questo metodo ricerca in cache creando la chiave su base tabella sql - id
    * 
    * @param  Mixed      $id           Id univoco Entità, puà essere un valore intero, stringa, o un array associativo.
    * @param  Boolean    $forceCache   Indica se bypassare cache, default FALSE
    * 
    * @return Array(Entities)
    */
   protected function getLoadEntitiesArray($id,$forceCache = false)
   {
      $entName      = $this->_class_ent_name;
      $ccKey        = $this->getCacheKeyMethodName(__FUNCTION__,$id);
      
      $entitiesArray = Array();
      
      if($this->_cc->isActive() && !$forceCache)
      {
         $fetched = $this->_cc->fetch($ccKey);
         if($fetched!==false){
             return $fetched;
         }
      }
      

      $ccStore = false;
      
      $stmt    = $this->_getBuildStatementById($id,null);
      
      if($stmt !== false)
      {
         if($this->_db->exeStatement($stmt)!==false)
         {
            if($this->_db->getNumRows()>0)
            {
               $entitiesArray = $this->_db->fetchArrayResultSet(DAO_DBManager::FETCH_CLASS_LATE,$entName);  //Fetch Array Entità
               $ccStore       = true;
            }
         }
      }
      
   
      if($this->_cc->isActive() && $ccStore)
      {
         if(!$this->_cc->store($ccKey,$entitiesArray))
         {
            return self::throwNewException(23748289347278346253574,"Impossibile salvere su cache!");
         }
      }

      return $entitiesArray;
      
   }
   
   
   /**
    * Restituisce un Array di Entities configurata nell'init in base all'id fornito. Questo metodo ricerca in cache creando la chiave su base tabella sql-'all'
    * 
    * @param  Boolean    $forceCache   Indica se bypassare cache, default FALSE
    * 
    * @return Array(Entities)
    */
   protected function getLoadEntitiesArrayAll($forceCache = false)
   {
      $entName      = $this->_class_ent_name;
      $ccKey        = $this->getCacheKeyMethodName(__FUNCTION__,'ALL');
      
      $entitiesArray = Array();
      
      if($this->_cc->isActive() && !$forceCache)
      {
         $fetched = $this->_cc->fetch($ccKey);
         if($fetched!==false){
             return $fetched;
         }
      }
      

      $ccStore = false;
      
      $stmt    = $this->_getBuildStatementById(null,null);
      
      if($stmt !== false)
      {
         if($this->_db->exeStatement($stmt)!==false)
         {
            if($this->_db->getNumRows()>0)
            {
               $entitiesArray = $this->_db->fetchArrayResultSet(DAO_DBManager::FETCH_CLASS_LATE,$entName);  //Fetch Array Entità
               $ccStore       = true;
            }
         }
      }
      
   
      if($this->_cc->isActive() && $ccStore)
      {
         if(!$this->_cc->store($ccKey,$entitiesArray))
         {
            return self::throwNewException(23748289347278346253574,"Impossibile salvere su cache!");
         }
      }

      return $entitiesArray;
      
   }
   
   
   /**
    * Elimina l'entità cachata tramite il sistema di cachind di default.
    * <b>NB: Se si utilizza prima un sistema e poi un altro si deve pulire la cache manualmente sul vecchio sistema!</b>
    * 
    * @param Mixed $id Id Univoco Entità
    * 
    * @return Boolean
    */
   protected function getUnloadEntities($id)
   {
      if($this->_cc->isActive())
      {
         $ccKey        = $this->getCacheKeyMethodName('getLoadEntities',$id);
         return $this->_cc->delete($ccKey);
      }
      
      return false;
   }
   
   /**
    * Elimina l'Array di Entità cachata tramite il sistema di cachind di default.
    * 
    * <b>NB: Se si utilizza prima un sistema e poi un altro si deve pulire la cache manualmente sul vecchio sistema!</b>
    * 
    * @param Mixed $id Id Univoco Entità
    * 
    * @return Boolean
    */
   protected function getUnloadEntitiesArray($id)
   {
      if($this->_cc->isActive())
      {
         $ccKey        = $this->getCacheKeyMethodName('getLoadEntitiesArray',$id);
         return $this->_cc->delete($ccKey);
      }
      
      return false;
   }
   
     
   /**
    * Elimina l'Array di tutte le Entità cachate tramite il sistema di cachind di default.
    * 
    * <b>NB: Se si utilizza prima un sistema e poi un altro si deve pulire la cache manualmente sul vecchio sistema!</b>
    * 
    * @return Boolean
    */
   protected function getUnloadEntitiesArrayAll()
   {
      if($this->_cc->isActive())
      {
         $ccKey        = $this->getCacheKeyMethodName('getLoadEntitiesArrayAll','ALL');
         return $this->_cc->delete($ccKey);
      }
      
      return false;
   }
   
   
   /**
    * Restituisce il nome della classe manager invocatrice del metodo
    * 
    * @return String
    */
   protected function getCalledClass()
   {
      return function_exists("get_called_class") ? get_called_class() : static::$_class_name;
   }
   
   /**
    * Costruisce il nome per la chiave per il metodo e l'id specificato
    * @param String  $methodName Nome del metodo del manager
    * @param Mixed   $id         [OPZIONALE] Id univoco chiave-metodo, default NULL
    * @return String
    */
   protected function getCacheKeyMethodName($methodName,$id = null)
   {
      
      if(is_array($id) && count($id) > 0){
         $id = implode("_",array_values($id));
      }
           
      if(!is_null($id)){
         return $this->_cc->prepareKey(str_replace($this->getDatabaseManager()->getConfigurationValue("table_prefix"),"",$this->_class_sql_table_name),$methodName,str_replace($this->getDatabaseManager()->getConfigurationValue("table_prefix"),"",$id));
      }
      
      return $this->_cc->prepareKey(str_replace($this->getDatabaseManager()->getConfigurationValue("table_prefix"),"",$this->_class_sql_table_name),$methodName);
   }

   
   /**
    * Restituisce tutte le chiavi possibile per il Manager Figlio
    * 
    * @param Mixed $id  [OPZIONALE] id record, possibile anche null
    * 
    * @return Array
    */
   public function getAllCacheKeys($id = null)
   {
      $className    = function_exists("get_called_class") ? get_called_class() : __CLASS__;
      $class        = new ReflectionClass($className);
      $methods      = $class->getMethods(ReflectionMethod::IS_FINAL | ReflectionMethod::IS_PUBLIC);
      
      foreach($methods as $method)
      {
         $methodName = $method->getName();
         if(strstr($methodName,"__")===false && strstr($methodName,"init")===false && $methodName!==__FUNCTION__)
         {
            $arrKeys[] =  $this->getCacheKeyMethodName($methodName,$id);
            $arrKeys[] =  $this->getCacheKeyMethodName($methodName);
         }
      }
      
      return $arrKeys;
   }
   
   
   /**
    * Restituisce tutti i nomi metodi che hanno un nome contenente $name
    * 
    * @param String $name Nome del metodo, o substring del nome
    * 
    * @return Array
    */
   protected function getAllMethodsLikeByName($name)
   {
      $className    = function_exists("get_called_class") ? get_called_class() : __CLASS__;
      $class        = new ReflectionClass($className);
      $methods      = $class->getMethods(ReflectionMethod::IS_FINAL | ReflectionMethod::IS_PUBLIC);
      
      $methodsArray = Array();
      
      foreach($methods as $method)
      {
         $methodName = $method->getName();
         if(strstr($methodName,"__")===false && strstr($methodName,"init")===false && strstr($methodName,$name)!==false){
            $methodsArray[] =  $methodName;
         }
      }
      
      return $methodsArray;
   }
   
   /**
    * Pulisce tutte le chiavi possibili esistenti per il manager su base id
    * 
    * @param Mixed $id [OPZIONALE] id record, possibile anche null
    * 
    * @return Boolean
    */
   protected function clearAllCacheKey($id = null)
   {
      if(!$this->_cc->isActive()){
         return true;
      }
      
      $cacheKeys = $this->getAllCacheKeys($id);
      
      if(is_array($cacheKeys) && count($cacheKeys)>0)
      {
         foreach($cacheKeys as $key){
            $this->_cc->delete($key);
         }
         
         return true;
      }
      
      return false;
   }
   
   
   /**
    * Filtra gli elementi NULL o Abstract_Entities::NULL dell'array indicato. Questo metodo è utile per evitare di aggiornare valori sul database che non sono cambiati
    * 
    * @param Array   $arrayValues Array chiave => valore da filtrare
    * @param Boolean $filterId    [OPZIONALE] Indica se eliminare dall'array anche la chiave che contiene l campo id (utile in fase di update), default FALSE
    * 
    * @return Array
    */
   protected function filterEmptyArrayValues($arrayValues,$filterId = false)
   {
       $filteredArray = false;
       
       if(is_array($arrayValues) && count($arrayValues) > 0)
       {
          $filteredArray =  array_filter($arrayValues,function($value){ return !Abstract_Entities::isNULL($value); });
          
          if($filterId)
          {
             if(isset($filteredArray[$this->_class_sql_table_id]))
             {
                unset($filteredArray[$this->_class_sql_table_id]);
             }
          }
       }
       
       return $filteredArray;
   }
   
   /**
    * Costruisce lo statement su base Id fornito ai metodi
    * 
    * @param Mixed $id     Identificativo del record da ricercare, o array di valori per chiavi multiple
    * @param Int   $limit  Limit Query, default 1
    * 
    * @return PDOStatement  FALSE se $id non è valido
    */
   private function _getBuildStatementById($id,$limit = 1)
   {
      $sqlFieldId   = $this->_class_sql_table_id;
      
      $whereCondition = Array();
      $stmtParameter  = Array();
      
      if(is_array($id) && count($id)>0)
      {
         foreach($sqlFieldId as $key)
         {
            if(isset($id[$key]))
            {
               $whereCondition[$key] = " = :{$key} ";
               $stmtParameter[$key]  = $id[$key];
            }
            else
            {
               return self::throwNewException(2934820348023402,'Cannot build Statement, key whit name '.$key.' is not defined in sqltable id property used! ');
            }
         }
      }
      else if(!is_null($id))
      {
         $whereCondition[$sqlFieldId] = " = :{$sqlFieldId} ";
         $stmtParameter[$sqlFieldId]  = $id;
      }
      
      
      if(is_array($whereCondition) && count($whereCondition) > 0)
      {
         $stmt    = $this->_db->BuildSqlStatementSelect("*",$this->_class_sql_table_name,$whereCondition,null,null,null,$limit,null);

         if(count($stmtParameter) > 0)
         {
            foreach($stmtParameter as $key => $value){
               $stmt->bindValue($key, $value);
            }
         }

         return $stmt;
      }
      
      return false;
   }
}
