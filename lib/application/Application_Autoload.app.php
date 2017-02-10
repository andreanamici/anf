<?php

/**
 * Questa classe si occupa di registrare un autoload per il caricamento delle classi/interfaccie/trait etc..
 */
class Application_Autoload
{     
   
    use Trait_ApplicationConfigs,
            
        Trait_ObjectUtilities,
            
        Trait_Singleton;
   
    /**
     * Metodo di default utilizzato da questa classe per il caricamento delle classi
     * 
     * @var String
     */
    const AUTOLOAD_METHOD_NAME           = 'autoloadClass';

    /**
     * Nome del metodo statico del singleton da invocare sull'oggetto
     * 
     * @var String
     */
    const SINGLETON_METHOD_NAME          = 'getInstance';
    
    /**
     * Array che contiene la mappatura su come caricare le classi ricercate nell'applicazione
     * 
     * @var Array
     */
    protected $_AUTOLOAD_PATHS_MAPS             = null;
    
    /**
     * Array che contiene i nomi delle directory nel quale ricercare le classi che sfruttano i namespace dell'applicazione, a partire sempre da ROOT_PATH
     * 
     * @var Array
     */
    protected $_AUTOLOAD_NAMESPACS_SRC          = array();
        
    /**
     * Path assoluto in cui cercare le classi di default del namespace "/"
     * 
     * @var String
     */
    protected $_classDirectory  = null;
       
    /**
     * Estenzione file classi di default
     * 
     * @var String
     */
    protected $_classExtension  = null;
    
    
    /**
     * Questa classe si occupa di registrare un autoload per il caricamento delle classi senza eseguire i require
     * 
     * @return Application_Autoload
     */
    public function __construct() 
    {        
    }
    
    
    public function initMe()
    {
        $this->getApplicationConfigs()->loadConfigsFile('application-autoload');

        $this->setClassDirectory(APPLICATION_AUTOLOAD_CLASS_DIRECTORY)
             ->setClassExtension(APPLICATION_AUTOLOAD_CLASS_DEFAULT_EXTENSION)
             ->loadDefaultAutoloadMap()
             ->loadDefaultNamespacesPath();
        
        return $this;
    }
    
    /**
     * Imposta il path di default in cui cercare le classi del namespace "/"
     * 
     * @param String $classDirectory Path
     * 
     * @return \Application_Autoload
     */
    public function setClassDirectory($classDirectory)
    {
        $this->_classDirectory = $classDirectory;
        return $this;
    }
    
    /**
     * Restituisce il path di default in cui cercare le classi del namespace "/"
     * 
     * @return String
     */
    public function getClassDirectory()
    {
        return $this->_classDirectory;
    }
    
    /**
     * Imposta l'estenzione dei file delle classi
     * 
     * @param String $classExtension Path
     * 
     * @return \Application_Autoload
     */
    public function setClassExtension($classExtension)
    {
        $this->_classExtension = $classExtension;
        return $this;
    }
    
