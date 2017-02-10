<?php

/**
 * Cache File Manager, questo sistema si occupa di storare le informazioni su file
 */
class Cache_File extends Cache_Abstract
{   
   
   use Trait_Exception;
   
   /**
    * Default Cache timetolive
    */
   const CACHE_DEFAULT_TTL              = CACHE_DEFAULT_TTL;
   
   /**
    * Path in cui storare le chiavi di caching
    * @var String
    */
   public static $_cache_directory       = CACHE_FILE_DIRECTORY;
   
   /**
    * Nome del File cache gargage Info
    * @var String 
    */
   public static $_cache_file_cgm        = CACHE_FILE_GARBAGE_FILENAME;   //
   
   /**
    * Ogni quanto deve essere avviato il garbage
    * @var Int
    */
   public static $_cache_garbage_timeout = CACHE_FILE_GARBAGE_TIMEOUT;
   
   
   public function __construct()
   {
      
      if(defined("CACHE_KEYPREFIX") && strlen(CACHE_KEYPREFIX) > 0)
      {
         self::$_cache_directory = self::$_cache_directory.'/'.CACHE_KEYPREFIX;
      }
      
      return $this->_initCgm();
   }
  
   public function __destruct() 
   {
      return $this->_cleanExpiredKeys();   
   }
   
   public function check()
   {
      if(!is_writable(self::$_cache_directory))
      {
         return self::throwNewException(77466558884663, 'Questo caching engine "'.__CLASS__.'" scrive nella cartella  '.self::$_cache_directory.' che risulta non scribile!');
      }
      
      return true;
   }
   
   public function fetch($key) 
   {
      $subfolder  = $this->_getKeySubfolderPath($key);
      $cachefiles = glob($subfolder."/{$key}*",GLOB_NOSORT);
      
      if(is_array($cachefiles) && count($cachefiles)>0)
      {
           foreach($cachefiles as $file)
           {              
              $res = Array();
              $fileinfo = preg_match("/({$key})\.([0-9]+)\.cache/",$file,$res);  
              
              if(isset($res[1]) && isset($res[2]))
              {
                  $keyname  = $res[1];
                  $keyttl   = $res[2];

                  if($keyttl>=time())
                  {
                     $keycontent = file_get_contents($file);
                     $value = unserialize($keycontent);
                     return $value;
                  }
                  else
                  {
                     $this->delete($keyname);
                  }
              }
           }
      }
      
      return false;
   }
   
   
   public function store($key, $value, $ttl = self::CACHE_DEFAULT_TTL)
   {
      $keydirectory = $this->_getKeySubfolderPath($key);
      if($keydirectory!==false)
      {
         
         if($this->fetch($key)!==false){
            $this->delete($key);
         }
          
         
         $ttl          = $ttl == 0 ? 946080000 : $ttl;         //se il TTL è = 0 verrà impostato di default la data di  (3600*24)*365*30  = 30 anni in sec.. quasi immortale :D
         
         $filecache    = $keydirectory."/".$key.".".(time()+$ttl).".cache";
         $data         = serialize($value);
         
         if(!file_exists($keydirectory)){
            mkdir($keydirectory,0777,TRUE);   
         }
         
         file_put_contents($filecache,$data);
         
         return true;
      }
      
      return false;
   }
   
   
   public function delete($key)
   {
       $subfolder  = $this->_getKeySubfolderPath($key);
       $cachefiles = glob($subfolder."/{$key}*",GLOB_NOSORT);
       
       if(is_array($cachefiles) && count($cachefiles)>0)
       {
           foreach($cachefiles as $file){
              @unlink($file);
           }
           
           return true;
       }
       
       return false;
   }
   
   public function deleteByPrefix($prefix) 
   {
      $cacheDirectory = CACHE_FILE_DIRECTORY;
      
      if(file_exists($cacheDirectory.'/'.$prefix))
      {
         $cacheDirectoryByPrefix = $cacheDirectory.'/'.$prefix;
         return $this->_rrmdir($cacheDirectoryByPrefix);
      }
      
      return false;
   }
   
