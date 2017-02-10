<?php

/**
 * Questo trait mette a disposizione l'accesso al DAO, sfruttando i services basilari del framework
 * 
 * <ul>
 *  <li>@database</li>
 *  <li>@cache</li>
 *  <li>@session</li>
 *  <li>@cookie</li>
 * </ul>
 * 
 */
trait Trait_DAO
{
   /**
    * Rifermento al db
    * 
    * @var DAO_DBManager 
    */
   protected   $_db                   = null; 

   /**
    * Riferimento alla classe per la costruzione di Statement o sql native
    * 
    * @var DAO_DBsqlStatementBuilder
    * 
    * @deprecated Rimuovere a breve
    */
   protected   $_sqlBuilder           = null;
   
   /**
    * Rifermento al Cache Manager
    * 
    * @var DAO_CacheManager
    */
   protected   $_cc                   = null;
   
   /**
    * Rifermento al Session Manager
    * 
    * @var Application_SessionManager
    */
   protected   $_sm                   = null;
   
   /**
    * Rifermento al Cookie Manager
    * 
    * @var Application_CookieManager
    */
   protected   $_cm                   = null;   
   
   /**
    * Restituisce l'instanza del Session Manager
    * 
    * @return Application_SessionManager
    */
   public function getSessionManager()
   {
      if(is_null($this->_sm))
      {
          $this->initDAO();
      }
       
      return $this->_sm;
   }
   
   
   /**
    * Restituisce l'instanza del Cookie Manager
    * 
    * @return Application_CookieManager
    */
   public function getCookieManager()
   {
      if(is_null($this->_cm))
      {
          $this->initDAO();
      }
      
      return $this->_cm;
   }
   
   /**
    * Restituisce l'instanza del Database Manager
    * 
    * @return DAO_DBManager
    */
   public function getDatabaseManager()
   {
      if(is_null($this->_db))
      {
          $this->initDAO();
      }
      
      return $this->_db;
   }
   
   /**
    * Restituisce l'instanza del Cache Manager
    * 
    * @return DAO_CacheManager
    */
   public  function getCacheManager()
   {
      if(is_null($this->_cc))
      {
          $this->initDAO();
      }  
      
      return $this->_cc;
   }
   
   
   /**
    * Inizializzazione dell'Access Data Object
    * 
    * @return Trait_DAO
    */
   protected function initDAO()
   {
       $this->_db          = getApplicationService('database');
       $this->_cc          = getApplicationService('cache');
       $this->_cm          = getApplicationService('cookie');
       $this->_sm          = getApplicationService('session');
       $this->_sqlBuilder  = $this->_db;
       
       return $this;
   }
  
}