    /**
     * Restituisce l'estenzione dei file delle classi
     * 
     * @return String
     */
    public function getClassExtension()
    {
        return $this->_classExtension;
    }
    
    
    /**
     * Aggiunge all'autoload la mappatura con la quali verranno caricate le classi con il prefix specificato
     * 
     * @param String $classPrefix Prefisso classi caricate , es: 'Myclass'
     * 
     * @param array  $pathInfo    Array informazioni contenente:
     * 
     *                            <ul>
     *                                <li>'path'        => percorso relativo/assoluto</li>
     *                                <li>'extension'   => estenzione file , es: .myext.php</li>
     *                                <li>'prefix'      =>(bool | string) indica se aggiungere il prefisso della classe</li>
     *                            </ul>
     * 
     * @return Application_Autoload
     */
    public function addAutoloadPath($classPrefix,array $classInfo,$prepend = true)
    {
       
       if(!isset($classInfo["path"]))
       {
          return self::throwNewException(3094538748834,'Impossibile registrare questa classInfo: '.print_r($classInfo,true).', parametro <b>path</b> non trovato!');
       }
       
       if(!isset($classInfo['extension']))
       {
           $classInfo['extension'] = $this->_classExtension;
       }
       
       if(empty($classInfo["extension"]))
       {
          return self::throwNewException(46357352345746,'Impossibile registrare questa classInfo: '.print_r($classInfo,true).', parametro <b>extension</b> non deve essere vuoto!');
       }
       
       if(is_string($classInfo["extension"]))
       {
           $classInfo["extension"] = array($classInfo["extension"]);
       }
       
       
       if(!isset($classInfo['prefix']))
       {
           $classInfo['prefix'] = true;
       }
       
       $pathExists = false;
       
       if(!isset($this->_AUTOLOAD_PATHS_MAPS[$classPrefix]))
       {
           $this->_AUTOLOAD_PATHS_MAPS[$classPrefix] = Array();
       }
       
       foreach($this->_AUTOLOAD_PATHS_MAPS[$classPrefix]  as  $key => $pathData)
       {   
          if(!$pathExists)
          {
             if($pathData['path'] == $classInfo['path'] && count(array_diff($pathData['extension'],$classInfo['extension'])) == 0 && $classInfo['prefix'] == $pathData['prefix'])
             {
                $pathExists = true;
             }
          }
       }
       
       if(!$pathExists)
       {
          if($prepend)
          {
              $currentMap = $this->_AUTOLOAD_PATHS_MAPS[$classPrefix];
              $newMap     = array_merge(array($classInfo),$currentMap);
              $this->_AUTOLOAD_PATHS_MAPS[$classPrefix] = $newMap;
          }
          else
          {
              $this->_AUTOLOAD_PATHS_MAPS[$classPrefix][] = $classInfo;
          }
          
       }
                     
       return $this;
    }
    
    /**
     * Restituisce la lista dei path aggiunti all'utoload
     * 
     * @return type
     */
    public  function getAutoloadPathMaps()
    {
       return $this->_AUTOLOAD_PATHS_MAPS;
    }
    
    /**
     * Aggiunge una directory in cui cercare i namespaces a partire sempre da ROOT_PATH
     * 
     * @param String $dirName DirectoryName
     * 
     * @return \Application_Autoload
     */
    public function addNamespaceSrcDirectoryName($dirName)
    {
        $this->_AUTOLOAD_NAMESPACS_SRC[] = $dirName;
        return $this;
    }
    
    /**
     * Restituisce i nomi delle directory in cui verranno ricercati le classi che sfruttano i namespace
     * 
     * @return Array
     */
    public function getNamespaceSrcDirectorory()
    {
        return $this->_AUTOLOAD_NAMESPACS_SRC;
    }
    
    
    /**
     * Restituisce le informazioni per la mappatura relativo al namespace / class prefix specificato
     * 
     * @param String $nameSpaceOrClassPrefix  namespace / class prefix 
     * 
     * @return Array  or FALSE se non presente
     */
    public function getMapInfo($nameSpaceOrClassPrefix)
    {
       if(is_array($this->_AUTOLOAD_PATHS_MAPS) && count($this->_AUTOLOAD_PATHS_MAPS) > 0 )
       {
          if(!isset($this->_AUTOLOAD_PATHS_MAPS[$nameSpaceOrClassPrefix]) || (isset($autoloadMapArray[$nameSpaceOrClassPrefix]) && count($autoloadMapArray[$nameSpaceOrClassPrefix]) == 0))
          {
             return false;
          }
          
          return $this->_AUTOLOAD_PATHS_MAPS[$nameSpaceOrClassPrefix];
       }
       
       return false;
    }
    
    
    /**
     * Rimuove Autoload function 
     * 
     * @return Boolean
     */
    public function unregister()
    {
       
        $deregister  = spl_autoload_unregister(array($this,self::AUTOLOAD_METHOD_NAME));
        
        if(!$deregister){
           return self::throwNewException(891274891624162498,"Impossibile rimuovere il metodo dell'oggetto ".__CLASS__."::".self::AUTOLOAD_METHOD_NAME."() dallo stack delle autoload");
        }
        
        return true;
    }
    
