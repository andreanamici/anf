<?php

/**
 * Classe per la scrittura di sql Query con statement PDO
 */
class DAO_DBsqlStatementBuilder extends DAO_DBsqlBuilder
{
    
    public function  __construct()
    {
       return true;
    }

    public function  __destruct() 
    {
        unset($this);
        return true;
    }

    /**
     * Prepare lo statement per la query di Insert
     * 
     * @param String     $table Tabella 
     * @param Array      $fieldsValuesArr Array associativo campo=>valore
     * @param Boolean    $ignore IGNORE case
     * @param String     $priority Specifica prirità insert
     * 
     * @return PDOStatement
     */
    public function prepareStatementInsert($table,$fieldsValuesArr,$ignore=null,$priority=null)
    {
        if(count($fieldsValuesArr)==0){
            return self::throwNewException(394503845349,"Impossibile costruire la query di insert");
        }

        $ignore    = !is_null($ignore)   ? " IGNORE "  : "";
        $priority  = !is_null($priority) ? " ".$priority." "    : "";
        
        $table = $this->prefixTable($table);

        $fieldsValuesArr  = $this->filterTableField($fieldsValuesArr, $table);
        if(empty($fieldsValuesArr)){
            return false;
        }   
        
        $sqlString = "INSERT ".$priority." ".$ignore." INTO ".$table."  (";

        foreach($fieldsValuesArr as $field=>$value){
             $sqlString.=" $field,";
        }

        $sqlString = substr($sqlString,0,strlen($sqlString)-1);
        $sqlString.= ") VALUES (";
        $Values    = Array();

        $i=0;
        foreach($fieldsValuesArr as $field=>$val){
            $Values[$i++] = " :{$field} "; 
        }

        $sqlString.= implode(",",$Values);
        $sqlString.= ")";
        
        $stmt = $this->_pdo->prepare($sqlString);
        
        foreach($fieldsValuesArr as $field => &$val){
            $stmt->bindParam(":".$field, $val);
        }
        
        return $stmt;
        
    }
    
    /**
     * Prepare lo statement per la query di Update
     * 
     * @param String $table            Tabella sql
     * @param Array  $fieldsValuesArr  Valori da aggiornare
     * @param Array  $conditionArr     Condizioni, preimpostate (no statement)
     * @param String $priority         Priorità
     * 
     * @return PDOStatement
     */
    public function prepareStatementUpdate($table,$fieldsValuesArr,$conditionArr,$priority=null)
    {
        if(count($fieldsValuesArr)==0){
            return self::throwNewException(3020938747823922,"Sql Builder Failed, field Arr is divers to valuesArr");
        }
        
        $table = $this->prefixTable($table);
        
        $priority  = !is_null($priority) ? $priority : "";
        $sqlString = " UPDATE ".$priority." ".$table." SET ";
        $updateArr = Array();
                
        $fieldsValuesArr  = $this->filterTableField($fieldsValuesArr, $table);
        
        if(empty($fieldsValuesArr)){
            return false;
        } 
        
        $i=0;
        
        foreach($fieldsValuesArr as $field=>$value)
        {
            if(strstr($value,'`')!==false){
                $updateArr[$i++] = " ".$field." = {$value} ";
             }else{
                $updateArr[$i++] = " ".$field."= :{$field} ";
             }
        }
     
        $sqlString.=implode(",",$updateArr);
        $sqlString.=" WHERE 1";
        
        if(is_array($conditionArr) && count($conditionArr)>0)
        {
           foreach($conditionArr as $field=>$condition_value)
           {
                $sqlStringCond = "  AND (".$field.' '.$condition_value.") ";
                if($this->_checkSqlInjection($condition_value)){
                  $sqlString.= $sqlStringCond;
                }else{
                  $sqlString.=" AND (1=0) "; //Condizione falsa! :P
                }
           }
        }
        
        $stmt = $this->_pdo->prepare($sqlString);

        foreach($fieldsValuesArr as $field => &$val)
        {
            if(strstr($val,'`')===false){
               $stmt->bindParam(":".$field, $val);
            }
        }
        
        return $stmt;
    }
    
    /**
     * Prepara lo statement per la query di DELETE
     * 
     * @param String  $table           Tabella sql
     * @param Array   $condition       Condizioni, preimpostate (no statement)
     * @param String  $priority        Imposta priorità, LOW, HIGTH
     * 
     * @return PDOStatement
     */
    public function prepareStatementDelete($table,$condition,$priority=null)
    {
        $table = $this->prefixTable($table);
                
        $priority  = !is_null($priority) ? $priority : "";
        $sqlString=" DELETE {$priority} FROM ".$table ." WHERE 1 ";
                
        if(is_array($condition) && count($condition)>0)
        {
            foreach($condition as $field=>$condition_value)
            {
                $sqlStringCond = "  AND (".$field.' '.$condition_value.") ";
                if($this->_checkSqlInjection($condition_value)){
                  $sqlString.= $sqlStringCond;
                }else{
                  $sqlString.=" AND (1=0) "; //Condizione falsa! :P
                }
            }
        }
        
        $stmt = $this->_pdo->prepare($sqlString);
        
        return $stmt;
    }
    
