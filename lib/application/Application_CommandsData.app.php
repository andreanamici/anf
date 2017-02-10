<?php


/**
 * Questa classe rappresenta le informazioni del comando parsato da una stringa, che viene elaborata dal Manager dei Commands (Application_Commands)
 * 
 * Tale classe andrà data in pasto al manager Application_Commands per il processing del command ricercato
 * 
 */
class Application_CommandsData
{
   
   /**
    * Nome del comando
    * 
    * @var String
    */
   protected $_commandName = false;
   
   /**
    * Parametri del comando
    * 
    * @var Application_ArrayBag 
    */
   protected $_params      = null;
   
   /**
    * Opzioni del comando
    * 
    * @var Application_ArrayBag
    */
   protected $_options     = null;
   
   
   
   /**
    * Imposta il nome del comando
    * 
    * @param String $commandName Nome comando
    * 
    * @return \Application_CommandsData
    */
   public function setCommandName($commandName)
   {
      $this->_commandName = $commandName;
      return $this;
   }
   
   /**
    * Imposta i parametri del comando
    * 
    * @param array $params Parametri
    * 
    * @return \Application_CommandsData
    */
   public function setParams(array $params)
   {
      $this->_params = new Application_ArrayBag($params);
      return $this;
   }
   
   /**
    * Imposta le opzioni del comando
    * 
    * @param array $options   Opzioni
    * 
    * @return \Application_CommandsData
    */
   public function setOptions(array $options)
   {
      $this->_options = new Application_ArrayBag($options);
      return $this;
   }
   
   /**
    * Restituisce il nome del comando
    * 
    * @return String
    */
   public function getCommandName()
   {
      return $this->_commandName;
   }
   
   /**
    *  Restituisce i parametri
    * 
    * @return Application_ArrayBag
    */
   public function getParams()
   {
      return $this->_params;
   }
   
   /**
    * Restituisce le opzioni, precedute  con "--"
    * 
    * @return Application_ArrayBag
    */
   public function getOptions()
   {
      return $this->_options;
   }
   
   
  /**
   * Questa classe rappresenta le informazioni del comando parsato da una stringa, che viene elaborata dal Manager dei Commands (Application_Commands)
   * 
   * Tale classe andrà data in pasto al manager Application_Commands per il processing del command ricercato
   * 
   * 
   * @param array $commandsDataArray Rappresenta le informazio per inizializzare l'oggetto, vanno forniti:
   *                                 <ul>
   *                                    <li>[String] command Nome del comando</li>
   *                                    <li>[Array]  params  Parametri</li>
   *                                    <li>[Array]  options Opzioni</li>
   *                                 </ul>
   * 
   */
   public function __construct(array $commandsDataArray = array())
   {
      if(isset($commandsDataArray["command"]))
      {
         $this->setCommandName($commandsDataArray["command"]);
      }
      
      if(isset($commandsDataArray["params"]))
      {
         $this->setParams($commandsDataArray["params"]);
      }
      
      if(isset($commandsDataArray["options"]))
      {
         $this->setOptions($commandsDataArray["options"]);
      }
      
      return true;
   }
}