    /**
     * Registra Autoload Function
     * 
     * @return Boolean
     */
    public function register()
    {
        $register = spl_autoload_register(array($this,self::AUTOLOAD_METHOD_NAME));
        
        if(!$register){
           return self::throwNewException(3479876235872658923457,"Impossibile caricare il metodo dell'oggetto ".__CLASS__."::".self::AUTOLOAD_METHOD_NAME."() come autoload");
        }
        
        return true;
    }
    
    /**
     * Autoload Function
     * 
     * @param String $className Class Name, es: DAO_DBManager
     * 
     * @return boolean
     * 
     * @throws Exception
     */
    public function autoloadClass($className)
    {
       $originalClassName  = $className;
       
       $classFilePath      = null;
       $fileFounded        = false;
       
       /**
        * Autoload Namespaces
        */
       if(preg_match("/[\\\]+/",$className))    //Namespace
       {
            $className = ltrim($className, '\\');
            $fileName  = '';
            $namespace = '';
            
            if ($lastNsPos = strrpos($className, '\\')) 
            {
                $namespace = substr($className, 0, $lastNsPos);
                $className = substr($className, $lastNsPos + 1);
                $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
            }
            
            $fileName.= $className;
                                          
            /**
             * Ricerco il file nei namespaces di ricerca forniti
             */
            foreach($this->_AUTOLOAD_NAMESPACS_SRC as $namespaceSrc)
            {
                if(!$fileFounded)
                {
                    $classFilePath = ROOT_PATH . DIRECTORY_SEPARATOR . $namespaceSrc . DIRECTORY_SEPARATOR .  $fileName .'.php';
                    
                    if(file_exists($classFilePath))
                    {
                        $className     = $originalClassName;
                        $fileFounded   = true;
                    }
                    else if(count($this->_AUTOLOAD_PATHS_MAPS) > 0)
                    {
                        foreach($this->_AUTOLOAD_PATHS_MAPS as $classPrefix => $classPathsArray)
                        {
                           if(strlen($classPrefix) > 0 && strstr($fileName,$classPrefix) !== false)
                           {
                              foreach($classPathsArray as $classPath)
                              {
                                 if(!$fileFounded)
                                 {
                                    $extension = is_array($classPath["extension"]) > 0 ? $classPath["extension"] : array('php');
                                    
                                    foreach($extension as $fileExtension)
                                    {
                                        if(!$fileFounded)
                                        {
                                            $classFilePath = $classPath["path"]. DIRECTORY_SEPARATOR . $className . '.' . $fileExtension;

                                            if(strstr($classFilePath,$fileName) !== false)
                                            {
                                               if(file_exists($classFilePath))
                                               {
                                                  $className    = $originalClassName;
                                                  $fileFounded  = true;
                                               }
                                            }
                                        }
                                    }
                                 }
                              }
                           }
                        }
                    }
                }
            }
       }
       /**
        * Autoload Classes nel namespace "/" con il prefix "_"
        */
       else if(preg_match("/_/",$className))
       {           
          $tmpArr             = explode("_",$className);
          
          $currentClassPrefix = $tmpArr[0];
          $currentClassName   = $tmpArr[1];
        
          if(count($this->_AUTOLOAD_PATHS_MAPS) > 0)
          {
               foreach($this->_AUTOLOAD_PATHS_MAPS as $classPrefix => $classPathsArray)
               {
                  if($classPrefix == $currentClassPrefix)
                  {                     
                     foreach($classPathsArray as $pathInfo)
                     {
                        if(!$fileFounded)
                        {
                           $classRealPrefix = is_string($pathInfo["prefix"]) && strlen($pathInfo["prefix"])>0 ? $pathInfo["prefix"]."_" : "";

                           if($pathInfo["prefix"]!==false && $classRealPrefix == '')
                           {
                              $classRealPrefix = $currentClassPrefix."_";
                           }
                           
                           if(is_array($pathInfo["extension"]) && count($pathInfo) > 0)
                           {
                                foreach($pathInfo["extension"] as $fileExtension)
                                {
                                   if(!$fileFounded)
                                   {
                                      $classFilePath = $pathInfo["path"] . DIRECTORY_SEPARATOR . $classRealPrefix  . $currentClassName . '.' . $fileExtension;

                                      if(file_exists($classFilePath))
                                      {
                                         $fileFounded = true;
                                      }
                                   }
                                }  
                           }
                        }
                     }
                  }
               }
          }
       }
       /**
        * Caricamento classi senza prefisso nel namespace "/"
        */
       else
       {          
          if(count($this->_AUTOLOAD_PATHS_MAPS) > 0 && isset($this->_AUTOLOAD_PATHS_MAPS['']))
          {
             $fileFounded = false;
             
             foreach($this->_AUTOLOAD_PATHS_MAPS[''] as $classPathsArray)
             {
                if(!$fileFounded)
                {
                    $extension = $classPathsArray['extension'];
                    
                    foreach($extension as $fileExtension)
                    {
                        if(!$fileFounded)
                        {
                            $classFilePath = $classPathsArray['path'] . DIRECTORY_SEPARATOR .  $className . '.'  .$fileExtension;

                            if(file_exists($classFilePath))
                            {
                               $fileFounded = true;
                            }
                        }
                    }
                }
             }
          }
          else
          {
             $fileFounded   = false;          
             $classFilePath = $this->_classDirectory . DIRECTORY_SEPARATOR .  $className . '.'  .$this->_classExtension;
             
             if(file_exists($classFilePath))
             {
                $fileFounded = true;
             }
          }
          
       }

       if($fileFounded)
       {
           require_once($classFilePath);
         
           $classNotFound       = false;
           $interfaceNotFound   = false;
           $traitNotFound       = false;
         
          if(!class_exists($className, false))
          {
              $classNotFound = true;
          }
         
          if(!interface_exists($className,false))
          {
             $interfaceNotFound = true;
          }
         
          if(!trait_exists($className, false))
          {
             $traitNotFound = true;
          }
          
          if($classNotFound && $interfaceNotFound && $traitNotFound)
          {
             return self::throwNewException(4583458,"Non è possibile caricare l'oggetto '".$className."' poichè è stato trovato solamente il file in '".$classFilePath."', ma all'interno non vi è ne una classe, ne un intefaccia, ne un trait");
          }
         
          return true;
       }
       
       return false;
    }
    
