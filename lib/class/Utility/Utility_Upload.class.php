<?php

/**
 * Classe per l'upload
 */
class Utility_Upload 
{ 
    
    use Trait_Exception,Trait_Singleton;
   
    /**
     * Max size di default 25 Megabyte
     */
    const DEFAULT_MAX_SIZE = 25000000;//25 mb
    
    /**
     * Cartella per l'upload su sistemi operativi Windows
     * @var Strign
     */
    private $winTmpDir  = "public";
    
    /**
     * Riferimento al $_FILES globale
     * @var Array
     */
    private $fileArr              = null;
    
    /**
     * Size massimo supportato per i file in upload
     * @var Int
     */
    private $maxSize              = null;
    
    
    /**
     * Nome chiave elemento di upload
     * @var String
     */
    private $fileArrName          = 'filetoupload';
    
    /**
     * Directory in cui effettuare l'upload dei file
     * @var String
     */
    private $directoryToUpload    = null;
    
    /**
     * Nr di file caricati
     * @var Int
     */
    private $totalFileUploaded    = 0;
    
    /**
     * Nr di file che hanno fallito l'upload
     * @var Int
     */
    private $totalFileNotUploaded = 0;
    
    /**
     * Indica se criptare il nome del file
     * @var Boolean
     */
    private $encrypt              = null;
    
    /**
     * Lista file caricati
     * @var Array
     */
    private $uploadedArr          = array();
    
    /**
     * Archivio zip per caricamento tramite zipFile
     * @var String
     */
    private $zipArchive           = null;
    
    /**
     * Estenszioni e mimeType abilitati al caricamento
     * @var Array
     */
    private $allowedExtensions   = null;

    
    /**
     * Imposta il formato accettato per il caricamento, se NULL accetta qualunque file
     * 
     * @param Array $extensionArray Array per le specifiche dei formati accettati es: Array('image/jpeg'=>'jpeg'), NULL per qualunque estenzione
     * 
     * @return Utility_Upload
     */
    public function setAllowedExtension(array $extensionArray = null)
    {
       $this->allowedExtensions = $extensionArray;
       return $this;
    }
    
    /**
     * Aggiunge un mimeType e un estenzione come formati supportati per l'upload
     * 
     * @param String $mimeType      Mime type del file caricato, es: text/html
     * @param String $extension     Estenzione fisica del file, es: html
     * 
     * @return \Utility_Upload
     */
    public function addAllowedExtension($mimeType, $extension)
    {
       $this->allowedExtensions[$mimeType] = $extension;
       return $this;
    }
    
    
    /**
     * Reimposta i mimetype e le estenzioni supportate con quelle di default 
     * 
     * @return \Utility_Upload
     */
    public function resetAllowExtension()
    {
       $uploadsAllowedExtensions = !defined("UPLOADS_ALLOWED_EXTENSIONS") ? array() : unserialize(UPLOADS_ALLOWED_EXTENSIONS);
       $this->setAllowedExtension($uploadsAllowedExtensions);
       return $this;
    }
    
    
    /**
     * Imposta l'array contenente i file da caricare, default $_FILES
     * @param Array $var
     * @return \Utility_Upload
     */
    public function setFileArr($var) 
    {
        $this->fileArr = $var;
        return $this;
    }

    /**
     * Imposta il size massimo per accettare il file
     * @param Int $var Kilobyte massimi accettati
     * @return \Utility_Upload
     */
    public function setMaxSize($var) 
    {
        $this->maxSize = $var;
        return $this;
    }
    
    /**
     * Imposta il nome del campo di upload dell'array $_FILES
     * @param String $var  Nome della chiave dell'array di $_FILES
     * @return \Utility_Upload
     */
    public function setFileArrName($var) 
    {
        $this->fileArrName = $var;
        return $this;
    }

