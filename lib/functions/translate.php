<?php

if(!function_exists('translate'))
{
   /**
    * [FUNCTION ALIAS]
    * 
    * Funzione Alias di Application_Languages::translate();
    * 
    * Traduce una stringa nella lingua selezionata del portale
    * 
    * @param String   $code                  Chiave parola da tradurre
    * @param Mixed    $domain / $replacement [OPZIONALE] Dominio delle stringhe, se NULL applica quello di default dell'oggetto Application_Languages
    * @param Mixed    $replacement           [OPZIONALE] Parametri per effettuare le sostituizioni nella stringa, verrà effettuato sia la formattazione che la sostituzione di stringhe, es: ["string1","string2","STRINGA"=>"valore"]
    * @param Boolean  $returnFallbackValue   [OPZIONALE] Indica se restiure il valore di fallback  qualora non fosse presente nel catalogo della lingua in uso, default TRUE 
    * 
    * @see Application_Languages
    */
   function translate($code,$domain = null ,array $replacement = array(),$returnDefault = false)
   {
      return getApplicationService('translate')->translate($code,$domain,$replacement,$returnDefault);
   }
}
