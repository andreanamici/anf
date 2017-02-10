<?php

/**
 * Classe che gestisce i commands
 */
class Application_Commands extends Application_CliColors implements Interface_ApplicationCommands
{
      
   use Trait_ObjectUtilities,Trait_Singleton;

   use Trait_ApplicationKernel;
   
   
   private static $_STDIN_USER_QUIT = Array('q','quit');
   
   const COMMAND_CLI_EXIT_CODE = 3949394;
   
   
   /**
    * Iteratore, contiene tutti i riferimenti ai comandi registrati all'applicazione
    * 
    * @var ArrayIterator 
    */
   protected $_COMMANDS_ITERATOR = null;
   
   
   /**
    * Indica se i comandi sono abilitati
    * @var Boolean
    */
   protected $_is_enable                = true; 
   
   
   /**
    * Risorsa al stdin aperto con la console
    * @var resource
    */
   private  $_stdin_resource          = null;

   /**
    * Stringa digitata dall'utente
    * @var String
    */
   private  $_current_user_input      = null;
   
   
   
   /**
    * Classe manager degli hooks attivi sul portale
    * 
    * @return boolean
    */
   public function __construct() 
   {
      parent::__construct();
      
      $this->_is_enable = defined("APPLICATION_COMMANDS_ENABLE") ? APPLICATION_COMMANDS_ENABLE : true;
      
      if($this->_is_enable)
      {
         return $this->inizializeCommandsStackIterator();
      }
      
      return self::writeLog('Hooks disabilitati',Application_Kernel::LOG_FILE_NAME);
   }
   
   
   /**
    * Binda la lettura del input che digita l'utente con l'stdin
    * 
    * @return \Application_Commands
    */
   public function bindStdin()
   {
      $this->_stdin_resource = fopen("php://stdin","r");
      return $this;
   }
   
   
   /**
    * Legge l'input digitato dall'utente e controlla che non sia per l'exit dalla console
    * 
    * @return boolean
    */
   public function readUserInput()
   {
      $this->_current_user_input = trim(fgets($this->_stdin_resource));
            
      if(in_array($this->_current_user_input, self::$_STDIN_USER_QUIT)){
         return self::throwNewException(self::COMMAND_CLI_EXIT_CODE,'exit from cli mode');
      }
      
      if(strlen($this->_current_user_input) > 0){
         return true;
      }
      
      return false;
   }
   
   /**
    * Restituisce la stringa digitata dall'utente
    * 
    * @return String
    */
   public function getUserInput()
   {
      return $this->_current_user_input;
   }
   
   /**
    * Effettua il parsing di una stringa restituendo un Application_CommandsData object, per l'esecuzione del command
    * 
    * @param type $string  Stringa sulla quale effettuare il parsing per ricercare comando e parametri
    * 
    * @return Application_CommandsData
    */
   public static function getParseCommand($string)
   {
      $string      = trim($string);
      
      $commandName = false;
      $params      = array();
      $options     = array();
      
      if(strlen($string) > 0)
      {
         if(preg_match_all('/--([A-z0-9\.\_\-]+)\=\"(.*)\"/',$string,$matches)) // trovo delle opzioni da passare al command
         {
             if(is_array($matches[0]) && count($matches[0]) > 0)
             {
                 foreach($matches[0] as $key => $option)
                 {
                     $string          = str_replace($option,"",$string);
                     $field           = $matches[1][$key];
                     $value           = $matches[2][$key];
                     $options[$field] = $value;
                 }
             }
         }
         
         if(preg_match_all('/--([A-z0-9\.\_\-]+)\=(.*)/',$string,$matches)) // trovo delle opzioni da passare al command
         {
             if(is_array($matches[0]) && count($matches[0]) > 0)
             {
                 foreach($matches[0] as $key => $option)
                 {
                     $string          = str_replace($option,"",$string);
                     $field           = $matches[1][$key];
                     $value           = $matches[2][$key];
                     $options[$field] = $value;
                 }
             }
         }
         
         if(preg_match_all('/--([A-z0-9\.\_\-]+)/',$string,$matches))  // Opzione del comando senva valore --option
         {
             if(is_array($matches[0]) && count($matches[0]) > 0)
             {
                 foreach($matches[0] as $key => $option)
                 {
                     $string          = str_replace($option,"",$string);
                     $field           = $matches[1][$key];
                     $value           = true;
                     $options[$field] = $value;
                 }
             }
         }
          
         $stringArray = explode(' ', $string);
               
         if(count($stringArray) == 0)
         {
            $stringArray = array($string);
         }
         if(is_array($stringArray) && count($stringArray) > 0)
         {
            foreach($stringArray as $key => $stringToken)
            {
               if($key == 0 && strlen($stringToken) > 0 && strpos($stringToken,"--") === false)
               {
                  $commandName = $stringToken;
               }
               else
               {
                  if(strpos($stringToken,"--") === false)                                              //Parametro
                  {
                     $params[] = $stringToken;
                  }
               }
            }
         }
      }

      return  self::generateCommandsData($commandName, $params, $options);
   }
   
   
   /**
    * Genera un oggetto CommandsData utilie per processare il command
    * 
    * @param String $commandName Nome del comando
    * @param array  $params      [OPZIONALE] Parametri del command, default Array()
    * @param array  $options     [OPZIONALE] Options del command, default Array()
    * 
    * @return \Application_CommandsData
    */
   public static function generateCommandsData($commandName,array $params = Array(),array $options = Array())
   {
       return new Application_CommandsData(array(
                                 'command' => $commandName,
                                 'params'  => $params,
                                 'options' => $options
       ));
   }
   