    /**
     * Imposta la directory di upload dei file
     * @param String  $val  Path directory assoluto
     * @return \Utility_Upload
     */
    public function setDirectoryToUpload($val) 
    {
        if(!file_exists($val))
        {
           if(!mkdir($val,0755,true))
           {
              return $this->throwNewException(2357892789371625424672842492746,"Impossibile creare la directory per caricate i file in ".$val);
           }
        }
        
        $this->directoryToUpload = $val;
        return $this;
    }

    /**
     * Imposta l'encryption sui nomi dei file
     * 
     * @param Boolean $val 
     * 
     * @return Utility_Upload
     */
    public function setEncryption($val) 
    {
        $this->encrypt = $val;
        return $this;
    }

    
    /**
     * Restituisce il rifericmento all'array $_FILE caricato
     * @return Array
     */
    public function getFileArr()
    {
        return $this->fileArr;
    }
    
    /**
     * Restituisce il size massimo supportato per i file di upload
     * @return String
     */
    public function getMaxSize() 
    {
        return $this->maxSize;
    }

    /**
     * Restituisce il nome della chiave dell'array $_FILE per il caricamento
     * @return Array
     */
    public function getFileArrName() 
    {
        return $this->fileArrName;
    }

    /**
     * Restituisce la directory in cui caricari i file
     * @return String
     */
    public function getDirectoryToUpload() 
    {
        return $this->directoryToUpload;
    }

    /**
     * Restituisce il nr di file caricati
     * @return Int
     */
    public function getTotalFileUploaded() 
    {
        return $this->totalFileUploaded;
    }

    
    /**
     * Restitiuisce il nr di file che non sono stati caricati
     * @return Int
     */
    public function getTotalFileNotUploaded() 
    {
        return $this->totalFileNotUploaded;
    }

    /**
     * Restituisce la lista dei file caricati
     * 
     * @return array
     */
    public function getUploadeArr() 
    {
        return $this->uploadedArr;
    }
    
    
    /**
     * Restituisce la lista dei mimeType => fileExtension supportati per il caricamento
     * 
     * @return array
     */
    public function getAllowedExtension()
    {
       return $this->allowedExtensions;
    }

    
    /**
     * Classe per l'upload dei file sul server
     * 
     * @param Array  $fileArr            [OPZIONALE] Rifermento al $_FILES, default NULL
     * @param String $fileNameArr        [OPZIONALE] Nome del campo dell'array, default NULL
     * @param Int    $maxSize            [OPZIONALE] Dimensione massima dell'upload in Byte, default NULL
     * @param String $directoryUpload    [OPZIONALE] Directory in cui storare i file caricati, default NULL
     * 
     * @return Boolean
     */
    public function __construct($fileArr = null, $fileNameArr = null, $maxSize = null, $directoryUpload = null) 
    {
        if(!is_null($fileArr))
        {
            $this->setFileArr($fileArr);
        }
        
        if(!is_null($fileNameArr))
        {
            $this->setFileArrName($fileNameArr);
        }
        
        if($maxSize>0)
        {
            $this->setMaxSize($maxSize);
        }
        else
        {
            $this->setMaxSize(self::DEFAULT_MAX_SIZE);
        }
        
        if(!is_null($directoryUpload))
        {
             $this->setDirectoryToUpload($directoryUpload);
        }
        
        
        $this->setEncryption(false)->resetAllowExtension();
        
        return true;
    }