    /**
     * Restituisce l'instanza di una classe, specificando i parametri del costruttore e se eventualmente utilizzare il caricamento tramite singleton
     * 
     * @param String  $className          Nome della classe
     * @param array   $constructParams    [OPZIONALE] Parametri formato array, default Array()
     * @param Boolean $useSingleton       [OPZIONALE] Indica se utilizzare Singleton, default TRUE
     * 
     * @return Mixed  Object Instance
     */
    public function getLoadClassInstance($className,array $constructParams = array(),$useSingleton = true,$singletonMethod = self::SINGLETON_METHOD_NAME)
    {
        if($this->autoloadClass($className))
        {  
           $instance = false;
           
           if($useSingleton)
           {
              if(method_exists($className, $singletonMethod))
              {
                 $instance = call_user_func_array(array($className,$singletonMethod), $constructParams);
                 return $instance;
              }
           }
           
           $reflectionClass = new ReflectionClass($className);
           $instance = $reflectionClass->newInstanceArgs($constructParams);
           
           if(!is_object($instance))
           {
              return self::throwNewException(39086420957304914, 'Questa classe richiesta '.$className.' non è stata caricata correttamente.');
           }
           
           return $instance;
        }

        
        return self::throwNewException(289334406340820, 'Questa classe non esiste: '.$className);
    }
    
    
    /**
     * Carica le directory in cui ricercare i namespaces di default dell'applicazione
     * 
     * @return \Application_Autoload
     */
    private function loadDefaultNamespacesPath()
    {
        if(defined("APPLICATION_AUTOLOAD_NAMESPACE_SRC"))
        {
            $this->_AUTOLOAD_NAMESPACS_SRC = unserialize(APPLICATION_AUTOLOAD_NAMESPACE_SRC);
        }
        
        return $this;
    }
    