   /**
    * Pulisce completamente tutta la cartella di caching in cui vengono storate le chiavi
    * 
    * @return Boolean
    */
   public function flush()
   {
      return $this->_rrmdir(self::$_cache_directory);
   }
   
   private function _initCgm($info = Array(),$force = false)
   {
      $cgmfile = self::$_cache_directory."/".self::$_cache_file_cgm;
      
      if(!file_exists(self::$_cache_directory)){
         mkdir(self::$_cache_directory,0777,true);
      }
      
      if(!file_exists($cgmfile) || $force)
      {
         $cgminfo = Array("garbagetime" => (time()+self::$_cache_garbage_timeout),"nrlastexpired"=>(isset($info["nrlastexpired"]) ? $info["nrlastexpired"] : 0));
         $cgmdata = serialize($cgminfo);
         return file_put_contents($cgmfile, $cgmdata);
      }
   }
   
   private function _getCgm()
   {
      $cgmfile = self::$_cache_directory."/".self::$_cache_file_cgm;
      if(!file_exists($cgmfile))
      {
         $this->_initCgm();
         return $this->_getCgm();
      }
      
      $cgminfo = unserialize(file_get_contents($cgmfile));
      return $cgminfo;
   }
   
   
   private function _cleanExpiredKeys()
   {
      $cgminfo = $this->_getCgm();
      $filexpired = 0;
      
      if($cgminfo["garbagetime"]<=time())
      {
         $filexpired = 0;
         $directory  = self::$_cache_directory;
         $files      = $this->_getAllCachedKey($directory);
         
         if(is_array($files) && count($files)>0)
         {
            foreach($files as $file)
            {
               $res = Array();
               $filettl = preg_match("/(.*?)\.([0-9]+)\.cache/", $file,$res);
               if(is_array($res) && isset($res[2]) && is_numeric($res[2]))
               {
                  if(time()>$res[2])
                  {
                     if(@unlink($file)){
                        $filexpired++;
                     }
                  }   
               }
            } 
            
            $this->_initCgm(Array("nrlastexpired"=>$filexpired),true);
         }
      }
      
      return $filexpired;
   }
   
   private  function _getAllCachedKey( $path = '.',$retDirList = Array())
   { 
         $ignore = array( 'cgi-bin', '.', '..' ); 
         $dh     = @opendir( $path ); 
         while(($file = readdir($dh)) !== false )
         { 
             if(!in_array( $file, $ignore ))
             {
                 if(is_dir("$path/$file")){ 
                     $retDirList   = $this->_getAllCachedKey("$path/$file",$retDirList); 
                 }else{ 
                     $retDirList[] = $path."/".$file;
                 } 
             } 

         } 
         
         closedir( $dh ); 
         return $retDirList;
   }
   
   private function _getKeySubfolderPath($key)
   {
      $cachedir  = self::$_cache_directory;
      
      if(strlen($key)>0 && strlen($key)>=3){
         $cachedir  = self::$_cache_directory."/".strtoupper(substr($key,0,1))."/".strtoupper(substr($key,1,1));
      }
      
      return $cachedir;
   }
   
   
   private function _buildKeySubfolder($key)
   {
      if(strlen($key)<=2){
         return false;
      }
      
      $cachedir = $this->_getKeySubfolderPath($key);
     
      if(!file_exists($cachedir)){
         mkdir($cachedir,0777,true);
      }
      
      return $cachedir;
   }
   
   /**
    * Elimina una directory e tutte le sue sottodirectory ricorsivamente
    * 
    * @param String $dir Directory
    * 
    * @return boolean
    */
   private function _rrmdir($dir)
   {
      if (!file_exists($dir)) 
        return true;

      if (!is_dir($dir)) 
        return unlink($dir);

      foreach (scandir($dir) as $item) 
      {
         if ($item == '.' || $item == '..'){
            continue;
         }
         else if (!$this->_rrmdir($dir.DIRECTORY_SEPARATOR.$item)){
                 return false;
         }
      }

      return rmdir($dir);       
   }
   
}