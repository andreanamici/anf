<?php

/**
 * Manager Entitò Generico
 * Questo Manager puà gestire Tabelle generiche instanziando il nome tabella e campo id
 * 
 */
class EntitiesManager_Generic extends Abstract_EntitiesManager
{
   
   private $_fetch_mode = null;
   
   /**
    * Manager Entitò Generico
    * 
    * Questo Manager puà gestire Tabelle generiche instanziando il nome tabella e campo id
    * 
    * @param String $sqlTableName [OPZIONALE] Nome Tabella sql
    * @param String $sqlTableId   [OPZIONALE] Id campo tabella sql
    * 
    * @return Boolean
    */
   public function __construct($sqlTableName = null,$sqlTableId = null,$fetchMode = DAO_DBManager::FETCH_CLASS_LATE)
   {
      $this->_fetch_mode = $fetchMode;
      return$this->initMe($sqlTableName,$sqlTableId);
   }
   
   /**
    * Imposta la tipologia di return dei metodi del Manager
    * 
    * @param String $fetchMode
    * 
    * @return Boolean
    */
   public function setFetchMode($fetchMode){
      return $this->_fetch_mode = $fetchMode;
   }
   
   /**
    * Inizializza Tabella e campo field principale 
    *  
    * @param String $sqlTableName [OPZIONALE] Nome Tabella sql
    * @param String $sqlTableId   [OPZIONALE] Id campo tabella sql
    * 
    * @return Boolean
    */
   public function initMe($sqlTableName,$sqlTableId){
      return $this->init(Entities_Generic::getClassName(),$sqlTableName,$sqlTableId);
   }
   
   /**
    * Inserisce un nuovo record per la tabella instanziata nel Manager
    * 
    * @param Array $arrInfo Informazioni da storare
    * 
    * @return Mixed Id nuovo Record
    */
   public function addRecord($arrInfo){
      return parent::add($arrInfo);
   }
   
   
   /**
    * Aggiorna il record per la tabella instanziata nel Manager tramite id
    * 
    * @param Mixed $id      Id univoco del record da aggiornare
    * @param Array $arrInfo Informazioni da storare
    * 
    * @return Boolean
    */
   public function updateRecord($id,$arrInfo){
      return parent::update($id,$arrInfo);
   }
   
   /**
    * Elimina Fisicamente il Record con id specificato dal DB
    * 
    * @param Mixed $id  Id Record, Stringa, intero, char etc
    * 
    * @return Boolean
    */
   public function deleteRecord($id){
      return parent::delete($id);
   }
   
   
   /**
    * Elimina Fisicamente il Record dal DB con id specificato  tramite condizione
    * 
    * @param Array $conditionArr Array condizioni sql (no stmt)
    * 
    * @return Boolean
    */
   public function deleteRecordWithCondition($conditionArr) {
      return parent::deleteWithCondition($conditionArr);
   }
   
   /**
    * Conta il numero di record che rispondono alla condizione specificata
    * 
    * @param Array $conditionArr Array condizioni sql (no stmt)
    * 
    * @return type
    */
   public function countRecords($conditionArr) {
      return parent::count($conditionArr);
   }
   
   
   /**
    * Ricerca informazioni per il record specifico
    * 
    * @param Mixed   $id     Id Univoco
    * @param Boolean $force  Indica se bypassare o meno la cache, defalt FALSE
    * 
    * @return Mixed
    */
   public function getLoad($id,$force = false)
   {
      $entities = $this->getLoadEntities($id,$force);
      
      switch($this->_fetch_mode)
      {
         case DAO_DBManager::FETCH_ARRAY:
         case DAO_DBManager::FETCH_ASSOC:
         case DAO_DBManager::FETCH_ASSOC_SINGLE:
                                                   return $entities->toArray();
         default:
                                                   return $entities;
      }
      
      return false;
   }
   
   
   /**
    * Ricerca tutti i record che rispondo al valore del campo passato,
    * 
    * @param Mixed  $id        Valore del campo da filtrare
    * @param String $field_id  Campo con il quale filtrare record dalla tabella instanziata
    * @param Boolean $force    Indica se bypassare o meno la cache, defalt FALSE
    * 
    * @return Mixed
    */
   public function getLoadAllByField($id,$field_id)
   {
      $curr_field_id = $this->_class_sql_table_id;
      $this->setClassSqlTableId($field_id);
      
      $entities = $this->getLoadEntitiesArray($id);
      $return   = false;
      
      switch($this->_fetch_mode)
      {
         case DAO_DBManager::FETCH_ARRAY:
         case DAO_DBManager::FETCH_ASSOC:           $return = Utility_CommonFunction::Entities_to_Array($entities); break;
         case DAO_DBManager::FETCH_ASSOC_SINGLE:    $return = Utility_CommonFunction::Entities_to_Array($entities); 
                                                    $return = $return[0];
                                                   break;
                                                   
            
                                          break;
         default:
                                          $return = $entities;
            
                                          break;
      }
      
      $this->setClassSqlTableId($curr_field_id);
      return $return;
   }
   
   
     
