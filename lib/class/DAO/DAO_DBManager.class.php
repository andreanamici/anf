<?php

/**
 * Classe che gestisce la connessione al database
 *
 * @method DAO_DBManager getInstance Restituisce l'instanza univoco di questo manager
 *
 */
class DAO_DBManager extends DAO_DBsqlStatementBuilder
{
   use Trait_Singleton,Trait_ObjectUtilities, Trait_ApplicationConfigs;


   /**
    * Contiene i dati strutturali del database, ricercati in cache
    * @var Array()
    */
   private static $_cache_structure_data = Array();


   /**
    * Contiene l'array contenente tutte le configurazioni dei managers
    *
    * @var Array
    */
   private static $_MANAGERS_CONFIGS     = null;


   /**
    * Nr di tentativi massimi di connessiona al database, al raggiungimento verrà lanciata un eccezione
    *
    * @var Int
    */
   const MAX_CONNECTION_RETRY = 10;


   /**
    * ResultSet fetchato come array associativo
    * @var Int
    */
   const FETCH_ASSOC       = 1;


   /**
    * ResultSet fetchato come array in cui gli indici sono numerici, da 0
    * @var Int
    */
   const FETCH_ARRAY       = 2;

   /**
    * ResultSet fetchato con l'istanza di un oggetto specifico
    * @var Int
    */
   const FETCH_CLASS       = 3;

   /**
    * ResultSet fetchato con l'istanza di un oggetto specifico, invocando dopo il costruttore
    * @var Int
    */
   const FETCH_CLASS_LATE  = 3;

   /**
    * ResultSet fetchato come instanza di stdClass
    * @var Int
    */
   const FETCH_OBJECT      = 4;


   /**
    * ResultSet fetchato come array singolo, unico array di valori
    * @var Int
    */
   const FETCH_ASSOC_SINGLE = 5;


   /**
    * Insert Mode Replace
    * @var String
    */
   const INSERT_MODE_REPLACE = "REPLACE";

   /**
    * Insert Mode Ignore
    * @var String
    */
   const INSERT_MODE_IGNORE  = "IGNORE";


   /**
    * Driver Mysql utilizzato
    * @var String
    */
   const DRIVER_MYSQL      = 'mysql';

      /**
    * Driver sqlite2 utilizzato
    * @var String
    */
   const DRIVER_SQLITE2      = 'sqlite2';

      /**
    * Driver sqlite3 utilizzato
    * @var String
    */
   const DRIVER_SQLITE      = 'sqlite';

   /**
    * Host sql
    * @var String
    */
   private $_host          = null;

   /**
    * Utente
    * @var String
    */
   private $_user          = null;

   /**
    * Password
    * @var String
    */
   private $_pass          = null;

   /**
    * Porta
    * @var Int
    */
   private $_port          = null;

   /**
    * Indica se connessione persistente
    * @var String
    */
   private $_dbname        = null;

   /**
    * Indica il driver utilizzato nella connession DNS
    * @var String
    */
   private $_driver        = null;

   /**
    * Indica se connessione persistente
    * @var Boolean
    */
   private $_persistent    = null;


   /**
    * Contiene il prefisso delle tabelle del database configurato
    * @var String
    */
   private $_table_prefix  = "";


   /**
    * Charset Default
    * @var Strings
    */
   private  $_charset = null;


   /**
    * Riferimento oggetto PDO
    * @var PDO
    */
   protected $_pdo           = null;


   /**
    * Nome della configurazione del manager attualmente in uso
    * @var String
    */
   protected $_manager       = null;


   /**
    * Rifermento Classe query builder FluendPDO , basato sulle librerid PDO
    * @var FluentPDO
    */
   protected $_fluentpdo     = null;

   /**
    * Stora l'ultima sql Query Lanciata dal manager
    * @var String
    */
   protected static $_last_sql = "";


   /**
    * Conteggia il nr di query eseguite
    * @var Int
    */
   protected static $_query_numbers = 0;

   /**
    * Riferimento Statement
    * @var PDOStatement
    */
   protected $_pdo_statement = null;


   private static $_connection_enable = true;

