<?php

require_once dirname(__FILE__).'/Utility_ArrayToXML.class.php';

/**
 * Classe di funzioni ed Utility portale. 
 * <br>
 * <b>Non estendibile</b>
 */
class Utility_CommonFunction
{
    
   use Trait_Singleton;
   
   /**
    * Version
    * 
    * @var String
    */
   public static $_file_default_last_update = "1.1";
   
   /**
    * Ip not found
    * 
    * @var String
    */
   public static $_ip_not_found             = "127.0.0.1";
   
   
   /**
    * Effettua il listing della directory specificata restituendo la lista delle cartelle presenti
    * 
    * @param String $directory Path assoluto della directory
    * 
    * @return Array
    */
   public static function Directory_getSubdirectoriesList($directory)
   {
      $dirScan = scandir($directory);

      $subdirectoriesList = Array();
      if(is_array($dirScan) && count($dirScan) > 0)
      {
         foreach($dirScan as $subdirectory)
         {
            if(!is_file($subdirectory) && $subdirectory[0] != '.'){
               $subdirectoriesList[] = $subdirectory;
            }
         }
      }
      
      return $subdirectoriesList;
   }
   
   /**
    * Elimina tutti i file in una directory
    * 
    * @param String $directory Path directory
    * 
    * @return Boolean
    */
   public static function Directory_ClearAllFiles($directory)
   {
       if(!file_exists($directory))
       {
           return false;
       }
       
       if(class_exists('\DirectoryIterator'))
       {
            foreach (new \DirectoryIterator($directory) as $fileInfo) 
            {
                 if(!$fileInfo->isDot()) 
                 {
                     unlink($fileInfo->getPathname());
                 }
            }
            
            $allFiles = self::File_getFilesInDirectory($directory);            
       }
       else
       {
            array_map('unlink', glob($directory.'/*'));
       }
       
       return count($allFiles) == 0;
   }
   
   //########################################################################################
   //###                   FILE FUNCTION                                                  ###
   //########################################################################################
   
   /**
    * Restituisce tutti i file all'interno di una directory
    * 
    * @param String  $directory            Path assoluto della directory
    * @param Array   $filters              [OPZIONALE] Lista dei file da filtrare, default "." e ".."
    * @param Int     $scandirSortOrder     [OPZIONALE] Ordinamento dei file, SCANDIR_SORT* constant
    * @param Boolean $absolutePath         [OPZIONALE] Indica se ottenere il percorso assuluto dei file, default FALSE
    * 
    * @return Array lista dei path assoluti dei file contentuti
    */
   public static function File_getFilesInDirectory($directory,array $filters = array(".","..",".DS_Store","README"),$scandirSortOrder = SCANDIR_SORT_ASCENDING,$absolutePath = false)
   {
      $allFiles = array();
      
      if(file_exists($directory))
      {
         $allFiles = scandir($directory,$scandirSortOrder);

         foreach($allFiles as $key => $fileName)
         {
            if(count($filters) > 0)
            {
                if(in_array($fileName,$filters))
                {
                   unset($allFiles[$key]);
                }
                
                foreach($filters as $filePattern)
                {
                   if($filePattern[0] == "/" && $filePattern[strlen($filePattern)-1] == "/")
                   {
                        if(!@preg_match($filePattern,$fileName))
                        {
                            unset($allFiles[$key]);
                        }
                   }
                }
            }
            
            if(isset($allFiles[$key]) && $absolutePath)
            {
                $allFiles[$key] = $directory. DIRECTORY_SEPARATOR . $fileName;
            }
         }
      }
      
      return $allFiles;
   }
   