   /**
    * Ricerca tutti i record
    * 
    * @param Boolean $force  Indica se bypassare o meno la cache, defalt FALSE
    * 
    * @return Array(Entities_Generic)
    */
   public function getLoadAll($force = false)
   {
      $curr_field_id = $this->_class_sql_table_id;
      $this->setClassSqlTableId($curr_field_id);
      
      $entities = $this->getLoadEntitiesArrayAll($force);
      $return   = false;
      
      switch($this->_fetch_mode)
      {
         case DAO_DBManager::FETCH_ARRAY:
         case DAO_DBManager::FETCH_ASSOC:
                                                    $return = Utility_CommonFunction::Entities_to_Array($entities);  break;
                                                 
         case DAO_DBManager::FETCH_ASSOC_SINGLE:     $return = Utility_CommonFunction::Entities_to_Array($entities); 
                                                     $return = $return[0];
                                                   break;
         default:
                                          $return = $entities;
            
                                          break;
      }
      
      $this->setClassSqlTableId($curr_field_id);
      return $return;
   }
   
   
   /**
    * Ricerca tutti i record per la tabella su base condizione 
    * 
    * @param Array    $condition     [OPZIONALE] Array condizione (no stmt)
    * @param String   $orderBy       [OPZIONALE] Campo ordinamento Record
    * @param String   $orderMode     [OPZIONALE] Modalità ordinamento ASC,DESC,RAND()
    * @param Int      $limit_start   [OPZIONALE] Limit start,defalt NULL
    * @param Int      $limit_end     [OPZIONALE] Limit end,defalt NULL
    * 
    * @return Array(Entities_Generic)
    */
   public function getLoadSearch($condition = Array(),$orderBy = null,$orderMode = 'ASC',$limit_start = null,$limit_end = null){
      return $this->search($condition, $limit_start, $limit_end, $orderBy, $orderMode,$this->_fetch_mode);
   }
   
   /**
    * [SKIP LOGICHE ENTITIES MANAGER]
    * 
    * Restituisce il primo record resulset della query nel formato specificato
    * 
    * @param String $sqlQuery      Query da eseguire
    * 
    * return Mixed
    * 
    */
   public function getLoadByQuery($sqlQuery)
   {
      $res = $this->_db->exeQuery($sqlQuery);
      
      if($res!==false){
         return $this->_db->fetchResultSet($this->_fetch_mode);
      }
      
      return false;
   }
   
   /**
    * [SKIP LOGICHE ENTITIES MANAGER]
    * 
    * Restituisce tutto resulset della query nel formato specificato
    * 
    * @param String $sqlQuery      Query da eseguire
    * 
    * @return Mixed
    * 
    */
   public function getLoadAllByQuery($sqlQuery)
   {
      $res = $this->_db->exeQuery($sqlQuery);
      
      if($res!==false){
         return $this->_db->fetchArrayResultSet($this->_fetch_mode);
      }
      
      return false;
   }
  
}