   /**
    * Abilita la connessione automatica al costruttore
    * @return Boolean
    */
   public static function enableConnection()
   {
      return self::$_connection_enable = true;
   }

   /**
    * Disabilita la connessione automatica al costruttore
    * @return Boolean
    */
   public static function disableConnection()
   {
      return self::$_connection_enable = false;
   }


   /**
    * Classe per la gestione del DB
    */
   public function __construct()
   {
       require_once 'FluendPDO/FluentPDO.php';

       parent:: __construct();

       if($this->_checkConfiguration())
       {
           if(!$this->initManagerConfigs()->setManagerName(DB_MANAGER_CONFIG_DEFAULT,self::$_connection_enable))
           {
              if(self::$_connection_enable)
              {
                 return self::throwNewException(56572547,"Impossibile stabilire una connessione con il databaase per la connection \"".$this->_manager.'"');
              }
           }
       }

       return false;
   }


   /**
    * Distrugge la classe e chiude la connessione al DB
    * @return Boolean
    */
   public function  __destruct()
   {
      if(!$this->_persistent)
      {
        $close = $this->closeConnection();
        unset($this);
        return $close;
      }

      return true;
    }

    /**
     * Imposta il nome della configurazione del manager attualmente in uso
     *
     * @param String   $manager           Nome della configurazione da usare
     * @param Boolean  $openConnection    Indica se connettere automanticamente a questa configurazione
     *
     * @return \DAO_DBManager
     */
    public function setManagerName($manager,$openConnection = true)
    {
       $this->_manager = $manager;

       if($openConnection)
       {
          $this->enableConnection();
          return $this->openConnection();
       }

       return $this;
    }


    /**
     * Restituisce il nome della configurazione del manager attualmente in uso
     *
     * @return String
     */
    public function getManagerName()
    {
       return $this->_manager;
    }


   /**
    * Restituisce l'array delle configurazioni in base al nome del manager richiesto
    *
    * @param String $managerName Nome del manager
    *
    * @return Array
    */
   public function getConfigurationByManagerName($managerName)
   {
       if(!isset(self::$_MANAGERS_CONFIGS[$managerName]))
       {
          return self::throwNewException(9128391284013945945945,'Nome configurazione per il DBManager non trovata: '.$managerName);
       }

       return self::$_MANAGERS_CONFIGS[$managerName];
   }


   /**
    * Ricerca il valore nella configurazione attualmente in uso
    *
    * @param String $configName nome del parametro da ricercare nel configs del manager
    *
    * @return String
    */
   public function getConfigurationValue($configName)
   {
      if(isset(self::$_MANAGERS_CONFIGS[$this->getManagerName()][$configName]))
      {
         return self::$_MANAGERS_CONFIGS[$this->getManagerName()][$configName];
      }

      return self::throwNewException(12983102940912773509913, 'Impossibile trovare la proprietà: '.$configName.' per il manager: '.$this->getManagerName());
   }



   /**
    * Restituisce la configurazione attualmente usata
    *
    * @return Array
    */
   public function getConfiguration()
   {
      if(isset(self::$_MANAGERS_CONFIGS[$this->getManagerName()]))
      {
         return self::$_MANAGERS_CONFIGS[$this->getManagerName()];
      }

      return self::throwNewException(12983102940912773509913, 'Impossibile restituire la configurazione attuale per il manager: '.$this->getManagerName());
   }


   /**
    * Aggiunge una configurazione al manager
    *
    * @param Strubg $managerName Nome delle configurazione
    * @param array  $configs     Parametri di configurazione
    *
    * @return DAO_DBManager
    */
   public function addConfiguration($managerName,array $configs)
   {
      self::$_MANAGERS_CONFIGS[$managerName] = $configs;

      return $this->_checkConfiguration();
   }


