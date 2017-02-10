<?php

/**
 * Classe per la scrittura di sql Query native
 */
class DAO_DBsqlBuilder extends Exception_ExceptionHandler
{
    
    public function  __construct() 
    {
        parent::__construct();
    }

    public function  __destruct() 
    {
        unset($this);
        return true;
    }

    /**
     * Crea Query Sql per INSERT
     * 
     * @param String $table Tabella 
     * @param Array $fieldsValuesArr Array associativo campo=>valore
     * @param Boolean $ignore IGNORE case
     * @param String $priority Specifica prirità insert
     * 
     * @return String
     */
    public function prepareSqlInsert($table,$fieldsValuesArr,$ignore=null,$priority=null)
    {
        if(count($fieldsValuesArr)==0){
            return self::throwNewException(349293488181,"Sql Builder Failed, field Arr is divers to valuesArr");
        }

        $ignore    = !is_null($ignore)   ? " IGNORE "  : "";
        $priority  = !is_null($priority) ? " ".$priority." "    : "";

        $sqlString = "INSERT ".$priority." ".$ignore." INTO ".$this->prefixTable($table)."  (";
                
        $fieldsValuesArr  = $this->filterTableField($fieldsValuesArr, $table);
        
        if(empty($fieldsValuesArr)){
            return false;
        }
        
        foreach($fieldsValuesArr as $field=>$value){
             $sqlString.="$field,";
        }

        $sqlString = substr($sqlString,0,strlen($sqlString)-1);
        $sqlString.= ") VALUES ('";
        $Values    = Array();

        $i=0;
        foreach($fieldsValuesArr as $field=>$val){
            $Values[$i++]=trim($val);
        }

        $sqlString.= implode("','",$Values);
        $sqlString.= "')";

        return $sqlString;
        
    }

    /**
     * Crea Query Sql per INSERT
     * 
     * @param String $table            Tabella sql
     * @param Array  $fieldsValuesArr  Valori da aggiornare
     * @param Array  $conditionArr     Condizioni, preimpostate (no statement)
     * @param String $priority         Priorità
     * 
     * @return String 
     */
    public function prepareSqlUpdate($table,$fieldsValuesArr,$conditionArr,$priority=null)
    {
        if(count($fieldsValuesArr)==0){
            return self::throwNewException(239092834789234,"Sql Builder Failed, field Arr is divers to valuesArr");
        }
        
        $priority  = !is_null($priority) ? $priority : "";
        $sqlString = " UPDATE ".$priority." ".$this->prefixTable($table)." SET ";
        $updateArr=Array();
        
        $fieldsValuesArr  = $this->filterTableField($fieldsValuesArr, $table);
        
        if(empty($fieldsValuesArr)){
            return false;
        }
        
        $i=0;
        
        foreach($fieldsValuesArr as $field=>$value)
        {
             if(strstr($value,'`')!==false){
                $updateArr[$i++]=" ".$field." = {$value} ";
             }else{
                $updateArr[$i++]=" ".$field.self::escapeString("=", $value);
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
        
        return $sqlString;
    }

    /**
     * Prepara la query di DELETE
     * 
     * @param String  $table           Tabella sql
     * @param Array   $condition       Condizioni, preimpostate (no statement)
     * @param String  $priority        Imposta priorità, LOW, HIGTH
     * 
     * @return String
     */
    public function prepareSqlDelete($table,$condition,$priority=null)
    {
        $priority  = is_null($priority) ? "" : " {$priority} PRIORITY ";
        $sqlString = " DELETE {$priority} FROM ".$table ." WHERE 1 ";
        
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
        
        return $sqlString;
    }

    /**
     * Prepara la query di SELECT
     * 
     * @param Array   $fieldArr            Campi da selezionare
     * @param String  $table               Table FROM
     * @param Array   $conditionArr        Condizioni
     * @param String  $groupBy             Raggruppamento
     * @param String  $orderBy             Ordinamento
     * @param String  $orderbyMode         Ordine ASC,DESC
     * @param Int     $limit_start         Limit start
     * @param Int     $limit_end           Limit end
     * 
     * @return String
     */
    public function prepareSqlSelect($fieldArr,$table,$conditionArr=null,$groupBy=null,$orderBy=null,$orderbyMode='ASC',$limit_start=0,$limit_end=0)
    {
        $table = $this->prefixTable($table);
        
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

        return $sqlString;

    }

    /**
     * Prepara lo statement per la query di JOIN
     * 
     * @param Array   $fieldArr            Campi da selezionare
     * @param String  $table               Table FROM
     * @param Array   $joinArr             Join,preimpostate (no statement)
     * @param Array   $condArr             Condizioni, preimpostate (no statement)
     * @param String  $groupBy             Raggruppamento
     * @param String  $orderBy             Ordinamento
     * @param String  $orderbyMode         Ordine ASC,DESC
     * @param Int     $limit_start         Limit start
     * @param Int     $limit_end           Limit end
     * 
     * @return PDOStatement
     */
    public function prepareSqlJoin($fieldArr,$table,$joinArr,$condArr=null,$groupBy=null,$orderBy=null,$orderbyMode='ASC',$limit_start=0,$limit_end=0)
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
        
        $table = $this->prefixTable($table);
        
        $sqlString = substr($sqlString,0,strlen($sqlString)-1);
        
        $sqlString.=" FROM ".$table;
        
        foreach($joinArr as $join=>$condition){
            $sqlString.= " ".$this->prefixTable($join)." ON(".$condition.") ";
        }

        $sqlString.=" WHERE 1";

        if(is_array($condArr) && count($condArr)>0)
        {
           foreach($condArr as $field=>$condition_value){
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

        return $sqlString;

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
        if(strlen($condition)>0 && strlen($value)>0){
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