    /**
     * Ricerca la mappatura di default dell'autoload e se la trova la carica
     * 
     * @return \Application_Autoload
     */
    private function loadDefaultAutoloadMap()
    {
       if(!defined("APPLICATION_AUTOLOAD_MAP"))
       {
          return self::throwNewException(2348234829340384, 'La configurazione APPLICATION_AUTOLOAD_MAP non è stata trovata');
       }
       
       $customAutoloadMaps  = unserialize(APPLICATION_AUTOLOAD_MAP);

       if(is_array($customAutoloadMaps) && count($customAutoloadMaps) > 0)
       {
           foreach($customAutoloadMaps as $classNameSpace => $paths)
           {
              $this->_AUTOLOAD_PATHS_MAPS[$classNameSpace] = $paths;
           }
       }
       
       foreach($this->_AUTOLOAD_PATHS_MAPS as $classPrefix => $paths)
       {
           foreach($paths as $key => $path)
           {
               if(!isset($path['prefix']))
               {
                  $path['prefix'] = true;
               }
               
               if(!isset($path['extension']))
               {
                  $path['extension'] = $this->_classExtension;
               }
               else if(is_string($path['extension']))
               {
                  $path['extension'] = array($path['extension']);
               }
              
               $paths[$key] = $path;
           }
           
           $this->_AUTOLOAD_PATHS_MAPS[$classPrefix] = $paths;
       }
       
       return $this;
    }
    
    /**
     * Restituisce le classe su un file senza effettuare il require/include
     * 
     * @param String  $filepath       Path assoluto file
     * @param String  $subclassName   [OPZIONALE] Nome subclass per filtrare le classi nel file, se indicata verrà anche incluso il file
     * @param Boolean $first          [OPZIONALE] Indica se restituire la prima classe trovata
     * 
     * @return array|string
     */
    public static function getClassesInFile($filepath,$subclassName  = null,$first = false) 
    {
        $php_code = file_get_contents($filepath);
        $classes  = self::_getPHPClassesInCode($php_code);
        
        $classesFile = array();

        if(is_array($classes) && count($classes) > 0)
        {            
            foreach($classes as $namespace => $classesList)
            {
                if(is_numeric($namespace))    
                {
                    $namespace = '\\';
                }
                else
                {
                    $namespace  = '\\'.$namespace.'\\';
                }
                
                foreach($classesList as $class)
                {
                    $classFullName = $namespace.$class;
                    $isValid       = true;
                    
                    if(!is_null($subclassName))
                    {
                        require_once $filepath;
                        $reflector = new ReflectionClass($classFullName);
                        $isValid   = $reflector->isSubclassOf($subclassName);
                    }
                    
                    if($isValid)
                    {
                        $classesFile[$namespace][] = $classFullName;
                    }
                }
            }
        }
         
        if(!$first)
        {
            return $classesFile;
        }
        
        $classesFile  = reset($classesFile);
        if($classesFile)
            $classesFile  = reset($classesFile);
          
        return $classesFile;
    }

    private static function _getPHPClassesInCode($phpcode) 
    {
        $classes = array();
        $namespace = 0;
        $tokens = token_get_all($phpcode);
        $count = count($tokens);
        $dlm = false;
        for ($i = 2; $i < $count; $i++)
        {
            if ((isset($tokens[$i - 2][1]) && ($tokens[$i - 2][1] == "phpnamespace" || $tokens[$i - 2][1] == "namespace")) ||
                    ($dlm && $tokens[$i - 1][0] == T_NS_SEPARATOR && $tokens[$i][0] == T_STRING))
            {
                if (!$dlm)
                    $namespace = 0;
                if (isset($tokens[$i][1]))
                {
                    $namespace = $namespace ? $namespace . "\\" . $tokens[$i][1] : $tokens[$i][1];
                    $dlm = true;
                }
            }
            elseif ($dlm && ($tokens[$i][0] != T_NS_SEPARATOR) && ($tokens[$i][0] != T_STRING))
            {
                $dlm = false;
            }
            if (($tokens[$i - 2][0] == T_CLASS || (isset($tokens[$i - 2][1]) && $tokens[$i - 2][1] == "phpclass")) && $tokens[$i - 1][0] == T_WHITESPACE && $tokens[$i][0] == T_STRING)
            {
                $class_name = $tokens[$i][1];
                if (!isset($classes[$namespace]))
                    $classes[$namespace] = array();
                $classes[$namespace][] = $class_name;
            }
        }
        
        return $classes;
    }    
}