<?php

/**
 * Classe astratta da estendere per creare un command
 */
abstract class Abstract_Commands implements Interface_Commands
{
   use Trait_DAO,Trait_ObjectUtilities,Trait_Singleton;
   
   use Trait_ApplicationKernel, 
           
       Trait_ApplicationRouting,
           
       Trait_ApplicationPlugins, 
           
       Trait_ApplicationHooks, 
           
       Trait_ApplicationConfigs,
           
       Trait_ApplicationLanguages;
   
   /**
    * Indica il comando successivo
    * @var String
    */
   private $_next_cmd_name = false;
   
   
   /**
    * Response del command
    * @var Mixed
    */
   private $_response      = false;
   
   /**
    * Parametri da passare al comando
    * 
    * @var Application_ArrayBag
    */
   private $_params        = null;
   
   
   /**
    * Opzioni da passare al comando
    * 
    * @var Application_ArrayBag 
    */
   private $_options       = null;
   
   
   /**
    * Contiene la configurazione delle opzioni gestite da questo comando
    * @var array
    */
   private $_options_configuration = array();
   
   /**
    * Contiene le condifurazioni dei parametri gestiti da questo comando
    * @var array
    */
   private $_params_configuration  = array();
   
   
   /**
    * Restiuisce il nome del comando
    * 
    * @return String Nome della classe senze il suffix "Command_"
    */
   public function getName()
   {
      return str_replace("Command_","",get_called_class());
   }
   
   /**
    * Restituisce la description del command
    */
   public function getDescription()
   {
      return "[no description]";
   }
   
   /**
    * Restituisce la mappatura dei parametri passati dal Gestore dei commands. Questa mappatura è utile per 
    * ricercare i parametri tramite nome e non solamente tramite posizione int
    * 
    * @return boolean
    */
   public static function getParametersSchema()
   {
      return false;
   }
   
   /**
    * Restituisce la descrizione dei parametri
    * 
    * @return string
    */
   public function getParametersDescription()
   {
      return "";
   }
   
   
  /**
    * Restituisce la descrizione delle opzioni
    * 
    * @return string
    */
   public function getOptionsDescription() 
   {
      return  "  
            String   --env     Indica l'environment del Kernel
            String   --debug   Indica il debug del Kernel
      "; 
   }
   
 
   /**
    * Imposta i parametri da utilizzare in fase di esecuzione del comando
    * 
    * @param Application_ArrayBag $params parametri (in ordine di richiesta)
    * 
    * @return \Abstract_Commands
    */
   public function setParams(Application_ArrayBag $params = null)
   {
      $this->_params = $params;
      return $this;
   }
   
   
   /**
    * Restituisce i parametri utilizzati da questo comando
    * 
    * @return Application_ArrayBag
    */
   public function getParams()
   {
      return $this->_params;
   }
   
   /**
    * Ricerca un parametro passato a questo comando, verificando l'esistenza della mappatura dei parametri
    * 
    * @param String $key      Chiave, o posizione intera
    * @param Mixed  $default  [OPZIONALE] Valore di default da restituire, default FALSE
    * 
    * @return Mixed
    */
   public function getParam($key,$default = false)
   {
      
      $parameterSchema = $this->getParametersSchema();
      
      if(!$parameterSchema)
      {
         return $this->getParams()->getIndex($key,$default);
      }
      
      foreach($parameterSchema as $position => $name)
      {
         if($key == $name)
         {
            return $this->getParams()->getIndex($position,$default);
         }
      }
      
      return $default;
   }
   
   /**
    * Restituisce le options utilizzate da questo comando
    * 
    * @return Application_ArrayBag
    */
   public function getOptions()
   {
      return $this->_options;
   }
   
   
   /**
    * Restituisce l'option relativa alla chiave specificata utilizzate da questo comando
    * 
    * @param String $key      Chiave
    * @param Mixed  $default  [OPZIONALE] Valore di default da restituire, default FALSE
    * 
    * @return Mixed
    */
   public function getOption($key,$default = false)
   {
      return $this->getOptions()->getIndex($key, $default);
   }
   
   
   /**
    * Imposta le options da utilizzare in fase di esecuzione del comando
    * 
    * @param Application_ArrayBag $options parametri Opzionali (--option=<value>)
    * 
    * @return \Abstract_Commands
    */
   public function setOptions(Application_ArrayBag $options = null)
   {
      $this->_options = $options;
      return $this;
   }  
   
   
   /**
    * Imposta il nome del comando che verrà eseguito dopo il termine di questo
    * 
    * @param String $name Nome del comando
    * 
    * @return \Abstract_Commands
    */
   public function setNextCommand($name)
   {
      if(!is_string($name))
      {
         return self::throwNewException(1927371232573263, 'Il comando in cascata può essere solamente un comando stringa');
      }
      
      $this->_next_cmd_name = $name;
      return $this;
   }
   
   /**
    * Restituisce il nome del comando da lanciare al termine di questo
    * 
    * @param String $name Nome del comando
    * 
    * @return \Abstract_Commands
    */
   public function getNextCommand()
   {
      return $this->_next_cmd_name;
   }
   
   /**
    * Imposta la response del comando
    * 
    * @param Mixed $response
    * 
    * @return \Abstract_Commands
    */
   protected function setResponse($response)
   {
      $this->_response = $response;
      return $this;
   }
   
   
   /**
    * Inizializza l'applicationCommand
    * 
    * @return Application_Commands
    */
   public function __construct()
   {      
       
   }
   
   /**
    * Elabora questo comando
    * 
    * <b>Questo metodo va sovrascritto per l'elaborazione del comando figlio</b>
    * 
    */
   public function doProcessMe()
   {
       return true;  
   }
   
   /**
    * Restituisce la response del comando
    * 
    * @return Mixed
    */
   public function getResponse()
   {
      return $this->_response;
   }   
   
   
   /**
    * Stampa a video l'helper per usare il comando da CLI
    * @return String
    */
   public function getHelper()
   {
       return $this->getName();
   }
   
   /**
    * Print nome del command
    * 
    * @return String
    */
   public function __toString()
   {  
      return $this->getHelper();
   }
   
}
