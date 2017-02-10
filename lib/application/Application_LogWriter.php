<?php

/**
 * Classe per la scrittura dei file di Log dell'applicazione
 * 
 * @method Application_LogWriter getInstance Restituisce l'instanza del file log write manager
 * 
 */
class Application_LogWriter
{
    use Trait_Singleton,Trait_ObjectUtilities;
    
    /**
     * Scrive su file esistente spostando il cursore all'inzio del file
     */
    const FILE_WRITE            = "w";
    
    /**
     * Scrove su file, verificando l'esistenza ed eventualmente creando il file non presente
     */
    const FILE_WRITE_AND_CREATE = "w+";
    
    /**
     * Scrive su file appendendo il contenuto alla fine del file
     */
    const FILE_APPEND           = "a";
    
    /**
     * Scrive su file appendendo il contenuto alla fine del file,verificandone l'esistenza ed eventualmente craendo il file
     */
    const FILE_APPEND_CREATE    = "a+";
    
    
    /**
     * Default file di log
     * @var String
     */
    const    DEFAULT_LOG_TYPE    = 'exception';
    
    /**
     * Indica se sono abilitati i logs
     * 
     * @var Boolean
     */
    private $_logs_enable         = false;

    
    /**
     * Directory in cui salvare i file log
     * @var String
     */
    protected  $_log_dir           = null;
    
    /**
     * Nome file di log attualmente gestito dalla classe
     * @var String
     */
    protected  $_log_file_name     = null;
    
    
    /**
     * Path assoluto file di log gestito
     * @var String
     */
    protected $_log_file           = null;
    
    
    /**
     * Lista files di log disponibili
     * 
     * @var Array
     */
    protected static  $_LOGS_FILES  = null;
    
    /**
     * Dimensione massima dei file di log, in byte
     * 
     * @var int
     */
    protected $_file_max_size = 5000000;
    
    /**
     * Classe finale per la scrittura dei file di Log del portale
     * 
     * @param String $type Tipologia di log creato, default self::DEFAULT_LOG_TYPE
     */
    public function  __construct($type = self::DEFAULT_LOG_TYPE) 
    {

       $this->_file_max_size = defined("LOGS_MAX_FILE_SIZE") ? LOGS_MAX_FILE_SIZE : $this->_file_max_size;
       $this->_logs_enable   = defined("LOGS_ENABLE")        ? LOGS_ENABLE        : $this->_logs_enable;
       
       $this->initLogsFileType()
            ->setLogsDirectory(defined("LOGS_DIRECTORY") ? LOGS_DIRECTORY : null)
            ->setType($type);
             
       return $this;
    }
    
    
    /**
     * Restituisce tutte le tipologie di file di log gestiti
     * 
     * @return Array
     */
    public function getAllLogsType()
    {
       return static::$_LOGS_FILES;
    }
    
    /**
     * Aggiunge una tipologia alla lista delle tipologie di file di log gestiti, verificandone l'esistenza
     * 
     * @param String $type Tipologia
     * 
     * @return Boolean  TRUE se aggiunta, FALSE altrimenti
     */
    public function addLogsType($type)
    {
       if(!in_array($type, static::$_LOGS_FILES))
       {
          static::$_LOGS_FILES[] = $type;
          return true;
       }
       
       return false;
    }

    /**
     * Rimuove una tipologia alla lista delle tipologie di file di log gestiti
     * 
     * @param String $type Tipologia
     * 
     * @return Boolean  TRUE se rimossa, FALSE altrimenti
     */
    public function removeLogsType($type)
    {
       if(in_array($type, static::$_LOGS_FILES))
       {
          $newLogsType = array();
          
          foreach(static::$_LOGS_FILES as $key => $value)
          {
             if($value != $type)
             {
                $newLogsType[] = $value;
             }            
          }
          
          static::$_LOGS_FILES = $newLogsType;
          
          return true;
       }
       
       return false;
    }
    
    
    /**
     * Restituisce la directory dei file di log
     * 
     * @return String
     */
    public function getLogsDirectory()
    {
       return $this->_log_dir;
    }
    
    /**
     * Imposta la directory per i file di log
     * 
     * @param String $logDir Directory, path assoluto
     * 
     * @return \Application_LogWriter
     */
    public function setLogsDirectory($logDir)
    {
       $this->_log_dir = $logDir;
       return $this;
    }
    
    
    /**
     * Imposta la tipologia di file di log
     * 
     * @param String $type Tipologia di log creato, default 'exception'
     * 
     * @return Application_LogWriter instance
     */
    public function setType($type)
    {
       $this->_log_file_name = $type;
       $this->_log_file      = $this->getLogFilePath($type);
       
       return $this;
    }
    