    /**
     * Esegue l'upload con i settings preconfigurati
     * 
     * @param Array $configs Configurazioni (Richiama i metodi set dell'oggetto in camelCase
     * 
     * @return Boolean
     */
    public function startUpload(array $configs = array()) 
    {
        
        if(count($configs) > 0)
        {
            foreach($configs as $field => $value)
            {
                $methodName = "set".ucfirst(strtocamelcase($field));
                
                if(method_exists($this, $methodName))
                {
                   $this->$methodName($value);
                }
            }
        }
        
        
        if ($this->isUploaded()) 
        {    
            if (is_array($this->fileArr[$this->fileArrName]['name'])) 
            {
                for ($i = 0, $k = 0; $i < count($this->fileArr[$this->fileArrName]['name']); $i++, $k++) 
                {
                    if (strlen($this->fileArr[$this->fileArrName]['tmp_name'][$i]) > 0) 
                    {
                        $file_temp      = $this->fileArr[$this->fileArrName]['tmp_name'][$i];
                        $uploadfolder   = $this->directoryToUpload;
                        $file_name      = $this->fileArr[$this->fileArrName]['name'][$i];
                        $file_ext       = is_array($this->allowedExtensions) ? $this->allowedExtensions[$this->fileArr[$this->fileArrName]['type'][$i]] : $this->_getFileExtension($file_name);
                        $file_base_name = basename($file_name,'.'.$file_ext);
                        
                        $file           = $file_name;
                        $original_name  = $file_base_name;

                        if ($this->encrypt) 
                        {
                            $crypt          = md5($file_name . uniqid());
                            $file_base_name = substr($crypt,0,rand(30,40));
                            $file           = $file_base_name . "." . $file_ext;
                        }
                        else
                        {
                            $file_name      = $original_name;
                        }
                        
                        $this->uploadedArr[$k]['name']          = $file_base_name;
                        $this->uploadedArr[$k]['original_name'] = $original_name;
                        $this->uploadedArr[$k]['ext']           = $file_ext;
                        $this->uploadedArr[$k]['type']          = $this->fileArr[$this->fileArrName]['type'][$i];
                        $this->uploadedArr[$k]['size']          = $this->fileArr[$this->fileArrName]['size'][$i];
                        $this->uploadedArr[$k]['path']          = $uploadfolder . "/" . $file;
                        
                        if (!$this->_isLinuxOS()) 
                        {
                            $tmpPublicFile = ROOT_PATH . "/" . $this->winTmpDir . "/" . $file;
                            $movePublicDir = move_uploaded_file($file_temp, $tmpPublicFile);
                            
                            if (!$movePublicDir)
                                return false;
                            
                            if (copy($tmpPublicFile, $uploadfolder . "/" . $file)) 
                            {
                                $this->uploadedArr[$k]['error']         = 0;
                                $this->totalFileUploaded++;
                            }
                            else 
                            {
                                
                                $this->uploadedArr[$k]['error']         = 1;
                                $this->totalFileNotUploaded++;
                            }
                        } 
                        else 
                        {   
                            if (move_uploaded_file($file_temp, $uploadfolder . "/" . $file)) 
                            {   
                                $this->uploadedArr[$k]['error']         = 0;
                                $this->totalFileUploaded++;
                            }
                            else 
                            {
                                $this->uploadedArr[$k]['error']         = 1;
                                $this->totalFileNotUploaded++;
                            }
                        }
                    }
                }
            }
            else //Singolo Input file
            { 
              
                if (strlen($this->fileArr[$this->fileArrName]['tmp_name']) > 0) 
                {
                    $file_temp      = $this->fileArr[$this->fileArrName]['tmp_name'];
                    $uploadfolder   = $this->directoryToUpload;
                    $file_name      = $this->fileArr[$this->fileArrName]['name'];
                    $file_ext       = is_array($this->allowedExtensions) ? $this->allowedExtensions[$this->fileArr[$this->fileArrName]['type']] : $this->_getFileExtension($file_name);
                    $file_base_name = basename($file_name,'.'.$file_ext);
                    $original_name  = $file_base_name;

                    if ($this->encrypt)
                    {
                        $crypt          = md5($file_name . uniqid());
                        $file_base_name = substr($crypt,0,rand(30,40));
                    }
                    
                    $file               = $file_base_name . "." . $file_ext;
                     
                    $this->uploadedArr[0]['name']           = $file_base_name;
                    $this->uploadedArr[0]['original_name']  = $original_name;
                    $this->uploadedArr[0]['ext']            = $file_ext;
                    $this->uploadedArr[0]['type']           = $this->fileArr[$this->fileArrName]['type'];
                    $this->uploadedArr[0]['size']           = $this->fileArr[$this->fileArrName]['size'];
                    $this->uploadedArr[0]['path']           = $uploadfolder . "/" . $file;
                    
                    if (!$this->_isLinuxOS()) 
                    {
                        $tmpPublicFile = ROOT_PATH . "/" . $this->winTmpDir . "/" . $file;
                        $movePublicDir = move_uploaded_file($file_temp, $tmpPublicFile);
                        
                        if (!$movePublicDir)
                        {
                           return false;
                        }
                        
                        if (copy($tmpPublicFile, $uploadfolder . "/" . $file)) 
                        {   
                            $this->uploadedArr[0]['error']          = 0;
                            $this->totalFileUploaded++;
                        }
                        else 
                        {
                            $this->uploadedArr[0]['error']          = 1;
                            $this->totalFileNotUploaded++;
                        }
                    } 
                    else 
                    {                             
                        if (move_uploaded_file($file_temp, $uploadfolder . "/" . $file)) 
                        {   
                            $this->uploadedArr[0]['error']          = 0;
                            $this->totalFileUploaded++;
                        }
                        else 
                        {                        
                            $this->uploadedArr[0]['error']          = 1;
                            $this->totalFileNotUploaded++;
                        }
                    }
                }
            }
        }

        if ($this->totalFileUploaded == 0)
        {
           return false;
        }

        if ($this->totalFileNotUploaded > 0)
        {
            return false;
        }

        return true;
    }

