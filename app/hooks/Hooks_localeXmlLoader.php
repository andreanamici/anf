<?php

/**
 * Hook chiamato per la conversione di un file xml di un locale in un ArrayIterator di traduzioni
 */
class Hooks_localeXmlLoader extends Abstract_Hooks
{  
    
   public function __construct()
   {
       return $this->initMe(self::HOOK_TYPE_LOCALE_LOAD, self::getDefaultName());
   } 
   
   public function isEnable(\Application_HooksData $hookData)
   {
        $data = $hookData->getData();
        return isset($data['extension']) && $data['extension'] == 'xml';
   }
      
   public function doProcessMe(Application_HooksData $hookData) 
   {
        $appLanguage = $hookData->getData(); /*@var $appLanguage Application_Languages*/ 

        $localeInfo = $hookData->getData();

        $localePath = $localeInfo["path"];

        $xml            = simplexml_load_file($localePath);        
        $strings        = $xml->xpath("translate");
        
        $arrayIteratorCatalogue = new ArrayIterator();

        if(is_array($strings) && count($strings) > 0)
        {   
            foreach($strings as $key => $simpleXMLElement) /*@var $simpleXMLElement SimpleXMLElement*/
            {  
               $value = $simpleXMLElement->value;
               $id    = $simpleXMLElement->id;
               $arrayIteratorCatalogue->offsetSet((string) $id,(string) $value);
            }
        }
                
        $hookData->setData($arrayIteratorCatalogue);
   }
}