   /**
    * Legge il contenuto di un file, potendo specificare il nr di linee e se restituirlo invertito
    * 
    * @param String   $file            Path del file
    * @param Boolean  $reverse         [OPZIONALE] Indica se restituirlo invertito, default FALSE
    * @param Int      $linesNumber     [OPZIONALE] Indica il numero di linee, default 'all'
    * 
    * @return String    Contenuto del file, FALSE se il file non è stato letto correttamente o non esiste
    */
   public static function File_read($file,$reverse = false,$linesNumber = 'all')
   {
       if(!file_exists($file))
       {
          return false;
       }
       
       $content     = "";
       $textLines   = array();

       if($reverse  || ($linesNumber != 'all' && $linesNumber > 0))
       {
            $handle      = fopen($file, "r");
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
          $content = file_get_contents($file);
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
    * Restituisce la data dell'ultima modifica file del filesystem in formato timestamp
    * @param String $http_file_folder
    * @return Int
    */
    public static function File_get_date_last_update($http_file_folder)
    {
        $file = ROOT_PATH."/user/html".$http_file_folder;
        if(file_exists($file))
            return filemtime($file);
        
        return  self::$_file_default_last_update;
    }

    /**
     * Restituisce l'estenzione di un file
     * @param String $filePath Percorso assuluto file
     * @return String Estenzione file
     */
    public static function File_get_extension($filePath)
    {
        if(self::OS_isLinux())
            return self::_File_get_extension_linux ($filePath);
        
        return self::_File_get_extension_win($filePath);
    }
    
    /**
     * Restituisce l'estenzione del file su sistemi operativi Linux
     * @param String $file nome file
     * @return String
     */
    private static function _File_get_extension_linux($file)
    {
        $fileNameArr =  pathinfo($file);
        return $fileNameArr["extension"];
    }
    
    /**
     * Restituisce l'estenzione del file su sistemi operativi Window
     * @param String $file nome file
     * @return String
     */
    private static function _File_get_extension_win($file)
    {
        $filenamearr = explode(".",$file);
        return $filenamearr[count($filenamearr)-1];
    }
    
    
    /**
     * Restituisce il nome di un file, opzionale con estenzione
     * @param String  $file_absolute_path Percorso file
     * @param Boolean $file_extension false
     * @return String Nome file
     */
    public static function File_get_name($file_absolute_path,$file_extension=false)
    {
        $pathInfo =  pathinfo($file_absolute_path);
        $retStr   = $pathInfo["filename"];
        
        if($file_extension)
            $retStr = isset($pathInfo["basename"]) ? $pathInfo["basename"] : $retStr;
        
        return $retStr;
    }
    
    /**
     * Restituisce il path del file (senza "/" alla fine opzionale)
     * @param String  $file_absolute_path Percorso Assoluto del file
     * @param Boolean $endSlash Indica se inserire il carattere "/" alla file della stringa
     * 
     * @return String Path del file
     */
    public static function File_get_path($file_absolute_path,$endSlash=false)
    {
        $pathInfo =  pathinfo($file_absolute_path);
        $retStr   = $pathInfo["dirname"];
        if($endSlash)
            $retStr.= "/";
        
        return $retStr;
    }
    
    /**
     * Controlla esistenza file tramite http, basandosi su path http assoluti
     * 
     * @param String $path Assoluto es http://dominio/file.txt
     * @return Boolean
     */
    public static function File_exists_http($path)
    {
        return (@fopen($path,"r")==true);
    }
    
    /**
     * Verifica esistenza file Javascript
     * @param String $js_file_name Nome del file
     * @return Boolean
     */
    public static function File_js_exists($js_file_name)
    {
       $file_path = ROOT_PATH.STYLESHEET_PATH.JS_PATH."/".$js_file_name;
       return file_exists($file_path);
    }
    
    /**
     * Verifica esistenza file Css
     * @param String $css_file_name Nome del file
     * @return Boolean
     */
    public static function File_css_exists($css_file_name)
    {
       $file_path = ROOT_PATH.STYLESHEET_PATH.CSS_PATH."/".$css_file_name;
       return file_exists($file_path);
    }
    
    
  
    /**
     * Elimina una cartella ricorsivamente.
     * @param String $dir Percorso assoluto della cartella da eliminare fisicamente sul filesystem
     * @return Boolean
     */
    public static function rrmdir($dir)
    {
       if (is_dir($dir))
       {
          $objects = scandir($dir);
          foreach ($objects as $object)
          {
             if ($object != "." && $object != "..")
             {
                if (filetype($dir."/".$object) == "dir")
                   self::rrmdir($dir."/".$object);
                else
                   unlink($dir."/".$object);
             }
         }
         reset($objects);
         rmdir($dir);
         return true;
      }
      return false;
    }
 
   //#####################################################################
   //###                   ARRAY FUNCTION                              ###
   //#####################################################################
    
   /**
    * Genera una query stringa encoded da un array associativo
    * 
    * @param Array $array Array associativo singolo monodimensionale
    * 
    * @return String 
    */
   public static function Array_to_queryString($array)
   {
       if(is_array($array) && count($array)>0){
          return http_build_query($array);
       }
       
       if(is_string($array) && strlen($array)>0){
          return $array;
       }
       
       
       return "";
   }
   
   
   /**
    * Indica se un array e un array multidimensionale
    * 
    * @param Array $array
    * 
    * @return Boolean
    */
   public static function Array_is_multidimensional($array){
      return count($array)!=count($array,COUNT_RECURSIVE);
   }
    
   /**
    * Filtra un Array in base alla chiave o lista di chiavi fornite. L'array restituito non avrà le chiavi fornite.
    * 
    * @param Array $array        Array
    * @param Mixed $MatchedField Stringa chiave o lista di chiavi dell'array da rimuovere
    * 
    * @return Array
    */
   public static function Array_filterKeySearch($array,$MatchedKeys)
   {
       
        if(is_array($array) && count($array)>0)
        {
            $newArray  = Array();  
            
            foreach($array as $key=>$value)
            {
                if(is_array($MatchedKeys) && !in_array($key,$MatchedKeys)){
                       $newArray[$key] = $value;
                }
                else if(is_string($MatchedKeys) && $key<>$MatchedKeys){
                    $newArray[$key] = $value;
                }
            }
            
            return $newArray;
        }
        return false;
   }
   
   /**
    * Filtra un Array in base alla chiave o lista di chiavi fornite. L'array restituito  avrà esclusivamente le chiavi fornite.
    * 
    * @param Array $array        Array
    * @param Mixed $MatchedField Stringa chiave o lista di chiavi dell'array da restiture
    * 
    * @return Array
    */
   public static function Array_filterByKeys($array,$MatchedKeys)
   {
       
        if(is_array($array) && count($array)>0)
        {
            $newArray  = Array();  
            
            foreach($array as $key=>$value)
            {
                if(is_array($MatchedKeys) && in_array($key,$MatchedKeys)){
                       $newArray[$key] = $value;
                }
                else if(is_string($MatchedKeys) && $key == $MatchedKeys){
                    $newArray[$key] = $value;
                }
            }
            
            return $newArray;
        }
        
        return false;
   }
    
   /**
    * Filtra un Array ricercando il singolo valore  o una Lista eliminado la chiave relative
    * 
    * @param Array $array
    * @param Mixed $MatchedField Valore da ricercare singolo o Array di valori da Ricercare
    * 
    * @return Array
    */
   public static function Array_filterValuesSearch($array,$MatchedField)
   {
       
        if(is_array($array) && count($array)>0)
        {
            $newArray  = Array();  
            
            foreach($array as $key=>$value)
            {
                if(is_array($MatchedField) && !in_array($value,$MatchedField)){
                       $newArray[$key] = $value;
                }
                else if(is_string($MatchedField) && $value<>$MatchedField){
                    $newArray[$key] = $value;
                }
            }
            
            return $newArray;
        }
        return false;
   }
   
   /**
    * Converte un Array in un XML valido
    * 
    * @param Array      $array          Array
    * @param String     $encoding       [OPZIONALE] Encoding, default UTF-8
    * @param String     $rootNodeName   Nome nodo principale
    * 
    * @return String Xml
    */
   public static function Array_to_XML($array,$encoding = 'utf-8',$version = '1.0',$rootNodeName = 'root')
   {       
       Utility_ArrayToXML::init($version, $encoding);
       $xml =  Utility_ArrayToXML::createXML($rootNodeName, $array)->saveXML();
       return html_entity_decode($xml,ENT_XHTML);
   }
   
   
   /**
    * Converte un array in un Object
    * 
    * @param array  $array       Array
    * @param String $className   Nome della classe
    * 
    * @return Mixed Object
    */
   public static function Array_to_Object(array $array,$className = 'stdClass')
   {
       $object = new $className();
       
       if(count($array) > 0)
       {
           foreach($array as $key => $value)
           {
               $object->{$key} = is_array($value) ? self::Array_to_Object($value,$className) : $value;
           }
       }
       
       return $object;
   }
   
    //######################################################################
    //###                   STRING FUNCTION                              ###
    //######################################################################

   /**
    * Converte una stringa in camelCase
    * 
    * @param String $string   Stringa 
    * @param array  $exclude  Array caratteri da escludere
    * 
    * @return String
    */
   public static function String_StringToCamelcase($string, array $exclude = array())
   {
       $search   = explode(",","ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,ø,Ø,Å,Á,À,Â,Ä,È,É,Ê,Ë,Í,Î,Ï,Ì,Ò,Ó,Ô,Ö,Ú,Ù,Û,Ü,Ÿ,Ç,Æ,Œ");
       $replace  = explode(",","c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,o,O,A,A,A,A,A,E,E,E,E,I,I,I,I,O,O,O,O,U,U,U,U,Y,C,AE,OE");
       $string   =  str_replace($search, $replace, $string);
       $string   = preg_replace('/[^a-z0-9' . implode("", $exclude) . ']+/i', ' ', $string);
       $string   = ucwords(trim($string));
       
       return lcfirst(str_replace(" ", "", $string));
   }

   /**
    * Valuta se il parametro ? stringa, opzionale verifica se anche alfanumerica
    * 
    * @param String  $value        Stringa da valutare
    * @param Boolean $flagNumeric  Boolean se true ammette anche i numeri.
    * 
    * @return Boolean
    */
   public static function String_isString($value,$flagNumeric=false)
   {
      $pattern = "/[^A-Za-z$]/";
      if($flagNumeric)
         $pattern = "/[^A-za-z0-9$]/";
      
      return preg_match($pattern,$value);
   }

   /**
    * Controllo validit? email
    * 
    * @param String $email
    * @param Boolean $skipDNS  salto controllo esistenza dns, default true
    * 
    * @return Boolean
    */
   public static function String_isValidEmail($email, $skipDNS = true)
   {
           $isValid = true;
           $atIndex = strrpos($email, "@");
           if (is_bool($atIndex) && !$atIndex)
           {
                  $isValid = false;
           }
           else
           {
                  $domain = substr($email, $atIndex+1);
                  $local = substr($email, 0, $atIndex);
                  $localLen = strlen($local);
                  $domainLen = strlen($domain);
                  if ($localLen < 1 || $localLen > 64)
                  {
                         // local part length exceeded
                         $isValid = false;
                  }
                  else if ($domainLen < 1 || $domainLen > 255)
                  {
                         // domain part length exceeded
                         $isValid = false;
                  }
                  else if ($local[0] == '.' || $local[$localLen-1] == '.')
                  {
                         // local part starts or ends with '.'
                         $isValid = false;
                  }
                  else if (preg_match('/\\.\\./', $local))
                  {
                         // local part has two consecutive dots
                         $isValid = false;
                  }
                  else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
                  {
                         // character not valid in domain part
                         $isValid = false;
                  }
                  else if (preg_match('/\\.\\./', $domain))
                  {
                         // domain part has two consecutive dots
                         $isValid = false;
                  }
                  else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local)))
                  {
                         // character not valid in local part unless
                         // local part is quoted
                         if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\","",$local)))
                         {
                                $isValid = false;
                         }
                  }

                  if(!$skipDNS)
                  {
                          if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A")))
                          {
                                 // domain not found in DNS
                                 $isValid = false;
                          }
                  }
           }
           return $isValid;
   }
   
   /**
    * Converte una query String in Array
    * 
    * @param String $string 
    * 
    * @return Array
    */
   public static function String_parse_to_array($string)
   {
      $output = Array();
      parse_str($string, $output);
      return  $output;
   }
   
   /**
    * Sanitize String su encoding us-ascii//TRANSLIT
    * 
    * @param String $string Stringa da pulire
    * 
    * @return String
    */
   public static function String_SanitizeIconv($string)
   {
        $string = trim($string);
        setlocale(LC_ALL, "en_US.utf8");
        
        $string = iconv("utf-8", "us-ascii//TRANSLIT", $string);
        $array  = explode(' ', $string);
        
        if(is_array($array))
        {
            foreach ($array as $index => $value)
            {
                $value = preg_replace(Array("/[^A-Za-z0-9]/"), Array(""), $value);
                if ($value == ''){
                    unset($array[$index]);
                }
            }
            
            $string = implode(' ', $array);
        }

        $string = preg_replace(Array("/[^A-Za-z0-9]/"), Array(" "),$string);
        $string = str_replace(" ", "_", $string);

        $string =  strtolower($string);

        return $string;
   }
   
   /**
    * Controlla che una stringa sia serializzata
    * 
    * @param String $data
    * 
    * @return Boolean
    */
   public static function String_isSerialized($data)
   {
        // if it isn't a string, it isn't serialized
        if ( !is_string( $data ) )
            return false;
        $data = trim( $data );
        if ( 'N;' == $data )
            return true;
        if ( !preg_match( '/^([adObis]):/', $data, $badions ) )
            return false;
        switch ( $badions[1] ) {
            case 'a' :
            case 'O' :
            case 's' :
                if ( preg_match( "/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data ) )
                    return true;
                break;
            case 'b' :
            case 'i' :
            case 'd' :
                if ( preg_match( "/^{$badions[1]}:[0-9.E-]+;\$/", $data ) )
                    return true;
                break;
        }
        return false;
   }
   
   
   //######################################################################
   //###                  NUMERIC FUNCTION                              ###
   //######################################################################

   /**
    * Qusta funzione formatta un numero.
    * @example Numeric_Format(1234,344,'.',',',3,1) -> 1.235,350
    * @param Int,Float,Double  $number Numero da formattare
    * @param String $sepThousand Separatore Migliaia
    * @param String $sepDec Separatore decimali
    * @param Int    $qntDec Specifica quanti numeri dopo il separatore delle decine
    * @param Int    $round  1 eccesso 0 difetto
    * @return Double Number
    */
   public static function Numeric_Format($number,$sepThousand,$sepDec,$qntDec,$round=null)
   {
        $number = number_format($number,$qntDec,$sepDec,$sepThousand);

        if($round==1)
            return ceil ($number);
        if($round==0)
           return floor($number);
        return $number;
   }

   /**
    * Qusta funzione formatta un numero nella valuta Euro
    * @example Numeric_Format(1234,344,'.',',',3,1) -> 1.235,350
    * @param Int,Float,Double  $number Numero da formattare
    * @param String $sepThousand Separatore Migliaia
    * @param String $sepDec Separatore decimali
    * @param Int    $qntDec Specifica quanti numeri dopo il separatore delle decine
    * @param Int    $round  1 eccesso 0 difetto
    * @return Double Number
    */
   public static function Numeric_EuroConvert($number,$sepThousand,$sepDec,$qntDec,$round=null)
   {
      
       $euroVal = self::Numeric_Format($number, $sepThousand, $sepDec, $qntDec,-1);
       
       if(is_int($euroVal)){
           return $euroVal.",00";
       }

       return $euroVal;
   }
  


   //#########################################################################
   //###                   DATE FUNCTION                                   ###
   //#########################################################################
    
   /**
    * Restituisce la data e l'ora attuale in formato mysql (Y-m-d H:i:s)
    * 
    * @param String $format Formato ora, default "Y-m-d H:i:s"
    * 
    * @return String
    */
   public static function Date_getNow($format = "Y-m-d H:i:s")
   {
      return date($format,time());
   }
   
   /**
    * Formatta una data attraverso il locale in uso, Restituendo la data localizzata per lingua
    * 
    * @param String   $date       DataOra
    * @param String   $format     Formato da ottenere, default "<mese> <anno>" es: 'Gennaio 2013'
    * @param String   $timezone   Timezone da Usare, default 'Europe/Rome'
    * @param String   $locale     Locale, default utilizzato quello da php tramite setlocale precedentemente impostato
    * 
    * @return String Data Formattata
    */
   public static function Date_getDateLcFormat($date,$format = '%B %Y',$timezone = 'Europe/Rome',$locale = null)
   {
       $timestamp  = strtotime($date);       
       
       if(is_int($timestamp) && $timestamp>0)
       {
         $currentTimeZone = date_default_timezone_get();
         date_default_timezone_set($timezone);
         
         if(!is_null($locale))
         {
            setlocale(LC_TIME,$locale);
         }
         
         $date = strftime($format,$timestamp);
         date_default_timezone_set($currentTimeZone);
       }
       
       return $date;
   }
   
   /**
    * Calcola la data ottenuta aggiungendo n giorni alla data specificata
    * 
    * @param Date $date    Data di partenza
    * @param Int  $nrdays  Numero di giorni da aggiungere
    * 
    * @return Date
    */
   public static function Date_getDatePlusDays($date,$nrdays)
   {
       return date('Y-m-d', strtotime("+{$nrdays} days",strtotime($date)));
   }
   
   /**
    * Restituisce i microsendi di time()
    * @return Int
    */
   public static function Date_getMicroseconds()
   {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
   }

   /**
    * Determina se l'anno $year ? bisestile
    * @param Int $year
    * @return Boolean
    */
   public static function Date_YearIsBisestile($year)
   {
      if(!is_numeric($year) || $year<=0)
         return false;
      
      return (($year % 4) == 0) ? true : false;
   }

   /**
    * Formatta una data secondo il formato passato in ingresso
    * @param Date   $date
    * @param String $format
    * @return Date nel formato $format
    */
   public static function Date_format($date,$format)
   {
      if(preg_match("/\//",$date)){
         $date = str_replace ("/","-",$date);
      }
      if(strtotime($date)>0)
         return date($format,strtotime($date));
      return null;
   }

   /**
    *
    * Calcola i giorni di differenza tra due date
    * 
    * @param Date   $dateFrom     Data Inizio
    * @param Date   $dateTo       Data Fine
    * @param String $date_format  il formato in cui sono le date passate
    * 
    * @return Int Differenza di giorni tra le due date
    *
    */

   public static function Date_datediff($dateFrom,$dateTo,$date_format="Y-m-d H:i:s")
   {
         $timestamp_from = strtotime(self::Date_format($dateFrom,$date_format));
         $timestamp_to   = strtotime(self::Date_format($dateTo,$date_format));
         return ($timestamp_to - $timestamp_from ) / 3600*24;
   }

  /**
   * Controlla che la data passata sia Valida nel formato Y-m-d
   * 
   * @param  Date     $date    Data
   * @param  String   $format  Formato Data,default Y-m-d
   * 
   * @return Boolean
   */
   public static function Date_isValidDate($date,$format = 'Y-m-d'){
      return date($format,strtotime($date)) == $date;
   }
  
  /**
   * Controlla che la data/ora passata sia Valida nel formato Y-m-d H:i:s
   * 
   * @param  DateTime $datetime Data Ora
   * @param  String   $format   Formato Data Ora
   * 
   * @return Boolean
   */
   public static function Date_isValidDateTime($datetime,$format = 'Y-m-d H:i:s'){
       return date($format,strtotime($datetime)) == $datetime;
   }
   
   /**
    * Restituisce la data di inizio della settimana di una data
    * 
    * @param Date $date Y-m-d
    * 
    * @return Date
    */
   public static function Date_getWeekStart($date)
   {
        $arrDate = explode(" ",$date);
        if(count($arrDate)>0){
            $date = $arrDate[0];
        }

        $date.=" 02:00:00";

        for($i=strtotime($date)-(3600*24*7);$i<=strtotime($date);$i+=3600*24)
        {
            if(date("N",$i)==1)
                return date("Y-m-d",$i);
        }
   }

   /**
    * Calcola in numero dell'ultimo giorno del Mese su base mese/anno
    * @param Int $month
    * @param Int $year
    * @return Int numero del giorno
    */
   public static function Date_getEndDayOfMonth($month,$year)
   {
           switch($month)
           {
              case 01: return 31;
                       break;
              case 02: if(self::Date_YearIsBisestile($year))
                          return 29;
                       else
                          return 28;
                       break;
              case 03: return 31;
                       break;
              case 04: return 30;
                       break;
              case 05: return 31;
                       break;
              case 06: return 30;
                       break;
              case 07: return 31;
                       break;
              case 8:  return 31;
                       break;
              case 9:  return 30;
                       break;
              case 10: return 31;
                       break;
              case 11: return 30;
                       break;
              case 12: return 31;
                       break;
           }
     }
     
     /**
      * Restituisce un Array contente tutti i giorni del mese da 1 a 31
      * @return Array int
      */
     public static function Date_getDayArr($month)
     {
         $day_arr = Array();
         for($i=1;$i<=31;$i++)
             $day_arr[$i] = $i; 
         
         return $day_arr;
     }
     
     /**
      * Restituisce un Array contenente tutti i mesi dell'anno da tradurre in base al locale utilizzato.
      * 
      * @param  Boolean $translated Indica se restituire la lista dei mesi tradotta
      * 
      * @return Array
      */
     public static function Date_getMonthsArray($translated = true)
     {
        $translate = $translated ? function($string){ return translate($string); } : function($string){ return $string;};
        
        return Array(   1  => $translate('MONTH01'),
                        2  => $translate('MONTH02'),
                        3  => $translate('MONTH03'),
                        4  => $translate('MONTH04'),
                        5  => $translate('MONTH05'),
                        6  => $translate('MONTH06'),
                        7  => $translate('MONTH07'),
                        8  => $translate('MONTH08'),
                        9  => $translate('MONTH09'),
                        10 => $translate('MONTH10'),
                        11 => $translate('MONTH11'),
                        12 => $translate('MONTH12'));
     }
     
     
     
     /**
      * Restituisce un Array contenente gli anni compresi tra le date in input
      * @param Int $year_start
      * @param Int $year_end
      * @return Array 
      */
     public static function Date_getYearArr($year_start,$year_end)
     {
         if($year_start<=$year_end)
         {
            $year_arr = Array();
            for($i=intval($year_end);$i>=intval($year_start);$i--)
               $year_arr[$i] = $i;   
         
            return $year_arr;
         }
         return Array();
     }

   //##########################################################################
   //###                   OTHER FUNCTION                                   ###
   //##########################################################################

   /**
    * Aggancia al numero $n in testa o coda un numero di zeri tali da raggiungere il valore complessivo della lunghezza di $qntZero
    * 
    * @example getZero(10,5,"0",STR_PAD_LEFT) restituisce 00010.
    *
    * @param Int     $number     Numero da formattare
    * @param Int     $qntZero    Numero di Caratteri totali della stringa
    * @param String  $padString  [OPZIONALE] Carattere per il pad
    * @param Int     $headTail   [OPZIONALE] STR_PAD_LEFT | STR_PAD_RIGHT | STR_PAD_BOTH, default STR_PAD_LEFT
    * 
    * @return string
    */
    public static function getZeroFill($number,$qntZero,$padString="0",$headTail=STR_PAD_LEFT){   
       return str_pad((int) $number,$qntZero,$padString,$headTail);
    }

   /**
    * Determina l'indirizzo IP publico del client connesso al portale
    * @return String IP Address
    */
    public static function getIP()
    {
        if (isset($_SERVER['HTTP_CLIENT_IP']) && strlen($_SERVER['HTTP_CLIENT_IP'])>0)         //check ip from share internet
           return $_SERVER['HTTP_CLIENT_IP'];
        
        if (isset($_SERVER['HTTP_CLIENT_IP']) && strlen($_SERVER['HTTP_X_FORWARDED_FOR'])>0)   //to check ip is pass from proxy
          return $_SERVER['HTTP_X_FORWARDED_FOR'];
        
        if(isset($_SERVER['REMOTE_ADDR']))
          return $_SERVER['REMOTE_ADDR'];
        
        return self::$_ip_not_found;
    }
    

   
   /**
    * Converte un Object in Array associativo
    * 
    * @param Object $object Oggetto
    * 
    * @return Array
    */
   public static function ObjectToArray($object)
   {
     $retArr = Array();
     
     if(!is_object($object))
        return $retArr;
     
     foreach($object as $key => $value)
        $retArr[$key] = $value;
     
     return $retArr;
   }
   
   /**
    * Converte l'array di entità in array di Array invocando su ogni entità il metodo toArray()
    * 
    * @param Array|Entities     $entitiesArray Array di Entità o singola Entities
    * 
    * @return Array(Array Associativi)
    */
   public static function Entities_to_Array($entitiesArray)
   {
      $retArray = Array();
      
      if(is_array($entitiesArray) && count($entitiesArray)>0)
      {         
         foreach($entitiesArray as $key => $entities)
         {
            if(is_object($entities) && is_subclass_of($entities,"Abstract_Entities")){
               $retArray[$key] = $entities->toArray();
            }else {
               $retArray[$key] = $entities;
            }
         }
         
         return $retArray;
      }
      else if(is_object($entitiesArray) && is_subclass_of($entitiesArray,"Abstract_Entities"))
      {
         return $entitiesArray->toArray();
      }
      
      return $retArray;
   }
   
   /**
    * Converte l'array di entità in array di Array invocando su ogni entità il metodo toArray(), di ogni elemento dell'array cerchera il campo <keyName> e il relativo valore <value>,
    * cosi da restituire un array associativo chiave=>valore partendo da un array multidimensionale.
    * 
    * @param Array|Entities     $entitiesArray Array di Entità o singola Entities
    * 
    * @return Array(Array Associativi) | False in caso di errore
    */
   public static function Entities_to_Array_Associative($entitiesArray,$keyName = null,$valueName = null)
   {
      $retArray = Array();
      
      if(is_array($entitiesArray) && count($entitiesArray)>0)
      {
         foreach($entitiesArray as $entities)
         {
            if(is_object($entities) && is_subclass_of($entities,"Abstract_Entities")){
               $retArray[] = $entities->toArray();
            }
         }
      }
      else if(is_object($entitiesArray) && is_subclass_of($entitiesArray,"Abstract_Entities"))
      {
         $retArray =  $entitiesArray->toArray();
      }
      
      if(strlen($keyName)>0 && strlen($valueName)>0)
      {
         $tmpRetArray = $retArray;
         $retArray    = Array();
         
         foreach($tmpRetArray as $array)
         {
            if(isset($array[$keyName]) && isset($array[$valueName])){
               $retArray[$array[$keyName]] = $array[$valueName];
            }else{
               return false;
            }
         }
      }
      else if(strlen($keyName)>0 && is_null($values))
      {
         $tmpRetArray = $retArray;
         $retArray    = Array();
         
         foreach($tmpRetArray as $array)
         {
            if(isset($array[$keyName])){
               $retArray[$array[$keyName]] = self::Array_filterKeySearch($array,$keyName);
            }else{
               return false;
            }
         }
      }
         
      return $retArray;
   }
   
   
   /**
    * Rimuove gli slash
    * @param Mixed $variable (String,Array,MultipleArray)
    * @return Mixe $variable senza slash su stringhe 
    */
   public static function strip_slashes_recursive($variable)
   {
     if (is_string($variable))
        return stripslashes($variable);
     if (is_array($variable))
        foreach ($variable as $i => $value)
           $variable[$i] = self::strip_slashes_recursive($value);
     
     return $variable;
   }

   
   /**
    * Applica gli slash 
    * @param Mixed $variable Array,String
    * @return Mixed 
    */
   public static function add_slashes_recursive($variable)
   {
      if (is_string($variable))
        return addslashes($variable);
     
      else if (is_array($variable))
        foreach ($variable as $i => $value)
           $variable[$i] = self::add_slashes_recursive($value);
     
      return $variable;
   }
   
   /**
    * Restuisce html delle option di una select dall'array $optionArr passato. � possibile anche indicare un valore selezionato
    * 
    * @param Array   $optionArr Array formato key=>value
    * @param Mixed   $selectedValue Valore selezionato
    * @param String  $optionSelectValue Valore dell'opzione di selezione
    * @param String  $optionSelectLabel Valore text dell'opzione di selezione
    * 
    * @return String HTML
    */
   public static function HTML_renderSelectOption($optionArr,$valueSelected=null,$optionSelectValue=-1,$optionSelectLabel='---Selezionare---')
   {
         $html = "<option value=\"{$optionSelectValue}\">{$optionSelectLabel}</option>";
         
         if (count($optionArr) > 0)
         {
            foreach ($optionArr as $key => $value)
            {
               $selected = ($key == $valueSelected) ? "selected" : "";
               $html.="<option value=\"{$key}\" {$selected}>{$value}</option>";
            }
         }
         return $html;
   }
  
     /**
    * Restuisce html delle option di una select dall'array $optionArr passato. � possibile anche indicare un valore selezionato
    * 
    * @param Array   $objectsArr  Array di Oggetti
    * @param String  $keyMethod   Metodo da invocare per il value della option 
    * @param String  $valueMethod Metodo da invocare per il text della option
    * @param Mixed   $selectedValue Valore selezionato
    * @param String  $optionSelectValue Valore dell'opzione di selezione
    * @param String  $optionSelectLabel Valore text dell'opzione di selezione
    * 
    * @return String HTML Lista delle option della select
    */
   public static function HTML_renderSelectOptionObject($objectsArr,$keyMethod,$valueMethod,$selectedValue=null,$optionSelectValue=-1,$optionSelectLabel='---Selezionare---')
   {
         $html = "<option value=\"{$optionSelectValue}\">{$optionSelectLabel}</option>";
         
         if (count($objectsArr) > 0)
         {
            foreach ($objectsArr as $object)
            {
               $selected = ($object->$keyMethod() == $selectedValue) ? "selected" : "";
               $html.="<option value=\"".$object->$keyMethod()."\" {$selected}>".$object->$valueMethod()."</option>";
            }
         }
         return $html;
   }
   
   
   /**
    * Restituisce tutte le informazioni che verranno utilizzate nel template di paginazione
    * 
    * @param Int $recordCount          Numero di elementi totali
    * @param Int $rowxpage             Numero di record per pagina
    * @param Int $pageIntervall        Numero di pagine da visualizzare per volta se =0 non mostra i link delle singole pagine (F)
    * @param Int $currentPage          Pagina attuale
    * @param Boolean $showFirtPageLink Mostra il link "Prima Pagina" (A)
    * @param Boolean $showPrewPageLink Mostra il link "Pagina Prec" (B)
    * @param Boolean $showNextPrewLink Mostra il link "Pag Succ" (C)
    * @param Boolean $showLastPageLink Mostra il link "Ultima Pag" (D)
    * @param Boolena $showAllPageLink  Mostra il link "Tutti i record" (E)
    * @param Boolean $jsFunctionName   Nome funzione javascript alla quale viene passaga la varibile page
    * 
    * @return Array page Link
    */
    public static function HTML_getPaginationInfo($recordCount,$rowxpage=10, $pageIntervall=10, $currentPage=1, $showFirtPageLink=true, $showPrewPageLink=true, $showNextPrewLink=true, $showLastPageLink=true, $showAllPageLink=false, $jsFunctionName='getPage')
    { 
        $noPages = (int) ($recordCount / $rowxpage );

        if ($recordCount % $rowxpage)
            $noPages++;

        if ($noPages > 1) 
        {
            $pages = Array();
            
            $lowerLimit = (($currentPage - $pageIntervall) <= 0) ? 1 : ($currentPage - $pageIntervall);
            $c = $lowerLimit;
            while ($c <= $currentPage) 
            {
                $pages[] = $c;
                $c++;
            }

            $upperLimit = (($currentPage + $pageIntervall) >= $noPages) ? $noPages : ($currentPage + $pageIntervall);
            $c = $currentPage + 1;
            
            while ($c <= $upperLimit) {
                $pages[] = $c;
                $c++;
            }
            
            return Array("show"         => true,
                         "show_first"   => $showFirtPageLink && $currentPage>1 ? true : false,
                         "show_prev"    => $showNextPrewLink && $currentPage>1 ? true : false,
                         "pages"        => $pages,
                         "show_next"    => $showNextPrewLink  && $currentPage<$noPages ? true : false,
                         "show_last"    => $showLastPageLink  && $currentPage<$noPages ? true : false,
                         "show_all"     => $showAllPageLink,
                         "nr_page"      => $currentPage,
                         "nr_prev_page" => $currentPage-1,
                         "nr_next_page" => $currentPage+1,
                         "nr_last_page" => $noPages,
                         "js_function"  => $jsFunctionName);
        }
        
        return Array ("show"         => false,
                      "show_first"   => false,
                      "show_prev"    => false,
                      "pages"        => false,
                      "show_next"    => false,
                      "show_last"    => false,
                      "show_all"     => false,
                      "nr_page"      => 0,
                      "nr_prev_page" => 0,
                      "nr_next_page" => 0,
                      "nr_last_page" => 0,
                      "js_function"  => $jsFunctionName);
        
   }
   
  
   //##########################################################################
   //##########         ENVIROMENT FUNCTION                         ###########
   //##########################################################################
  
   /**
    * Restituisce vero se il sistema operativo ? linux, false altrimenti
    */
   public static function OS_isLinux()
   {
      if(function_exists("php_uname"))
         return is_int(strpos(php_uname(),"Linux"));
      
      return false;
   }
   
   /**
    * Verifica che lo script sia lanciato da console.
    * 
    * @return Boolean TRUE se script lanciato da console, False altrimenti
    * 
    */
   public static function  OS_isServerApiCli()
   {
      return getApplicationKernel()->isServerApiCLI();
   }
   
   
   //#########################################################################
   //##########         IMAGES FILE FUNCTION                         #########
   //#########################################################################
   
   
   /**
    * Carica L'array $_FILE nella directory specificata per un determinato tipo di oggetto
    * 
    * @param Array   $fileArr  ($_FILES)
    * @param Array   $fileNameArr Key Name $_FILES da caricare
    * @param String  $uploadDir Percorso assuluto (senza nome file)
    * @param String  $relPath   Percorso relativo dalla cartella images del package di default (senza nome file, es: '/pictures')
    * @param Boolen  $save_db   Determina se salvare il caricamento nel DB  Opzionale
    * @param Array   $pictObjInfo Array composto da 3 campi(0=>obj_id,1=>obj_sql_table_name,2=>obj_sql_table_id) Opzionale
    * 
    * @return Array File Caricati on success, false Altrimenti
    */
    public static function Images_Upload($fileArr,$fileNameArr,$uploadDir,$relPath,$create_thumb = false,$save_db=true,$pictObjInfo=Array())
    {
        if (isset($fileArr) && is_array($fileArr) && !empty($fileArr))
        {
           $uploadedArrInfo = Array();
           
           $upload          = new Utility_Upload($fileArr,$fileNameArr,MAX_FILE_SIZE,$uploadDir);
           $upload->setEncryption(1); //Maschera il nome dei file
           
           if($upload->startUpload())
           {
                $i=0;
                $arrInfo = $Upload->getUploadeArr();
                foreach ($arrInfo as $image)
                {
                    $FilePath            = $uploadDir . "/" . $image['name'] . "." . $image['ext'];   //Path foto appena caricata
                    $newFileName         = $image['name'] . preg_replace("/=/", "", base64_encode(rand(10000, 99999) . self::Data_getMicrosecond()));
                    $newFileNamePath     = $uploadDir . "/" . $newFileName . ".jpg";
                    $newFileNameRelPath  = $relPath   . "/" . $newFileName . ".jpg";
                    
                    list($width,$height) = getimagesize($FilePath);
                    
                    $uploadedArrInfo[$i] =  Array(
                                                  "error"         => 0,
                                                  "name"          => $newFileName,
                                                  "ext"           => "jpg",
                                                  "size"          => filesize($FilePath),
                                                  "width"         => $width,
                                                  "height"        => $height,
                                                  "path_absolute" => $uploadDir,
                                                  "path_relative" => $relPath,
                                                  "http_url"      => Portal_StaticContentBalance::getPicturesPath($newFileNameRelPath)
                                                  );

                    //*******IMAGE***************************
                    if(self::Image_Save($FilePath,$newFileNamePath,true))
                    {
                       if($save_db)
                           if(!self::Image_Save_DB($newFileNamePath,$pictObjInfo,$uploadedArrInfo[$i]))
                               return false;                 
                    }
                    
                    if($create_thumb)
                    {
                       if(!self::Image_Create_Thumbnails($newFileNamePath))
                          return false;
                    }
                    
                    
                       
                    //*******IMAGE***************************  
                    
                    $i++;
                }

                return $uploadedArrInfo;
            } 
        }
        return false;
    }

    /**
     * Salva l'immagine su disco fisico e salva nel DB tutte le informazioni necessarie (opzionale salvataggio)
     * 
     * @param String  $imgPathSource Percorso Assoluto Immagine Sorgente
     * @param String  $imgPathDest   Percorso Assoluto Immagine Destinazione
     * @param Boolean $delSource     Determina se cancellare il file orginale al termine del processo
     *
     * @return Boolean             
     */
    public static function Image_Save($imgPathSource,$imgPathDest,$delSource=true)
    {
          $image = new Utility_Thumbnail($imgPathSource);
          $image->output_format = 'JPG';
          
          $imageinfo = Array(IMAGES_WIDTH,IMAGES_HEIGHT);
          
          $image->size((int) $imageinfo[0],(int) $imageinfo[1]);
          $image->process();
          $res = $image->save($imgPathDest);
          if ($delSource)
              if(!unlink($imgPathSource))
                 return false;

          return $res;
    }
    
    /**
     * Salva Nel Database Tutte le informazioni relative ad un immagine acquisita relazionandola con
     * le informazioni di $pictObjInfo (Array oggetto relazione)
     * 
     * @param String  $imgPathSource Percorso Assoluto Immagine Sorgente
     * @param Array   $pictObjInfo   Array composto da 3 campi(0=>obj_id,1=>obj_sql_table_name,2=>obj_sql_table_id)   
     * @param Array   $pictArrInfo   Array composto da 6 campi Array(error => (0|1)
                                                                     name  => nome file senza estensione
                                                                     ext   => estenzione file
                                                                     size  => dimensione file
                                                                     path_absolute => percorso assoluto file
                                                                     path_relative => percorso relativo file
     * @return Boolean
     */
    public static function Image_Save_DB($pictObjInfo,$pictArrInfo)
    {
        $pictureManager = new EntitiesManager_Pictures($pictObjInfo[0], $pictObjInfo[1], $pictObjInfo[2]);
        return $pictureManager->AddObjSinglePicture($pictArrInfo);
    }
    
    /**
     * Effettua il Crop sull'immagine specificata salvadola nell'immagine specicata thumb
     * 
     * @param String $imgPathSource Path assoluto foto originale
     * @param String $imgThumbPath  Path assoluto foto thumbnaills
     * @param Int    $w             Width Foto
     * @param Int    $h             Height Foto
     * @param Int    $x1            Partenza x1 foto
     * @param Int    $y1            Partenza y1 foto
     * @param String $imageType     Tipo di immagine, default 'image/jpg'
     * 
     * @return String, Path immagine thumb
     */
    public static function Image_Crop($imgPathSource,$imgThumbPath,$w,$h,$x1,$y1,$imageType='image/jpg')
    {
         //$scale = 0.5;
         //$newImageWidth = ceil($imageWidth * $scale);
         //$newImageHeight = ceil($imageHeight * $scale);
       
         $newImage = imagecreatetruecolor($w,$h) or die("Cannot Initialize new GD image stream");

         switch($imageType) {
            case "image/gif":
               $source=imagecreatefromgif($imgPathSource); 
               break;
             case "image/pjpeg":
            case "image/jpeg":
            case "image/jpg":
               $source=imagecreatefromjpeg($imgPathSource); 
               break;
             case "image/png":
            case "image/x-png":
               $source=imagecreatefrompng($imgPathSource); 
               break;
         }


         //imagecopyresampled($newImage,$source,0,0,$start_width,$start_height,$newImageWidth,$newImageHeight,$imageWidth,$imageHeight) or die('unable to resize viewer image');
         imagecopy($newImage,$source,0,0,$x1,$y1,$w,$h) or die('unable to resize viewer image');

         switch($imageType) 
         {
            case "image/gif":
               imagegif($newImage,$imgThumbPath) or die('unable to copy image'); 
               break;
               case "image/pjpeg":
            case "image/jpeg":
            case "image/jpg":
               imagejpeg($newImage,$imgThumbPath,100) or die('unable to copy image'); 
               break;
            case "image/png":
            case "image/x-png":
               imagepng($newImage,$imgThumbPath) or die('unable to copy image');  
               break;
         }
         
         chmod($imgThumbPath, 0777);
         
         return $imgThumbPath;
    }
    /**
     * Crea immagini ridimensionate partendo da una immagine esistente
     * @param type $imgPathSource Percorso assoluto Immagine
     * @return Boolean
     */
    public static function Image_Create_Thumbnails($imgPathSource)
    {
        if(IMAGES_HAVE_THUMBNAILS)
        {
             $thumbnails_arr = unserialize(IMAGES_THUMBNAILS);
             foreach($thumbnails_arr as $thumb_prefix=>$thumb_info)
             {
                $thumb_file_name = self::File_get_path($imgPathSource,true).self::File_get_name($imgPathSource)."_{$thumb_prefix}.".self::File_get_extension($imgPathSource);
                //*******IMAGE THUMB SMALL***************************
                $imageThumb = new Utility_Thumbnail($imgPathSource);
                $imageThumb->output_format = 'JPG';
                $imageThumb->size((int) $thumb_info['THUMB_WIDTH'],(int) $thumb_info['THUMB_HEIGHT']);
                $imageThumb->process();
                if(!$imageThumb->save($thumb_file_name))
                    return false;
             }
             return true;
        }
    }
}