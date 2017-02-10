<?php

namespace Rain\Tpl;

class Modifiers 
{
    
    private static function getCharset()
    {
        
        if(!defined("SITE_CHARSET"))
        {
           throw new Exception('Cannot find constant SITE_CHARSET!',362364892);
        }
        
        
        return SITE_CHARSET;
    }
    
    public static function getClassName(){
       return __CLASS__;
    } 
   
    public static function lower($string){
       return mb_strtolower($string,self::getCharset());
    }
    
    public static function capitalize($string){
       return ucfirst($string);
    }
    
    public static function upper($string){
       return mb_strtoupper($string,self::getCharset());
    }
    
    public static function count_chatacters($string){
       return mb_strlen($string,self::getCharset());
    }
    
    public static function cat($string,$str_to_contact){
       return $string.$str_to_contact;
    }
    
    public static function count_paragraphs($string){
       return substr_count(".\r\n");
    }
    
    
    public static function count_sentences($string){
       return substr_count(".");
    }
    
    public static function count_words($string){
       return str_word_count($string,0);
    }
    
    public static function date_format($date,$format)
    {
       $timestamp  = strtotime($date);       
       if(is_int($timestamp)){
            $date = date($format,$timestamp);
       }
       
       return $date;
    }
    
    public static function date_lcformat()
    {
       return call_user_func_array(array('Utility_CommonFunction','Date_getDateLcFormat'),func_get_args());
    }
    
    
    public static function escape($string,$type = "html")
    {
       switch($type)
       {
          case "htmlall":     return htmlentities($string);             break;
          case "url":         return urlencode($string);                break;
          case "quotes":      return htmlentities($string,ENT_QUOTES);  break;
          case "hex":         
                              $encoded = bin2hex($string); 
                              $encoded = chunk_split($encoded, 2, '%'); 
                              $encoded = '%' . substr($encoded, 0, strlen($encoded) - 1); 
                              return $encoded;    
                              
                              break;
          case "hexentity":
                              $return = '';
                              for($i = 0; $i < strlen($string); $i++) { $return .= '&#x'.bin2hex(substr($string, $i, 1)).';'; }
                              return $return;    
          case "javascript":
                              return addslashes($string);      break;
             
          case "html":        return htmlentities($string);    break;
       }
       
       return $string;
    }

    
    public static function nl2br($string){
       return preg_replace("/\n|r\n/","<br>",$string);
    }

    public static function regex_replace($string,$regexpr,$replace){
       return preg_replace($regexpr,$replace,$string);
    }
   
    public static function replace($string,$search,$replacement){
       return str_replace($search, $replacement, $string);
    }
    
    
    public static function spacify($string,$spacecharacter=" "){
       return preg_replace("/(.)/","$1{$spacecharacter}",$string);
    }
    
    public static function string_format($string,$arg = null)
    {
       $params = func_get_args();
       $string = $params[0];
       array_shift($params);       
       return call_user_func_array("sprintf",array_merge(Array($string),$params));
    }
    
    public static function strip($string, $replacement= " ")
    {
       return preg_replace("/(\n|\s+){2,}/",$replacement,$string);
    }
    
    public static function strip_tags($string, $replaceSpace = true)
    {
       return trim(preg_replace('/<[^>]*>/',$replaceSpace ? ' ': '', $string));
    }
    
    
    public static function truncate($string,$length = 80,$appendString=" ...",$afterword = false)
    {
       $retString = "";
       
       if(!$afterword)
       {
          $retString =  substr($string,0,$length).$appendString;
       }
       else
       {
            $allWords = str_word_count($string, 2);

            foreach($allWords as $word)
            {
               if(strlen($retString.$word)<=$length){
                  $retString.=$word;
               }
            }
       }
       
       return $retString;
    }
    
     public static function wordwrap($string,$length = 80,$wrapString="\n",$afterword = false)
     {
         $retString = "";

         if(!$afterword)
         {
            for($i=0;$i<strlen($string);$i++)
            {
               if($i+1 % $length == 0){
                  $retString.=$string[$i].$wrapString;
               }else{
                  $retString.=$string[$i];
               }
            }
         }
         else
         {
              $allWords = str_word_count($string, 2);

              foreach($allWords as $word)
              {
                 if(strlen($retString.$word) % $length == 0){
                    $retString.=$word.$wrapString;
                 }else{
                    $retString.=$word;
                 }
              }
         }

         return $retString; 
     }
     
     public static function json($array)
     {
         return json_encode($array);
     }
     
     public static function defaultValue($name,$defaultValue) 
     {
        return empty($value) ? $defaultValue : $value;
     }
}