   /**
    * Effettua il parsing dell'attuale argv passato
    * 
    * @param type $string  Stringa sulla quale effettuare il parsing per ricercare comando e parametri
    * 
    * @return Application_CommandsData   Dati per processare un comando
    */
   public static function getParseCommandsFromArgv(array $argv)
   {
      $argvArray        = is_array($argv) && count($argv) > 0 ? array_slice($argv, 1) : array();
      $argvString       = implode(" ",$argvArray);
      
      return self::getParseCommand($argvString);
   }
   
   /**
    * Restituisce TRUE se i commands sono abilitati, false Altrimenti
    * 
    * @return Boolean
    */
   public function isEnable()
   {
      return $this->_is_enable;
   }
   
   /**
    * Imposta se gli hooks sono abilitati o meno
    * 
    * @param Boolean $enable  TRUE o FALSE
    * 
    * @var Boolean
    */
   public function setEnable($enable)
   {
      $this->_is_enable = $enable;
      return false;
   }
   
   
   /**
    * Restituisce l'iterator che contiene tutti i commands registrati
    * 
    * @return ArrayIterator
    */
   public function getAllCommands()
   {
      return $this->_COMMANDS_ITERATOR;
   }
   
   /**
    * Ricerca un comando tra quelli registrati
    * 
    * @param String $commandName Nome del comando
    * 
    * @return \Abstract_Commands    Comando
    * 
    * @throws \Exception se commando non esiste
    */
   public function getCommand($commandName)
   {
       if($this->_COMMANDS_ITERATOR->count() > 0)
       {
           foreach($this->_COMMANDS_ITERATOR as $command) /*@var $command \Abstract_Commands*/
           {
               if($command->getName() == $commandName)
               {
                   return $command;
               }
           }           
       }
       
       return $this->throwNewException(8126484367945, 'Il comando ricercato non è presente: '.$commandName);
   }
   
   /**
    * Verifica che vi sia registrato un comando con il nome indicato
    * 
    * @param String $commandName Nome del comando
    * 
    * @return Boolean
    */
   public function hasCommand($commandName)
   {
       try
       {
           if($this->getCommand($commandName))
           {
               return true;
           }
       } 
       catch (Exception $e) 
       {
           return false;
       }
       
       return false;
   }
   
   /**
    * Registra i comandi presenti nel package
    * 
    * @param Abstract_Package $package package
    * 
    * @return Int
    */
   public function registerCommandsForPackage(Abstract_Package $package)
   {
      $directory = $package->getCommandsPath();
      return $this->registerCommandsInDirectory($directory);
   }
   
   
   /**
    * Esegue un comando, richiamabile sia da CLI che da web
    * 
    * @param String                 $commandName Nome del comando
    * @param Application_ArrayBag   $params      [OPZIONALE]  Parametri da passare al commando, default NULL
    * @param Application_ArrayBag   $options     [OPZIONALE]  Opzioni da passare al commando, default NULL
    * 
    * @return Mixed
    */
   public function executeCommand($commandName,Application_ArrayBag $params = null, Application_ArrayBag $options = null)
   {
      try
      {
         $kernel = $this->getApplicationKernel();

         if($this->_COMMANDS_ITERATOR->count() > 0 )
         {
            foreach($this->_COMMANDS_ITERATOR as $command) /*@var $command Abstract_Commands*/
            {
               $cmdInfo = $this->getCommandExecutionInfo($command,$commandName);
               
               if(is_array($cmdInfo) && count($cmdInfo) > 0)
               {
                  $cmdName       = $cmdInfo["name"];
                  $cmdMethodName = $cmdInfo["method"];
                  
                  if($cmdName == $commandName)
                  {
                     $command->setParams($params)
                             ->setOptions($options);
                              
                     $currentEnv   = $kernel->getEnvironment();
                     $currentDebug = $kernel->isDebugActive();

                     if($options->getVal('env'))
                     {
                        $kernel->setEnvironment($options->getVal('env'));
                     }
                     
                     if($options->getVal('debug'))
                     {
                        $kernel->setDebug((bool) $options->getVal('debug'));
                     }
                     
                     if($options->getIndex('help') == 1)
                     {
                        return $command->getHelper();
                     }
                     else if($command->$cmdMethodName())
                     {
                        
                        $kernel->setEnvironment($currentEnv)
                               ->setDebug($currentDebug);
                        
                        if($commandName == $command->getNextCommand())   //Sto richiamando se stesso come comando successivo
                        {
                           return self::throwNewException(234982839471823842834, 'Il comando '.$command->getName() .' sta tendando di richiamre se stesso come comando successivo.');
                        }
                        else if($command->getNextCommand()!==false)              //Il comando richiama un altro comando
                        {
                           return $this->executeCommand($command->getNextCommand(),$params,$options);
                        }
                        
                        return $command->getResponse();
                     }
                     else
                     {
                        return false;
                     }
                  }
               }
            }
         }
      }
      catch(\Exception $e)
      {
         return $e;
      }
      
      return self::throwNewException(283984938762372638390, 'Impossibile trovare il comando: '.$commandName.', nessun comando è registrato!');
   }
   