    /**
     * Scrive sul file di log gestito attualmente
     * <br>
     * <b>Questo metodo verifica che i logs siano abilitati</b>
     * <b>Elimina il log qualora superi la dimensione massima indicata</b>
     * 
     * @param String $message Stringa da scrivere
     * @param String $mode    [OPZIONALE] Indica il mode con il quale aprire il file, default "a+"
     * 
     * @return Boolean
     */
    public function write($message,$mode = self::FILE_APPEND_CREATE)
    {
       
       if($this->_logs_enable)
       {
          $pf = fopen($this->_log_file,$mode);
          
          if(filesize($this->_log_file) >= ($this->_file_max_size))
          {
             if($this->clearLog($this->_log_file_name))
             {
                $pf = fopen($this->_log_file,$mode);
             }
             else
             {
                return self::throwNewException(92349293492394234,'Cannot delete log file: '.$this->_log_file);
             }
          }
          
          if(fputs($pf,$message)!==false)
          {
             return fclose($pf);
          }
          else
          {
             return self::throwNewException(28837443438921,'Cannot write log file: '.$this->_log_file);
          }
       }
       
       return false;
    }
    
    /**
     * Scrive sul file di log gestito attualmente una nuova riga
     * 
     * @param String $message Stringa da scrivere
     * @param String $mode    [OPZIONALE] Indica il mode con il quale aprire il file, default "a+"
     * 
     * @return Boolean
     */
    public function writeln($message,$mode = self::FILE_APPEND_CREATE)
    {
        return $this->write("\n{$message}",$mode);
    }
    
    /**
     * Legge il contenuto del file di log attualmente gestito
     * 
     * @param Boolean $reverse      [OPZIONALE] Indica se restituirlo al contrario, default FALSE
     * @param Int     $linesNumber  [OPZIONALE] Numero di linee, default 1000
     * 
     * @return String
     */
    public function read($reverse = false,$linesNumber = 1000)
    {  
       if(!file_exists($this->_log_file))
       {
           return null;
       }
       
       if(!is_readable($this->_log_file))
       {
          return self::throwNewException(102940952934314234, 'Cannot read file: '.$this->_log_file);
       }
       
       $content     = "";
       $textLines   = array();

       if($reverse  || ($linesNumber != 'all' && $linesNumber > 0))
       {
            $handle      = fopen($this->_log_file, "r");
            $linecounter = $linesNumber;
            $pos         = -2;
            $beginning   = false;

            while ($linecounter > 0) 
            {
                $t = " ";
                while ($t != "\n") 
                {
                    if(fseek($handle, $pos, SEEK_END) == -1) 
                    {
                        $beginning = true; 
                        break; 
                    }
                    $t = fgetc($handle);
                    $pos --;
                }
                $linecounter --;
                if ($beginning) 
                {
                   rewind($handle);
                }

                $textLines[$linesNumber-$linecounter-1] = fgets($handle);
                if ($beginning) break;
            }
            
            $textLines = array_reverse($textLines);
            fclose ($handle);
       }
       else 
       {
          $content = file_get_contents($this->_log_file);
       }
       
       if(is_array($textLines) && count($textLines) > 0)
       {
            if($reverse)
            {
               $textLines = array_reverse($textLines);
            }

            foreach ($textLines as $line)
            {
               $content.= $line;
            }
       }
       
       return $content;
    }
    
    /**
     * Pulisce tutti i file di log  gestiti da questa classe
     * 
     * @return Boolean
     */
    public function clearAll()
    {
       $allLogs     = static::$_LOGS_FILES;
       
       foreach($allLogs as $logfile)
       {          
          $this->clearLog($logfile);
       }
       
       return true;
    }
    
    
    /**
     * Elimina e rigenera un file di log nuovo
     * 
     * @param String $type [OPZIONALE] Tipologia di file log, default quello in uso dalla classe
     * 
     * @return Boolean   TRUE se il file è stato eliminato
     */
    public function clearLog($type = null)
    {  
       $type      = is_null($type) ? $this->_log_file_name : $type;
       $filePath  = $this->getLogFilePath($type);
       
       if(file_exists($filePath))
       {
          return unlink($filePath);
       }
       
       return false;
    }
    
    
    /**
     * Restituisce il path assoluto del file specificando la tipologia di log
     * 
     * @param String $type  Tipologia di file log
     * 
     * @return String   Path assoluto
     */
    public function getLogFilePath($type)
    {
         if(in_array($type,static::$_LOGS_FILES))
         {
            return  $this->getLogsDirectory() . '/' . $type. '.log';
         }

         return  $this->getLogsDirectory() . '/generic.log';
    }
    
    /**
     * Inizializza le tipologie di file gestite
     * 
     * @return Application_LogWriter
     */
    private function initLogsFileType()
    {
       static::$_LOGS_FILES = defined("LOGS_FILE_TYPES") ? unserialize(LOGS_FILE_TYPES) : array();

       return $this;
    }
}