    /**
     * Carica un archivio ZIP nella directory specificata
     * 
     * @param String  $directory           Directory in cui salvare i file estrapolati dallo Zip
     * @param Boolean $delAfterUncompress  [OPZIONALE] Indica se eliminare lo zip dopo il caricamento, default TRUE
     */
    public function startUploadZip($directory, $deleteAfterUncompress = true) 
    {
        
    }
    
    /**
     * Controlla che il file sia caricabile
     * 
     * @return Boolean
     */
    private function isUploaded() 
    {
        if (count($this->fileArr) == 0)
        {
            return false;
        }
        
        if(empty($this->allowedExtensions))
        {
           return true;
        }
        
        if (is_array($this->fileArr[$this->fileArrName]['name'])) 
        {
            for ($i = 0; $i < count($this->fileArr[$this->fileArrName]['name']); $i++) 
            {
                $file_temp = $this->fileArr[$this->fileArrName]['name'][$i];
                if (isset($file_temp) && strlen($file_temp) > 0) 
                {
                   if ($this->fileArr[$this->fileArrName]['size'][$i] > $this->maxSize)
                   {
                        return false;
                   }
                   else if (is_array($this->allowedExtensions) && !array_key_exists(strtolower($this->fileArr[$this->fileArrName]['type'][$i]), $this->allowedExtensions))
                   {
                        return false;
                   }
                }
            }
        }
        else 
        {
            $file_temp = $this->fileArr[$this->fileArrName]['name'];
            if (isset($file_temp) && strlen($file_temp) > 0) 
            {
               if ($this->fileArr[$this->fileArrName]['size'] > $this->maxSize)
               {
                    return false;
               }
               else if (is_array($this->allowedExtensions) && !array_key_exists(strtolower($this->fileArr[$this->fileArrName]['type']),$this->allowedExtensions))
               {
                    return false;
               }
            }
        }

        return true;
    }

    /**
     * Determina se il sistema operativo ? Linux
     * @return type 
     */
    private function _isLinuxOS()
    {
        return is_int(strpos(php_uname(), "Linux"));
    }
    
    /**
     * Ricerca estenzione file, prima tramite pathinfo(), e in caso negativo sul nome del file
     * 
     * @param String $file Percorso o nome file
     * 
     * @return String Estenzione
     */
    private function _getFileExtension($file)
    {
       $file_ext = pathinfo($file,PATHINFO_EXTENSION);
       
       if(strlen($file_ext) == 0){
          $file_ext = substr($file, strrpos($file, '.') + 1);
       }
       
       return $file_ext;
    }
}