   /**
    * Ricerca le informazioni di esecuzione del command
    * 
    * @param Abstract_Commands $command         Command
    * @param String            $commandName     Nome del comando da eseguire
    * 
    * @return Array
    */
   private function getCommandExecutionInfo(Abstract_Commands $command,$commandName)
   {
      $returnInfo = false;
      
      if($command->getName() == $commandName)
      {
         $returnInfo = Array(
                                 "name"   => $commandName,
                                 "method" => self::COMMANDS_DEFAULT_METHOD
         );
      }
      
      return $returnInfo;
   }
   
   /**
    * Inizializza lo stack dei comandi disponibili nella directory dell'applicazione
    * 
    * @return \Application_Commands
    */
   private function inizializeCommandsStackIterator()
   {
      $this->_COMMANDS_ITERATOR  = new ArrayIterator();
                        
      $commandPaths = $this->getApplicationKernel()->get('%APPLICATION_COMMANDS_PATHS');
      
      if(is_array($commandPaths) && count($commandPaths) > 0)
      {
         foreach($commandPaths as $path)
         {
            $this->registerCommandsInDirectory($path);
         }
      }
      
      return $this;
   }
   
   /**
    * Registra tutti i comandi presenti in una directory
    * 
    * @param String $directory path directory
    * 
    * @return Int numero di comandi registrati
    */
   private function registerCommandsInDirectory($directory)
   {
      $commandsFiles        = $this->getAllCommandsFiles($directory);
      $commandsRegistered   = 0;
      
      if(is_array($commandsFiles) && count($commandsFiles) > 0)
      {
         foreach($commandsFiles as $commandFile)
         {  
            $commandClassName = $this->getCommandClassNameByFileName($commandFile);

            if(!class_exists($commandClassName))
            {
               return self::throwNewException(8927345862369842, 'Non è possibile trovare il comando '.$commandClassName.' nel file '.$commandFile);
            }
            
            $command = $commandClassName::getInstance();
            
            $this->_COMMANDS_ITERATOR->append($command);
            $commandsRegistered++;
         }
      }
      
      return $commandsRegistered;
   }
   
   /**
    * Restituisce una lista di comandi presenti in una directory
    * 
    * @param $directory         Directory
    * @param $registerAutoload  [OPZIONALE] Registra autoload, default TRUE
    * 
    * @return Array
    */
   private function getAllCommandsFiles($directory,$registerAutoload = true)
   {
       
      if(!file_exists($directory)) 
      {
          return false;
      }
       
      $commandsFiles = glob($directory.'/*.'.self::COMMANDS_EXTENSION);
      
      if($registerAutoload)
      {
         $this->getApplicationAutoload()->addAutoloadPath('Commands', array(
                                    'path'       =>  $directory,
                                    'extension'  =>  'cmd.php'
         ));
      }
      
      if(is_array($commandsFiles) && count($commandsFiles) > 0)
      {
         return $commandsFiles;
      }
      
      return false;
   }
   
   /**
    * Restituisce il nome della classe del comando dal nome del file
    * 
    * @param String  $filePath Path del file
    * 
    * @return String
    */
   private function getCommandClassNameByFileName($filePath)
   {
      $class = $this->getApplicationAutoload()->getClassesInFile($filePath, 'Abstract_Commands',true);
      
      if(!$class)
      {
         return self::throwNewException(914707519037410947,'Attenzione! Questo percorso contiene un nome di un comando invalido: '.$filePath);
      }
      
      return $class;
   }
}