    /**
     * Prepara lo statement per la query di SELECT
     * 
     * @param Array   $fieldArr            Campi da selezionare
     * @param String  $table               Table FROM
     * @param Array   $conditionArr        Condizioni, preimpostate (no statement)
     * @param String  $groupBy             Raggruppamento
     * @param String  $orderBy             Ordinamento
     * @param String  $orderbyMode         Ordine ASC,DESC
     * @param Int     $limit_start         Limit start
     * @param Int     $limit_end           Limit end
     * 
     * @return PDOStatement
     */
    public function prepareStatementSelect($fieldArr,$table,$conditionArr=null,$groupBy=null,$orderBy=null,$orderbyMode='ASC',$limit_start=0,$limit_end=0)
    {
        $sqlString=" SELECT ";

        if(!is_array($fieldArr) && strlen($fieldArr)>0)
            $sqlString.=" {$fieldArr} ";
        else
        {
           foreach($fieldArr as $field){
             $sqlString.="$field,";
           }
        }

        $sqlString = substr($sqlString,0,strlen($sqlString)-1);
        
        $table = $this->prefixTable($table);
                
        $sqlString.=" FROM ".$table;
        $sqlString.=" WHERE 1 ";

        if(is_array($conditionArr) && count($conditionArr)>0)
        {
           foreach($conditionArr as $field=>$condition_value)
           {                
                $sqlStringCond = "  AND (".$field.' '.$condition_value.") ";
                if($this->_checkSqlInjection($condition_value)){
                  $sqlString.= $sqlStringCond;
                }else{
                  $sqlString.=" AND (1=0) "; //Condizione falsa! :P
                }
           }
        }
        
        if(!is_null($groupBy)  && strlen($groupBy)>0)
           $sqlString.=" GROUP BY ".$groupBy;

        if(!is_null($orderBy)  && strlen($orderBy)>0)
           $sqlString.=" ORDER BY ".$orderBy." ".$orderbyMode;
        
        if(!is_null($limit_start) && $limit_start>=0 && $limit_end>0){
           $sqlString.=" LIMIT ".$limit_start.",".$limit_end;
        }
        else if(!is_null($limit_start) && $limit_start>=0 && is_null($limit_end)){
           $sqlString.=" LIMIT ".$limit_start;
        }
        
        $stmt = $this->_pdo->prepare($sqlString);

        return $stmt;
    }

    /**
     * Prepara lo statement per la query di JOIN
     * 
     * @param Array   $fieldArr            Campi da selezionare
     * @param String  $table               Table FROM
     * @param Array   $joinArr             Join,preimpostate
     * @param Array   $condArr             Condizioni, preimpostate (no statement)
     * @param String  $groupBy             Raggruppamento
     * @param String  $orderBy             Ordinamento
     * @param String  $orderbyMode         Ordine ASC,DESC
     * @param Int     $limit_start         Limit start
     * @param Int     $limit_end           Limit end
     * 
     * @return String
     */
    public function prepareStatementJoin($fieldArr,$table,$joinArr,$condArr=null,$groupBy=null,$orderBy=null,$orderbyMode='ASC',$limit_start=0,$limit_end=10)
    {
        $sqlString=" SELECT ";

        if(!is_array($fieldArr) && strlen($fieldArr)>0)
            $sqlString.=" {$fieldArr} ";
        else
        {
           foreach($fieldArr as $field){
             $sqlString.="$field,";
           }
        }

        $sqlString = substr($sqlString,0,strlen($sqlString)-1);

        $table = $this->prefixTable($table);

        $sqlString.=" FROM ".$table;
        
        foreach($joinArr as $join=>$condition){
            $sqlString.= " ".$this->tablePrefix($join)." ON(".$condition.") ";
        }

        $sqlString.=" WHERE 1";

        if(!is_null($condArr))
        {
           foreach($condArr as $field=>$condition_value)
           {
               $sqlStringCond = "  AND (".$field.' '.$condition_value.") ";
               if($this->_checkSqlInjection($condition_value)){
                  $sqlString.= $sqlStringCond;
               }else{
                  $sqlString.=" AND (1=0) "; //Condizione falsa! :P
               }
           }
        }

        if(!is_null($groupBy) && strlen($groupBy)>0)
           $sqlString.=" GROUP BY ".$groupBy;

        if(!is_null($orderBy) && strlen($orderBy)>0)
           $sqlString.=" ORDER BY ".$orderBy." ".$orderbyMode;

        if(!is_null($limit_start) && $limit_start>=0 && $limit_end>0){
           $sqlString.=" LIMIT ".$limit_start.",".$limit_end;
        }
        else if(!is_null($limit_start) && $limit_start>=0 && is_null($limit_end)){
           $sqlString.=" LIMIT ".$limit_start;
        }

        
        $stmt = $this->_pdo->prepare($sqlString);
        
        return $stmt;

    }

    /**
     * Effettua l'escape di un valore su condizione 
     * 
     * @param String  $condition Sql, =,>,<,<>,!= etc
     * @param Mixed   $value Valore
     * 
     * @return String 
     */
    public function escape($cond='',$value='')
    {
        if($value=='')
            return $cond;
        return " {$cond} '".  addslashes($value)."' ";
    }
    
    /**
     * Effettua l'escape di un valore su condizione 
     * 
     * @param String  $condition Sql, =,>,<,<>,!= etc
     * @param Mixed   $value Valore
     * 
     * @return String 
     */
    public static function escapeString($condition,$value)
    {
        if(strlen($condition)>0 && strlen($value)>0)
        {
            return " {$condition} '".addslashes($value)."'";
        }
        
        return false;
    }
    
    
    /**
     * Controlla stringa per evenutali sqlInjection
     * 
     * @param String $sqlCondtion Condizione Sql
     * 
     * @return boolean 
     */
    private function _checkSqlInjection($sqlCondtion)
    {
       /**
        * @TODO controllo sql  
        */
       return true;
    }
}
