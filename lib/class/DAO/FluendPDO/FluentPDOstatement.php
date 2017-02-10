<?php

/**
 * PDOStatement Wrapper
 */
class FluentPDOstatement
{
   
   /**
    * Statement Parameter
    * @var Array
    */
   private $_statement_parameters = Array();
   
   /**
    * Statement 
    * @var PDOStatement
    */
   private $_statement            = null;
   
   /**
    * FluentPDO statement Wrapper class
    * 
    * @param PDOStatement $statement
    * @param Array $parameters
    */
   public function __construct(PDOStatement $statement = null, $parameters = Array())
   {
      $this->_statement            = $statement;
      $this->_statement_parameters = $parameters;
   }
   
   /**
    * Set PDOStatement
    * @param PDOStatement $statement
    * @return Boolean
    */
   public function setStatement(PDOStatement $statement){
      $this->_statement = $statement;
      return $this;
   }
   
   /**
    * Set Statement Parameter
    * @param Array $parameter
    * @return Boolean
    */
   public function setParameter($parameter){
      $this->_statement_parameters = $parameter;
      return $this;
   }
         
   /**
    * Return Statement
    * @return PDOStatement
    */
   public function getStatement(){
      return $this->_statement;
   }
   
   /**
    * Return Parameter for execute statement
    * @return Array
    */
   public function getParameter(){
      return $this->_statement_parameters;
   }

}