   /**
    * Apre una connessione con il db
    *
    * @param Int $nrRetry  Numero di tentativo attuale , non USARE
    *
    * @return Boolean
    */
   public function openConnection($nrRetry = 0)
   {

       if(!self::$_connection_enable)
       {
          return false;
       }

       $configuration        = $this->getConfigurationByManagerName($this->getManagerName());

       $this->_driver        = $configuration["driver"];

       switch($this->_driver)
       {

          case self::DRIVER_MYSQL:
          default:
              $this->_host          = $configuration["host"];
              $this->_port          = $configuration["port"];
              $this->_user          = $configuration["user"];
              if(!$this->_user)
              {
                return self::disableConnection();
              }
              $this->_pass          = $configuration["password"];
              $this->_persistent    = $configuration["persistent"];
              $this->_charset       = $configuration["charset"];
          case self::DRIVER_SQLITE:
          case self::DRIVER_SQLITE2:
              $this->_dbname        = $configuration["dbname"];
              $this->_write_sqllog  = $configuration["writelog"];
              $this->_table_prefix  = $configuration["table_prefix"];
              break;

       }

      $this->_pdo_statement = new PDOStatement();

       if($nrRetry >= self::MAX_CONNECTION_RETRY)
       {
          return self::throwNewException(981239812893,'Connessione al database fallita!');
       }
       else
       {
            try
            {
               $this->_pdo       = new PDO($this->_buildDSNString(), $this->_user,$this->_pass, Array(PDO::ATTR_PERSISTENT=>$this->_persistent,PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
               $this->_fluentpdo = new FluentPDO($this->_pdo);

               $this->_fluentpdo;
            }
            catch (\Exception $e)
            {
               $this->_fluentpdo = null;
               $this->_pdo       = null;
               return $this->openConnection(++$nrRetry);
            }
       }


       if($this->_pdo instanceof  PDO){
          return $this;
       }


      // return self::throwNewException(2903489038103098109380194,'PDO non utilizzabile!');
   }


   /**
    * Chiude la connessione con il db
    *
    * @return Boolean
    */
   public function closeConnection()
   {

      if($this->_pdo instanceof PDO)
      {
         return isset($this->_pdo->__destruct) ? $this->_pdo->__destruct() : $this->_pdo = null;
      }

	   return true;
   }

   /**
    * Restituisce la connection attuale
    *
    * @return \PDO
    */
   public function getConnection()
   {
       return $this->_pdo;
   }


   /**
    * Esegue una Query con eventuali parametri da bindare nello statement
    *
    * @param String $sqlQuery    Sql da eseguire
    * @param Array  $paramArray  [OPZIONALE] Parametri da bindare allo statement
    *
    * @return Boolean
    */
   public function exeQuery($sqlQuery,$paramArray = Array())
   {
	 if(is_string($sqlQuery) && strlen($sqlQuery)>0)
	 {
            $this->_pdo_statement  = $this->_pdo->prepare($sqlQuery);

            $this->bindStatementParams($paramArray);


            if($this->_write_sqllog)
            {
               $this->writeLog($this->_pdo_statement->queryString,'querylog');
            }

            static::$_last_sql = $this->_pdo_statement->queryString;
            static::$_query_numbers++;

            return $this->_pdo_statement->execute();
	 }
	 return self::throwNewException(923840202039,"Questo metodo deve ricevere una sql query string valida");
   }

   /**
    * Esegue uno Statement precedentemente preparato
    *
    * @param PDOStatement $stmt       PDOStatement
    * @param Array        $paramArray Parametri da bindare allo statement
    *
    * @return \DAO_DBManager
    */
   public function exeStatement(PDOStatement $stmt,$paramArray = Array())
   {
      $this->_pdo_statement  = $stmt;

      $this->bindStatementParams($paramArray);

      if($this->_write_sqllog){
            $this->writeLog($this->_pdo_statement->queryString,'querylog');
      }

      static::$_last_sql = $this->_pdo_statement->queryString;
      static::$_query_numbers++;

      $res = $this->_pdo_statement->execute();

      if($res!==false){
         return $this;
      }

      return $this->throwStatementError();
   }

   /**
    * Binda i parametri in formato array numerico / associativo allo statement corrente
    *
    * @param Array $paramArray Parametri da bindare
    *
    * @Boolean
    */
   public function bindStatementParams($paramArray)
   {
       if(is_array($paramArray) && count($paramArray)>0)
       {
            if(count($paramArray)>0)
            {
               foreach($paramArray as $key => &$value)
               {
                  $keyType   = gettype($key);
                  $valueType = gettype($value);
                  $pdoType   = PDO::PARAM_STR;
                  $parameter = $key;

                  switch($keyType)
                  {
                     case 'integer':  $parameter = $key+1;    break;
                     case 'string':   $parameter = ":{$key}"; break;
                  }

                  switch($valueType)
                  {
                     case 'integer':
                     case 'double':
                     case 'float':
                                      $pdoType = PDO::PARAM_INT; break;
                     case 'string':   $pdoType = PDO::PARAM_INT; break;
                  }

                  $this->_pdo_statement->bindParam($parameter,$value,$pdoType);
               }

               return true;
            }
        }

        return false;
   }


   /**
    * Esegue una transazione su base Lista di statement da preparare o di semplici query da eseguire
    * Se la Transazione fallisce verrà invocata la Rollback per sicurezza
    *
    * @param Array $sqlTransaction Array di Sql / Array di Array("sql"=>sqlQuery da preparare,"data"=>Array di parametri da bindare)
    *
    * @return Boolean.
    */
   public function exeTransaction($sqlTransaction)
   {
      try
      {
         if(is_array($sqlTransaction) && count($sqlTransaction)>0)
         {
            $this->TransactionBegin();

            foreach($sqlTransaction as $key=>$transaction)
            {
                if(is_array($transaction[$key]) && isset($transaction[$key]["sql"]) && isset($transaction[$key]["data"]))
                {
                     $sql  = $transaction[$key]["sql"];
                     $data = $transaction[$key]["data"];
                     $this->_pdo_statement = $this->_pdo->prepare($sql);
                     $this->bindStatementParams($data);
                     $this->_pdo_statement->execute();
                }
                else if(strlen($transaction[$key])>0)
                {
                     $sql  = $transaction[$key];
                     $this->_pdo_statement = $this->_pdo->prepare($sql);
                     $this->_pdo_statement->execute();
                }


                if($this->_write_sqllog){
                   $this->writeLog($this->_pdo_statement->queryString,'querylog');
                }

                self::throwNewException(994948828493,"Transazione Invalida!");
            }

            return $this->transactionCommit();
         }
      }
      catch(PDOException $e)
      {
         $this->transactionRollback();
      }

      return false;
   }

   /**
    * Restituisce il rifermento al FluentPDO attualmente in uso
    *
    * @return FluentPDO
    */
   public function getFluentPDO()
   {
      return $this->_fluentpdo;
   }

   /**
    * Avvia una transazione
    * @return Boolean
    */
   public function transactionBegin()
   {
      return $this->_pdo->beginTransaction();
   }

   /**
    * Esegue la RollBack
    * @return Boolean
    */
   public function transactionRollback(){
      return $this->_pdo->rollBack();
   }

   /**
    * Commit transaction
    * @return Boolean
    */
   public function transactionCommit(){
      return $this->_pdo->commit();
   }


   /**
    * Restituisce il driver PDO instanziato
    *
    * @return \PDO
    */
   public function getPDO()
   {
      return $this->_pdo;
   }


   /**
    * Ottiene il singolo record del  resultSet dello statement processato, verificando che ci siano record da fetchare.
    *
    * @param String  $mode            Metodo di fetch da eseguire, default self::FETCH_DEFAULT
    * @param String  $className       [FETCH_OBJECT] Nome classe sulla quella si popoleranno tutti gli attributi public con lo stesso nome dei campi del resultset,default stdClass()
    * @param Boolean $closeStatement  Indica se chiudere lo statement
    *
    * @return Array,Object or FALSE
    */
   public function fetchResultSet($mode = self::FETCH_ASSOC,$className='stdClass',$closeStatement = false)
   {
       $res = Array();
       $index  = 0;

       switch($mode)
       {

          case self::FETCH_ASSOC:

                            if($this->getNumRows()>0){
                               $res = $this->_pdo_statement->fetch(PDO::FETCH_ASSOC);
                            }

                            break;

          case self::FETCH_ARRAY:

                            if($this->getNumRows()>0){
                               $res = $this->_pdo_statement->fetch(PDO::FETCH_NUM);
                            }

                            break;

          case self::FETCH_CLASS:

                            if($this->getNumRows()>0)
                            {
                               $this->_pdo_statement->setFetchMode(PDO::FETCH_CLASS, $className);
                               $res = $this->_pdo_statement->fetch();
                            }

                            break;

          case self::FETCH_CLASS_LATE:

                            if($this->getNumRows()>0)
                            {
                               $this->_pdo_statement->setFetchMode(PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, $className);
                               $res = $this->_pdo_statement->fetch();
                            }

                            break;

          case self::FETCH_OBJECT:

                        if($this->getNumRows()>0){
                           $res = $this->_pdo_statement->fetchObject($className);
                        }

                        break;
	}

	if($closeStatement)
        {
           $this->_pdo_statement->closeCursor() or self::throwNewException(2934902734982,"Impossibile chiudere lo statement");
           $this->_pdo_statement = null;
        }

	return $res;
   }


   /**
    * Ottiene una lista di record/oggetti del  resultSet dello statement processato, verificando che ci siano record da fetchare.
    *
    * @param String  $mode            Metodo di fetch da eseguire, default self::FETCH_ASSOC
    * @param String  $className       [FETCH_CLASS] Nome classe sulla quella si popoleranno tutti gli attributi public con lo stesso nome dei campi del resultset,default stdClass()
    * @param Boolean $closeStatement  Indica se chiudere lo statement,default FALSE
    *
    * @return Array or FALSE
    */
   public function fetchArrayResultSet($mode = self::FETCH_ASSOC,$className='stdClass',$closeStatement = false)
   {
       $retArr = Array();
       $index  = 0;

       switch($mode)
       {

          case self::FETCH_ASSOC:

                                $retArr = $this->_pdo_statement->fetchAll(PDO::FETCH_ASSOC);

                                break;

          case self::FETCH_ARRAY:

                                $retArr = $this->_pdo_statement->fetchAll(PDO::FETCH_NUM);

                                break;

          case self::FETCH_ASSOC_SINGLE:

                                    $retArr = $this->_pdo_statement->fetchAll(PDO::FETCH_NUM);

                                    $returnSingleArray = Array();

                                    foreach($retArr as $row)
                                    {
                                       if(!is_array($row) || count($row)>1){
                                          return self::throwNewException(9943000292988,'Array di dimensione errata, deve essere di un solo elemento per essere unito al resulset globale, del tipo chiave=>valore');
                                       }

                                       $returnSingleArray[] = array_values($row)[0];
                                    }

                                    $retArr = $returnSingleArray;


                                    break;
          case self::FETCH_CLASS:

                                    $retArr = $this->_pdo_statement->fetchAll(PDO::FETCH_CLASS,$className);

                                    break;

          case self::FETCH_CLASS_LATE:

                                    $retArr = $this->_pdo_statement->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE,$className);
                                    break;

          case self::FETCH_OBJ:
                                 $retArr = $this->_pdo_statement->fetch(PDO::FETCH_OBJ);
                                    break;
        }

	if($closeStatement)
   {
	   $this->_pdo_statement->closeCursor() or self::throwNewException(8973489179813," Impossibile chiudere lo statement");
      $this->_pdo_statement = null;
    }

	return $retArr;
    }


   /**
    * Restituisce il valore del campo $field, all'indice del resultSet $index
    *
    * @param Int/String $field     Campo   Indice chiave del campo intero o string
    * @param Int        $rownumber Numero della riga del resultSet
    *
    * @return Mixed valore di $field
    */
   public function fetchResult($field,$rownumber)
   {
      $result = null;
      $i      = 0;

      if($this->getNumRows()>0)
      {
         $result = false;
         while($row = $this->_pdo_statement->fetch())
         {
            if($rownumber == $i++ )
            {
               if(isset($row[$field])){
                  $result =  $row[$field];
                  break;
               }
            }
         }
      }

      if($result !== false)
      {
         return $result;
      }

      return self::throwNewException(37938457934579," Impossibile Trovare il campo {$field} all'indice {$rownumber} ");
   }

   /**
    * Restituisce il valore autoincrement per la tabella specificata
    * <br>
    * <b>Puà lanciare un eccezione qualora non sia possibile determinare il valore</b>
    *
    * @param String $table Tabella sql
    *
    * @return Mixed
    *
    */
   public function getAutoIncrementValue($table)
   {
      if($this->exeQuery("SHOW TABLE STATUS LIKE '".$table."'")!==false)
      {
           if($this->getNumRows()>0)
           {
              return $this->fetchResult("Auto_increment",0);
           }
      }

      return self::throwNewException(923094820349823,'Impossibile determinare il valore Autoincrement per la tabella: '.$table);
   }


   /**
    * Ottiene l'id del record Appena Inserito, Questo metodo prevede che prima venga eseguita una SqlQuery di INSERT
    *
    * @return Mixed id
    */
   public function getLastInsertId()
   {
      try
      {
         return $this->_pdo->lastInsertId();
      }
      catch(\Exception $e)
      {
          return false;
      }

      return false;
   }

   /**
    * Restituisce il size del ResultSet dell'ultima query eseguita
    *
    * @return Int
    */
   public function getNumRows()
   {
      if(!is_null($this->_pdo_statement))
      {
         switch($this->_driver)
         {
            case self::DRIVER_SQLITE:
            case self::DRIVER_SQLITE2:
                                       return count($this->_pdo_statement->fetchAll(PDO::FETCH_NUM)); break;
            case self::DRIVER_MYSQL:
            default:                   return $this->_pdo_statement->rowCount(); break;

         }
      }

      return 0;
   }

   /**
    * Applica il prefisso alla tabella sql
    *
    * @param String $table sql Table
    *
    * @return String
    */
   public function prefixTable($table)
   {
       if(strlen($this->_table_prefix) === 0 || strpos($table,$this->_table_prefix) === 0)
       {
          return $table;
       }

       return $this->_table_prefix.'_'.$table;
   }

   /**
    * Restituisce tutti i campi presenti nel Database per la tabella specificata, con tutte le relative informazioni che il database fornisce
    *
    * @param String $table Tabella Sql
    *
    * @return Array
    */
   public  function getTableFields($table)
   {
      $table = $this->prefixTable($table);
      $cacheData  = $this->getCacheStructureData("table_{$table}");

      if($cacheData!==false){
         return $cacheData;
      }

      $sqlQuery = "SHOW COLUMNS FROM  `{$table}`";
      $res      = $this->exeQuery($sqlQuery);

      $returnArray = Array();

      if($res!==false)
      {
          $fields    = $this->fetchArrayResultSet();
          foreach($fields as $field)
          {
             $fieldName    = $field["Field"];
             $sqlFieldInfo = "SELECT * FROM information_schema.columns WHERE table_name = ? AND column_name = ?  LIMIT 0,30";
             $res = $this->exeQuery($sqlFieldInfo,Array($table,$fieldName));
             if($res !== false){
                $returnArray[$fieldName] = array_change_key_case($this->fetchResultSet(),CASE_LOWER);
             }
          }
      }

      $this->storeCacheStructureData("table_{$table}",$returnArray);

      return $returnArray;
   }

   /**
    * Restituisce l'array con i dati filtrati in base ai campi della tabella
    *
    * @param Array  $data    Dati da filtrare
    * @param String $table   Tabella sql
    *
    * @return Array
    */
   public function filterTableField(array $data,$table)
   {
       if(empty($data))
       {
           return false;
       }


       $tableFields = $this->getTableFields($table);

       $dataFiltered = array();

       foreach($tableFields as $field => $info)
       {
           $fieldData = array_key_exists($field,$data) ? $data[$field] : '__EMPTY__';

           if((string) $fieldData != '__EMPTY__')
           {
               $dataFiltered[$field] = $fieldData;
           }
       }

       return $dataFiltered;
   }

   /**
    * Restitusice i dati strutturali del database salvati in cache, sfruttando il sistema di caching Attivo e configurato
    *
    * @param String $key Nome chiave da ricercare
    *
    * @return Mixed
    */
   public function getCacheStructureData($key)
   {
      $key = "DAO_".$key;

      if(getApplicationKernel()->isDebugActive())   //a debug attivo non sfrutto il caching
      {
          return false;
      }

      if(isset(self::$_cache_structure_data[$key]))
      {
         return self::$_cache_structure_data[$key];
      }

      self::$_cache_structure_data[$key]  = DAO_CacheManager::isActive() ? DAO_CacheManager::getInstance()->fetch($key) : false;

      return self::$_cache_structure_data[$key];
   }

   /**
    * Salva su cache la chiave con valore $value
    *
    * @param String $key    Nome della chiave
    * @param Mixed  $value  Valore mixed da storare
    * @param Int    $ttl    Time to live della chiave, default 0 = eterno :)
    *
    * @return Boolean
    */
   protected function storeCacheStructureData($key,$value,$ttl = 0)
   {
      $key = "DAO_".$key;

      $res = DAO_CacheManager::isActive() ? DAO_CacheManager::getInstance()->store($key,$value,$ttl) : false;

      self::$_cache_structure_data[$key] = $value;

      return $res;
   }

   /**
    * Restituisce Tutte le tabelle del database, con i relativi campi associati
    *
    * @return Array
    */
   public  function getSchemaMap()
   {
      $sqlQuery  = "SHOW TABLES";
      $res       = $this->exeQuery($sqlQuery);

      $schemaMap = Array();

      if($res !== false)
      {
        $allTables = $this->fetchArrayResultSet(self::FETCH_ARRAY);

        foreach($allTables as $tableArray)
        {
           $table    = $tableArray[0];
           $schemaMap[$table] = $this->getTableFields($table);
        }
      }

      return $schemaMap;
   }

   /**
    * Restituisce l'ultima SQL Query
    * <b>Attenzione, Non tiene traccia delle Transaction</b>
    * @return String
    */
   public static function getLastSql()
   {
      return static::$_last_sql;
   }


   /**
    * Restituisce il nr di query eseguite
    * @return Int
    */
   public static function getQueryExecutedNumber()
   {
      return static::$_query_numbers;
   }

   /**
    * Costruisce la stringa DSN per il driver db utilizzato
    *
    * @return String or Thrown Exception
    *
    */
   private function _buildDSNString()
   {
      switch($this->_driver)
      {
         case self::DRIVER_SQLITE:
         case self::DRIVER_SQLITE2:
                                    return $this->_driver.":".$this->_dbname; break;
         case self::DRIVER_MYSQL:
         default:                   return $this->_driver.":host=".$this->_host.";dbname=".$this->_dbname.";charset=".$this->_charset; break;

      }

      return self::throwNewException(484883772784904,'Cannot find Driver: '.$this->_driver);
   }

   /**
    * Controlla esistenza file di configurazione db e delle costanti necessarie
    *
    * @return Boolean
    */
   private function _checkConfiguration()
   {
       if(!$this->getConfigValue("DB_MANAGER_CONFIGS"))
       {
          return false;
       }

       if(!$this->getConfigValue("DB_MANAGER_CONFIG_DEFAULT"))
       {
          return false;
       }

       return true;
   }


   /**
    * Inizializza l'array del manager che contiene tutte le configurazioni configurate nel file di configs
    *
    * @return \DAO_DBManager
    */
   private function initManagerConfigs()
   {
       $managerConfigs          = $this->getConfigValue('DB_MANAGER_CONFIGS');
       self::$_MANAGERS_CONFIGS = $managerConfigs;

       return $this;
   }


   /**
    * Lancia Eccezione Cercando errori da PDO
    * @return Boolean
    */
   private function throwStatementError()
   {
      $errorArr     = $this->_pdo_statement->errorInfo();
      $errorMessage = $errorArr[2];
      return self::throwNewException(1928302," Query Fallita: ".$this->_pdo_statement->queryString.", [ERROR CODE] : ".$this->_pdo_statement->errorCode().", [MESSAGE] : ".$errorMessage);
   }


   public function __sleep()
   {
      return array();
   }


   public function __wakeup()
   {
      $this->__construct();
      $this->openConnection();
   }
}
