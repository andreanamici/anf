<?php

/**
 * Classe per la gestione delle lingue e dei locale
 * 
 * @method Application_Languages getInstance() Restituisce l'instanza originale di questo oggetto sfruttando il DesignPattern Singleton
 * 
 */
class Application_Languages implements Interface_ApplicationLanguages
{
    use Trait_ObjectUtilities,Trait_Singleton,Trait_DAO;
   
    use Trait_ApplicationKernel,Trait_ApplicationConfigs, Trait_ApplicationPlugins;
    
    /**
     * Lingua in formato small attualmente in uso
     * @var String{2}
     */
    private  $_lang               = null;
    
    /**
     * Locale in formato esteso attualmente in uso
     * @var string{5}
     */
    private  $_locale             = null;
    
    /**
     * Lingua in formato small di fallback
     * @var string{2}
     */
    private  $_fallback_lang       = null;
    
    /**
     * Lingua in formato esteso di fallback
     * @var String{5}
     */
    private  $_fallback_locale     = null;
    
    /**
     * Dominio di default delle stringhe
     * 
     * @var String
     */
    private  $_default_domain      = self::LANGUAGES_LOCALE_DEFAULT_DOMAIN;
    
    /**
     * Array contenente la configurazioni delle lingue disponibili dall'applicazione
     * @var Array
     */
    private  $_locale_available   = null;

    /**
     * Dominio delle stringhe
     * @var String
     */
    protected $_locale_domain          = LANGUAGES_LOCALE_DEFAULT_DOMAIN;
    
    /**
     * Path file locale globale costruito in cache qualora il sistema non sia un debug
     * 
     * @var String
     */
    protected $_locale_cache_file_path =  null;
    
    /**
     * Cataloghi di traduzione di tutta l'applicazione, ogni chiave è un locale e il valore associato è un catalogo di traduzioni (Application_LanguagesCatalogueData)
     * 
     * ArrayIterator (
     *      [LOCALE]  => Application_LanguagesCatalogueData
     * )
     * 
     * @var ArrayIterator
     */
    protected $_LANGUAGE_CATALOGUES          = null;    
    
    /**
     * Catalogo di traduzione in uso
     * 
     * @var Application_LanguagesCatalogueData
     */
    protected $_LANGUAGE_CATALOGUE           = null;
    
    /**
     * Catalogo di traduzione di fallback da utilizzare in alternativa 
     * 
     * @var Application_LanguagesCatalogueData
     */
    protected $_LANGUAGE_CATALOGUE_FALLBACK   = null;
    

    /**
     * Classe per la gestione delle lingue e dei locale interni del portale
     * 
     * @return Portal_Languages
     */
    public function __construct()
    {        
       return $this->_initLanguagesConfiguration();
    }
    
    /**
     * Restituisce il catalogo di traduzione ricercato
     * 
     * @param String $locale Locale
     * 
     * @return Application_LanguagesCatalogueData Catalogo di traduzione, FALSE se non è presente quello richiesto
     */
    public function getLanguageCatalogueData($locale)
    {
        if($this->_LANGUAGE_CATALOGUES->offsetExists($locale))
        {
            return $this->_LANGUAGE_CATALOGUES->offsetGet($locale);
        }
        
        return false;
    }
    
    /**
     * Restituisce il catalogo di traduzione per il locale attualmente in uso
     * 
     * @return Application_LanguagesCatalogueData Catalogo di traduzione, FALSE se non è presente quello richiesto
     */
    public function getLanguageCatalogueDataCurrent()
    {
        if($this->_LANGUAGE_CATALOGUES->offsetExists($this->_locale))
        {
            return $this->_LANGUAGE_CATALOGUES->offsetGet($this->_locale);
        }
        
        return false;
    }
    
    /**
     * Restituisce il catalogo attualmente utilizzato per il locale in uso di fallback
     * 
     * @param String $domain [OPZIONALE] dominio delle stringhe, default NULL 
     * 
     * @return Application_LanguagesCatalogueData
     */
    public function getLanguageCatalogueDataFallback()
    {
        return $this->_LANGUAGE_CATALOGUE_FALLBACK;
    }
    
    
    /**
     * Controlla che la lingua sia valida e disponibile
     * 
     * @param String{2} $lang Lingua da controllare in formato small
     * 
     * @return Boolean 
     */
    public function checkLanguage($lang)
    {
        if(is_array($this->_locale_available) && count($this->_locale_available) > 0)
        {
            if(in_array($lang,array_keys($this->_locale_available)))
            {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Controlla che il locale fornito sia valido, verificando che questo sia contenuto nell'array dei locales supportati dall'applicazione
     * 
     * @param String{5} $locale Locale in formato esteso, es: it_IT
     * 
     * @return Boolean
     */
    public function checkLocale($locale)
    {
        if(is_array($this->_locale_available) && count($this->_locale_available) > 0)
        {
           foreach($this->_locale_available as $lang => $localeInfo)
           {
              if($localeInfo["locale"] == $locale){
                 return true;
              }
           }
        }
        
        return false;
    }
    
    
    /**
     * Imposta un locale di default che verra consultato qualora la traduzione richiesta non sia trovata per l'attuale configurazione
     * 
     * @param String{5} $locale Locale
     * 
     * @return Portal_Languages
     */
    public function setDefaultLocaleFallback($locale)
    {          
       if($this->checkLocale($locale))
       {
          $this->_fallback_locale = $locale;
          $this->_fallback_lang   = $this->_getLanguageSmallByLocale($this->_fallback_locale);  //Imposto il lang di default
          
          $this->initCurrentApplicationCatalogue();
          
          return $this;
       }
       
       return self::throwNewException(4994949882222000033, 'Questo locale '.$locale.' non è presente tra quelli disponibili in LANGUAGES_LOCALE_AVAILABLE');
    }
    
    
    /**
     * Imposta il dominio in cui ricercare le strighe
     * 
     * @param string $domain Dominio
     * 
     * @return Application_Languages
     */
    public function setLocaleDomain($domain)
    {
       $this->_locale_domain = $domain;
       return $this;
    }
    
    
    /**
     * Restituisce il locale in uso
     * 
     * @return String {5}
     */
    public function getPortalLocale()
    {
        return $this->_locale;
    }
    
    
    /**
     * Restituisce la lingua in uso
     * 
     * @return String {2}
     */
    public function getPortalLanguage()
    {
        return $this->_lang;
    }

    /**
     * Restituisce il nome della lingua del locale
     * 
     * @param String{5} $locale Locale
     * 
     * @return String | false
     */
    public function getLanguageNameByLocale($locale)
    {
       $allLocales = self::getAllLocaleSupported(true);
       return  isset($allLocales[$locale]) ? $allLocales[$locale]["name"] : false;
    }
    
    /**
     * Restituisce il nome della lingua della lingua small
     * 
     * @param String{2} $lang Lingua small
     * 
     * @return String | false
     */
    public function getLanguageNameByLang($lang)
    {
       $allLocales = self::getAllLocaleSupported(true);
       
       foreach($allLocales as $locale=>$info)
       {
          if($info["lang"] == $lang ){
             return $info["name"];
          }
       }
       
       return false;
    }
    
    
    /**
     * Restituisce il locale in formato esteso partendo dalla lingua
     * 
     * @param String $lang Locale {2}
     * 
     * @return String {5}
     */
    public function getLocaleByLanguage($lang)
    {
       return $this->_getLocaleByLanguageSmall($lang);
    }
    
    /**
     * Restituisce la lingua in formato small dal locale indicato
     * 
     * @param String $locale Locale {5}
     * 
     * @return String
     */
    public function getLanguageSmallByLocale($locale)
    {
        return $this->_getLanguageSmallByLocale($locale);
    }
    
        
    /**
     * Restituisce la lista dei locales Supportati. 
     * 
     * @param Boolean $getAllInfo [OPZIONALE] Indica se restituire un array associativo di locale => Array Info oppure una lista di locale, default FALSE
     * 
     * @return Array
     */
    public function getAllLocaleSupported($getAllInfo = false)
    {
       $retArray = Array();
       
       foreach($this->_locale_available as $lang => $localeInfo)
       {
          if($getAllInfo){
             $retArray[$localeInfo["locale"]] = Array("lang"=>$lang,"name"=>$localeInfo["name"]);
          }else{
             $retArray[]  = $localeInfo["locale"];
          }
       }
       
       return $retArray;
    }
    
    
    /**
     * Restitusce la lista delle lingue in formato small supportate
     * 
     * @return Array
     */
    public  function getAllLangsSupported()
    {
       return array_keys($this->_locale_available);
    }
 
    /**
     * Restituisce il locale di fallback
     * 
     * @return String{5} | Boolean 
     */
    public function getFallbackLocale()
    {
       return $this->_fallback_locale;
    }
    
    
    /**
     * Restituisce il language di fallback
     * 
     * @return String{2} 
     */
    public function getFallbackLanguage()
    {       
       return $this->_fallback_lang;
    }
    
    /**
     * Restituisce il dominio di default
     * 
     * @return String
     */
    public function getDefaultDomain()
    {
       return $this->_default_domain;
    }
    
    
    /**
     * Imposta il dominio di default
     * 
     * @param String $domain Dominio delle stringhe
     * 
     * @return \Application_Languages
     * 
     */
    public function setDefaultDomain($domain)
    {
       $this->_default_domain = $domain;
       return $this;
    }
    
    
    /**
     * Imposta il locale da utilizzare
     * 
     * @param String{5} $locale Locale
     * 
     * @return \Application_Languages
     */
    public function changeLocale($locale)
    {
        if($this->checkLocale($locale))
        {
            $lang = $this->_getLanguageSmallByLocale($locale);
            $this->setPortalLanguage($lang);
        }
        
        return $this;
    }
    
    
    /**
     * Cambia lingua in uso
     * 
     * @param String{2} $lang Lingua in formato small
     * 
     * @return Portal_Languages o FALSE in caso di errore
    */
    public function changeLanguage($lang)
    {
        if($this->checkLanguage($lang))
        {
           return $this->setPortalLanguage($lang);
        }
        
        return false;
    }
    
    /**
     * Imposta il catalogo delle traduzioni per il locale a cui il catalogo fa riferimento
     * 
     * @param Application_LanguagesCatalogueData $languageCatalogue Catalogo
     * 
     * @return \Application_Languages
     */
    public function setApplicationCatalogueData(Application_LanguagesCatalogueData $languageCatalogue)
    {
        $this->_LANGUAGE_CATALOGUES->offsetSet($languageCatalogue->getLocale(), $languageCatalogue);
        return $this;
    }
    
    /**
     * Restituisce il catalogo delle lingue per il locale specificato
     * 
     * @param String $locale{5} Locale
     * 
     * @return Application_LanguagesCatalogueData   Catalogo delle traduzioni, FALSE se non esiste
     */
    public function getApplicationCatalogue($locale)
    {
        if(strlen($locale) == 0)
        {
            return false;
        }
        
        if($this->_LANGUAGE_CATALOGUES->offsetExists($locale))
        {
            return $this->_LANGUAGE_CATALOGUES->offsetGet($locale);
        }
        
        return false;
    }
    
    
    /**
     * Unisce il catalogo delle traduzioni a quelli già presenti nel manager
     * 
     * @param Application_LanguagesCatalogueData $languageCatalogue Catalogo
     * 
     * @return Application_Languages
     */
    public function addApplicationCatalogueData(Application_LanguagesCatalogueData $languageCatalogue)
    {
        $currentLanguageCatalogue = $this->getApplicationCatalogue($languageCatalogue->getLocale());
        
        if($currentLanguageCatalogue instanceof Application_LanguagesCatalogueData)
        {
            $currentLanguageCatalogue->mergeLanguageCatalogue($languageCatalogue);
            $this->setApplicationCatalogueData($currentLanguageCatalogue);
        }
        else
        {
            $this->setApplicationCatalogueData($languageCatalogue);
        }
                
        return $this;
    }
    
    
    /**
     * Traduce una stringa nella lingua selezionata del portale
     * 
     * Questo metodo accetta l'inversine dei parametri $domain e $replacement
     * 
     * @param String   $code                    Chiave parola da tradurre
     * @param Mixed    $domain / $replacement   [OPZIONALE] Dominio delle stringhe, se NULL applica quello di default self::LANGUAGES_LOCALE_DEFAULT_DOMAIN
     * @param Mixed    $replacement / $domain   [OPZIONALE] Parametri per effettuare le sostituizioni nella stringa, verrà effettuato sia la formattazione che la sostituzione di stringhe, es: ["string1","string2","STRINGA"=>"valore"]
     * @param Boolean  $returnFallbackValue     [OPZIONALE] Indica se restiure il valore di fallback  qualora non fosse presente nel catalogo della lingua in uso, default TRUE
     * 
     * @return String
     */
    public function translate($code,$domain = null,$replacement = array(),$returnFallbackValue = false)
    {   
        if(is_array($domain))
        {
            $replacement = $domain;
            $domain      = null;
        }

        if(is_string($replacement)) 
        {
            $domain = $replacement;
        }

        $translateParameters = Array(
            "code"                => $code,
            "domain"              => $domain,
            "replacement"         => $replacement,
            "returnFallbackValue" => $returnFallbackValue
        );
        
        $translateParameters = $this->getApplicationKernel()->processHooks(Application_Hooks::HOOK_TYPE_LOCALE_TRANSLATE_BEFORE,$translateParameters)->getData();

        $code                 = $translateParameters["code"];
        $domain               = $translateParameters["domain"];
        $replacement          = $translateParameters["replacement"];
        $returnFallbackValue  = $translateParameters["returnFallbackValue"];
        
        if(is_null($domain))
        {
           $domain = $this->_default_domain;
        }
        
        if($this->getApplicationKernel()->getApplicationHooks()->hasHooksTypeRegistered(Application_Hooks::HOOK_TYPE_LOCALE_TRANSLATE_TRANS))
        {
            $translatedString = $this->getApplicationKernel()->processHooks(Application_Hooks::HOOK_TYPE_LOCALE_TRANSLATE_TRANS,$translateParameters)->getData();            
        }
        else
        {
            $translatedString =  $this->_findTranslateInLocaleCatalogues($code,$domain,$returnFallbackValue);

            if($translatedString !== false)
            {
               $translatedString =  $this->_formatString($translatedString,$replacement);           
            }
        }
        
        if($this->getApplicationKernel()->getApplicationHooks()->hasHooksTypeRegistered(Application_Hooks::HOOK_TYPE_LOCALE_TRANSLATE_AFTER))
        {
            $translateParameters["translatedString"] = $translatedString;
            $translateParameters                     = $this->getApplicationKernel()->processHooks(Application_Hooks::HOOK_TYPE_LOCALE_TRANSLATE_AFTER,$translateParameters)->getData();            
            $translatedString                        = $translateParameters["translatedString"];
        }
        
        if($translatedString !== false)
        {
            return $translatedString;
        }
        
        return $code;
    }
    
    
    /**
     * Restituisce la lingua in formato small partendo dal locale
     * 
     * @param String $locale Locale {5}
     * 
     * @return String {2}
     */
    private function _getLanguageSmallByLocale($locale)
    {       
       foreach($this->_locale_available as $lang=>$localeInfo)
       {
          if($localeInfo["locale"] == $locale){
             return $lang;
          }
       }  
       
       return self::throwNewException(9394940032227711223, 'Questo locale non esiste: '.$locale);
    }
    
    /**
     * Restituisce il locale in formato esteso partendo dalla lingua small
     * 
     * @param String $lang Locale {2}
     * 
     * @return String {5}
     */
    private  function _getLocaleByLanguageSmall($lang)
    {       
       foreach($this->_locale_available as $langKey => $localeInfo)
       {
          if($lang == $langKey)
          {
             return $localeInfo["locale"];
          }
       }
       
       return self::throwNewException(23948209348671374810349, 'Questa lingua non esiste: '.$lang);
    }
    
    /**
     * Imposta una lingua ed il locale associato
     * 
     * @param String $lang {2}
     * 
     * @return Portal_Languages 
     */
    private function setPortalLanguage($lang)
    {
        $this->_lang   = $lang;
        $this->_locale = $this->_getLocaleByLanguageSmall($lang);
        
        $this->initCurrentApplicationCatalogue();
        
        setlocale(LC_ALL,$this->_locale);

        return $this;
    }
    
    
    /**
     * Determina la lingua supportata dal client
     * 
     * @return String {2}
     */
    public function detectLanguage()
    {
        $httpAcceptLanguage  = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : "";
        
        $lang                = $this->_fallback_lang;
        
        $lang_info           = explode(';', $httpAcceptLanguage);
        
        if(is_array($lang_info) && count($lang_info)>0)
        {
            $languages = explode(',', $lang_info[0]);
            $lang      = $languages[0];
        }
        
        if(preg_match("/([a-z]{2})\-([a-z]{2})/",$lang,$matches))
        {
           $lang = $matches[1];
        }
                
        if(!$this->checkLanguage($lang))
        {
           $lang = $this->_fallback_lang;
        }
        
        
        return $lang;
    }   

    
    /**
     * Esporta il Locale desiderato in formato Array / Json per il catalogo attualmente gestito
     * 
     * @param String   $locale  Locale da esportare
     * @param String   $domain  [OPZIONALE] Dominio di default delle stringhe, default <LANGUAGES_LOCALE_DEFAULT_DOMAIN>
     * @param Boolean  $json    [OPZIONALE] Indica se restituirlo in formato JSON, default TRUE
     * 
     * @return Mixed
     * 
     * @throws \Exception se il locale specificato non esiste
     */
    public function exportLocale($locale,$domain = null,$json = false)
    {
       if($this->checkLocale($locale))
       {
            if(is_null($domain))
            {
               $domain = $this->_default_domain;
            }            
            
            $languageCatalogue = $this->_LANGUAGE_CATALOGUE;
            
            if($languageCatalogue instanceof Application_LanguagesCatalogueData)
            {
                if($languageCatalogue->isDomainExists($domain))
                {
                   $translationsCatalogue = $languageCatalogue->getAllTranslations($domain);

                   if($json)
                   {
                      $translationsCatalogue  =  json_encode($translationsCatalogue);
                   }

                   return $translationsCatalogue;
                }
            }
            
            return self::throwNewException('Non è possibile trovare il dominio '.$domain.' per il locale specificato '.$locale, 1231424534534134);
       } 
       
       return self::throwNewException('Non è possibile trovare il locale specificato '.$locale, 435359089304923);
    }
    
    /**
     * Inizializza il catalogo corrente e quello di fallback, dalla lista di tutti i cataloghi caricati nel momento in cui si imposta la lingua/locale
     * 
     * @return \Application_Languages
     */
    private function initCurrentApplicationCatalogue()
    {
        $languageCatalogue = $this->getApplicationCatalogue($this->_locale);
        
        if($languageCatalogue instanceof \Application_LanguagesCatalogueData)
        {
            $this->_LANGUAGE_CATALOGUE = $languageCatalogue;
        }
        
        $languageCatalogueFallback = $this->getApplicationCatalogue($this->_fallback_locale);
        
        if($languageCatalogueFallback instanceof \Application_LanguagesCatalogueData)
        {
            $this->_LANGUAGE_CATALOGUE_FALLBACK = $languageCatalogueFallback;
        }
        
        return $this;
    }
    
    
    /**
     * Carica un locale presenti nella directory specificata, restituendo l'iterator del catalogo caricato
     * 
     * @param String  $localePath      Path assoluto del locale
     * @param Boolean $force           [OPZIONALE] Indica se forzare il caricamento, default FALSE
     * 
     * @return Portal_Languages
     */
    public function loadLocale($localePath,$force = false)
    {       
       $languageCatalogue =  $this->_createLanguageCatalogue($localePath,$force);        
       $this->addApplicationCatalogueData($languageCatalogue);
       
       return $this;
    }
    
    
    /**
     * Carica tutti i locales presenti nella directory specificata restituendo un ArrayIterator in cui ogni indice è il file (dominio delle stringhe) ed ogni elemento è un appendIterator (il catalogo) delle traduzioni creato
     * 
     * @param String $localesPathDirectory Path assoluto della directory in cui sono presenti i locale da caricare, anche multidominio, es: ../locale/it_IT
     * @param Boolean $force               [OPZIONALE] Indica se forzare il caricamento, default FALSE
     * 
     * @return ArrayIterator
     */
    public function loadAllLocales($localesPathDirectory,$force = false)
    {
       $allLocalesFiles = $this->getUtility()->File_getFilesInDirectory($localesPathDirectory);

       foreach($allLocalesFiles as $localeFile)
       {
          $localePath      = $localesPathDirectory. "/" .$localeFile;
          $this->loadLocale($localePath,$force);
       }
       
       return $this;
    }
    
    /**
     * Carica tutti i locale presenti nel package specificato
     * 
     * @param Abstract_Package $package Instanza del package
     * @param Boolean               $force        [OPZIONALE] Indica se forzare la cache, default FALSE
     * 
     * @return Application_Languages
     */
    public function loadAllLocalesForPackage(Abstract_Package $package,$force = false)
    {   
        $allLanguages     = $this->getAllLocaleSupported();

        if(is_array($allLanguages) && count($allLanguages) > 0)
        {
            foreach($allLanguages as $locale)
            {
                $localesPath      = $package->getLocalesPath() . "/" .$locale;
                $this->loadAllLocales($localesPath, $force);
            }
        }
        
        return $this;
    }
    
    
    /**
     * Elimina tutti i cataloghi cachati
     * 
     * @return Boolean
     */
    public function flushCachedLanguageCatalogoues()
    {
        $appConfigs = $this->getApplicationConfigs();
        
        $currentCacheDirectory = $appConfigs->getConfigsCacheDirPath();
        $newCacheDirectory     = $currentCacheDirectory.'/locales';
     
        $appConfigs->setConfigsCacheDirPath($newCacheDirectory);
        return $this->getUtility()->Directory_ClearAllFiles($newCacheDirectory);
    }
    
    
    /**
     * Unisce il catalogo di traduzione a quello già presente per il locale a cui il catalogo è legato
     * 
     * @param Application_LanguagesCatalogueData $languageCatalogue Catalogo
     * 
     * @return Application_Languages
     */
    private function _mergeApplicationCatalogue(Application_LanguagesCatalogueData $languageCatalogue)
    {
        $currentApplicationCatalogue = $this->getApplicationCatalogue($languageCatalogue->getLocale());
    }
    
    /**
     * Traduce la stringa indicata ricercandola nei cataloghi delle traduzioni caricati
     * 
     * @param String  $code                  Codice Stringa da tradurre
     * @param String  $domain                [OPZIONALE] Dominio di ricerca, default self::LANGUAGES_LOCALE_DEFAULT_DOMAIN
     * @param Boolean $returnFallbackValue   [OPZIONALE] Indica se restiure il valore di fallback  qualora non fosse presente nel catalogo della lingua in uso, default TRUE
     * @param Boolean $defaultValue          [OPZIONALE] Indica il valore restituito se code non è presente in nessuno dei cataloghi, ne in quello in uso, ne in quello di fallback. Di default viene restituito il $code, se specificato questo valore sarò restituito
     * 
     * @return String
     */
    private function _findTranslateInLocaleCatalogues($code, $domain = self::LANGUAGES_LOCALE_DEFAULT_DOMAIN, $returnFallbackValue = true,$defaultValue = false)
    {     
       $languageCatalogue = $this->_LANGUAGE_CATALOGUE; 
       
       if($languageCatalogue instanceof Application_LanguagesCatalogueData)
       {
            if(!$languageCatalogue->isEmpty())
            {
                 if($languageCatalogue->isDomainExists($domain))
                 {
                    $translation = $languageCatalogue->getValue($code,$domain);   /*@var $translations ArrayIterator*/

                    if($translation)
                    {
                       return $translation;
                    }
                 }
            }
       }
       
       
       if($returnFallbackValue)
       {
           $fallbackLanguageCatalogue = $this->_LANGUAGE_CATALOGUE_FALLBACK;
           
           if($fallbackLanguageCatalogue instanceof Application_LanguagesCatalogueData)
           {
                if(!$fallbackLanguageCatalogue->isEmpty())
                {
                     if($fallbackLanguageCatalogue->isDomainExists($domain))
                     {
                         $translation = $fallbackLanguageCatalogue->getValue($code, $domain);

                         if($translation!==false)
                         {
                            return $translation;
                         }
                     }
                }
           }
       }
            
       if(!$defaultValue)
       {
          return $code;
       }
       
       return $defaultValue;
    }
    
    /**
     * Formatta una stringa
     * 
     * @param String $string      Stringa da formattare
     * @param array  $replacement Array contenente i valori della formattazione / parametri di replace
     * 
     * @return String
     */
    private function _formatString($string,array $replacement = array())
    {
        $arguments = Array();

        if(is_array($replacement) && count($replacement) > 0)
        {
           foreach($replacement as $key => $value)
           {
              if(is_int($key))
              {
                 $arguments[] = $value;
              }
              else if(is_string($key) && strstr($string,'$'.$key) !== false)
              {
                 $string = str_replace('$'.$key,$value,$string); 
              }
              else if(is_string($key) && strstr($string,'{{'.$key.'}}') !== false)
              {
                 $string = str_replace('{{'.$key.'}}',$value,$string); 
              }
              else if(is_string($key) && strstr($string,'{'.$key.'}') !== false)
              {
                 $string = str_replace('{'.$key.'}',$value,$string); 
              }
              else if(is_string($key) && strstr($string,'#'.$key.'#') !== false)
              {
                 $string = str_replace('#'.$key.'#',$value,$string); 
              }
              else if(is_string($key) && strstr($string, $key)!==false)
              {
                 $string = str_replace($key,$value,$string);
              }
              
           }

           if(is_array($arguments) && count($arguments) > 0)
           {
              $string = vsprintf($string, $arguments);
           }
        }

        return $string;
    }
    
    
    /**
     * Inizializza l'instanza caricando il catalogo delle traduzioni in base alla configurazione del browser
     * 
     * @return Portal_Languages
     */
    private function _initLanguagesConfiguration()
    {
       if(defined("LANGUAGES_LOCALE_AVAILABLE"))
       {
          if(is_null($this->_locale_available))
          {
             /**
              * Inizializzo la configurazione
              */
             $this->_locale_available   = unserialize(LANGUAGES_LOCALE_AVAILABLE);
             
             $this->_LANGUAGE_CATALOGUE           = null;
             
             $this->_LANGUAGE_CATALOGUE_FALLBACK  = null;
       
             $this->_LANGUAGE_CATALOGUES          = new ArrayIterator();
                          
             /**
              * Inizializzo il locale e la lingua di fallback dell'applicazione
              */
             $this->_initFallbackLocaleLanguage();
             
             /**
              * Imposto la lingua del portale ed il relativo locale associato
              */
             $this->setPortalLanguage($this->detectLanguage()); 
             
             /**
              * Imposta il dominio di default delle stringhe
              */
             $this->setDefaultDomain(self::LANGUAGES_LOCALE_DEFAULT_DOMAIN);
             
             /**
              * Inizializzo i cataloghi di traduzioni del locale di default e di quello impostato
              */
             $this->_loadDefaultLocaleCatalogues();
             
             /**
              * Inizializzo il catalogo di default
              */
             $this->initCurrentApplicationCatalogue();
          }
          
          return $this;
       }
       
       return self::throwNewException(994483849969848289, 'La classe '.__CLASS__.' richiede la costante di configurazione LANGUAGES_LOCALE_AVAILABLE');
    }
    
    /**
     * Inizializza un locale di fallback, ricercando tra quelli disponibili quale è stato impostato come default
     * 
     * @return Portal_Languages
     */
    private function _initFallbackLocaleLanguage()
    {
       if(is_array($this->_locale_available) && count($this->_locale_available) > 0)
       {
         /**
          * Ricerco il locale di default in base alla configurazione
          */
          foreach($this->_locale_available as $lang => $localeInfo)
          {
             if(isset($localeInfo["default"]) && $localeInfo["default"])
             {
                return $this->setDefaultLocaleFallback($localeInfo["locale"]);   //Imposto un locale di fallback per le traduzioni
             }
          }
          
          return self::throwNewException(11112213134433, 'Non è possibile trovare nessun locale di default, è neccessario configurarne uno tra quelli presenti in LANGUAGES_LOCALE_AVAILABLE');
       }
         
       return self::throwNewException(344884737282940000, 'Non è possibile impostare un locale di fallback poichè non è stato ancora inizializzato questo oggetto!');
    }
 
    /**
     * Inizializza ed eventualualmente crea i cataloghi dei locale di defautl nelle cartelle principili dell'applicazione
     * 
     * @return Portal_Languages
     */
    private function _loadDefaultLocaleCatalogues()
    {
       $this->loadAllLocales(self::LANGUAGES_LOCALE_DEFAULT_PATH . "/" .$this->_locale);
       $this->loadAllLocales(self::LANGUAGES_LOCALE_DEFAULT_PATH . "/" . $this->_fallback_locale);
       
       return $this;
    }
    
    /**
     * Restituisce tutte le informazioni tecniche del locale, utilizzando le informazioni di pathinfo()
     * <br>
     * <br>
     * L'array restituito avrà le seguenti informazioni:
     * <br>
     * <ul>
     *    <li>locale:     Locale in formato esteso: {5}</li>
     *    <li>lang:       Lingua in formato small{2}</li>
     *    <li>extension:  Estenzione del file</li>
     *    <li>domain:     Dominio di appartenenza delle stringhe</li>
     * </ul>
     * @param String $localePath Path assoluto del file di locale, es: /usr/bin/php/locales/it_IT/message.php
     * 
     * @throws Exception_PortalErrorException
     * 
     * @return Array 
     */
    private function _getLocaleInfo($localePath)
    {
       
       if(!file_exists($localePath)){
          return self::throwNewException(1029839248294, 'Non è possibile usare questo locale '.$localePath.' poichè il file non esiste!');
       }
       
       $domain      = pathinfo($localePath,PATHINFO_FILENAME);
       $extension   = pathinfo($localePath,PATHINFO_EXTENSION);
       $locale      = basename(pathinfo($localePath,PATHINFO_DIRNAME));
       $lang        = $this->_getLanguageSmallByLocale($locale);
       
       
       if(!$this->checkLocale($locale)){
          return self::throwNewException(1029839248294, 'Non è possibile usare questo locale '.$localePath.', il locale "'.$locale.'" non è valido!');
       }
       else if(!$this->checkLanguage($lang)){
          return self::throwNewException(1029839248294, 'Non è possibile usare questo locale '.$localePath.', la lingua "'.$lang.'" non è valida!');
       }
       else if(strlen($domain) == 0){
          return self::throwNewException(9218391823904, 'Non è possibile determinare il dominio per il file di locale: '.$localePath);
       }
       
       return Array("locale"      => $locale,
                    "lang"        => $lang,
                    "extension"   => $extension,
                    "domain"      => $domain,
                    "path"        => $localePath
       );
    }
    
    
    /**
     * Crea un catalogo di traduzioni
     * 
     * @param String   $localePath  Path assoluto del catalogo
     * @param Boolean  $force       [OPZIONALE] Indica se bypassare la cache, default FALSE
     * 
     * @return Application_LanguagesCatalogData
     */
    private function _createLanguageCatalogue($localePath,$force = false)
    {  
       $configFileName = sprintf(self::APPLICATION_LANGUAGE_CATALOGUE_FILE_NAME,md5($localePath));

       $configCacheDirectory            = $this->getApplicationConfigs()->getConfigsCacheDirPath();
       $configCacheDirectoryLocale      = $configCacheDirectory.'/locales';

       /**
        * Ricerco il catalogo delle traduzioni in cache
        */
       if(!$force && !$this->getKernelDebugActive())
       {
            $this->getApplicationConfigs()->setConfigsCacheDirPath($configCacheDirectoryLocale);

            if($this->getApplicationConfigs()->isConfigsCached($configFileName))
            {
                $languageCatalogue =  $this->getApplicationConfigs()->getConfigsFromCache($configFileName);
                $this->getApplicationConfigs()->setConfigsCacheDirPath($configCacheDirectory);
                return $languageCatalogue;
            }
       }
       
       $localeInfo = $this->_getLocaleInfo($localePath);
       
       $localeCatalogue     = false;
       
       $domain              = $localeInfo["domain"];
       $locale              = $localeInfo["locale"];
       $extension           = $localeInfo["extension"];
       
       switch($extension)
       {
          /**
           * File di traduzione PHP
           */ 
          case Application_Configs::CONFIGS_FILE_EXTENSION_PHP: 
            
                                                $localeArray     = include $localePath;

                                                if(!is_array($localeArray))
                                                {
                                                   return self::throwNewException(91823716231763228, 'Questo file '.$localePath.' deve restituire un array chiave => valore di traduzioni ');
                                                }
                                                
                                                $localeCatalogue = new ArrayIterator($localeArray);

                                             break;
          /**
           * File di traduzioni YAML
           */                                            
          case Application_Configs::CONFIGS_FILE_EXTENSION_YAML:     
                                                   
                                                $this->getApplicationPlugins()->includePlugin(Application_Configs::YAML_PLUGIN_NAME);
            
                                                if(!function_exists("yaml_load_file"))
                                                {
                                                   return self::throwNewException(388337738939283234, 'Questo formato file '.$extension.' necessita della function yaml_load_file');
                                                }

                                                $localeArray     = yaml_load_file($localePath);
                                                $localeCatalogue = new ArrayIterator($localeArray);
                                                         
                                             break;
                                             
         /**
          * Non conosco questo formato, invoco gli hooks Application_Hooks::HOOK_TYPE_LOCALE_LOAD
          * 
          * Se vi sono degli Hooks registrati saranno incaricati di interpretare il file di locale non conosciuto 
          * e di restiture un ArrayCollection contenente tutte le traduzioni del file di locale elaborato
          * 
          */                                    
         default:
                                             $localeCatalogue = $this->getApplicationKernel()->processHooks(Application_Hooks::HOOK_TYPE_LOCALE_LOAD,$localeInfo)->getData();
                                                
                                             break;
             
       }
       
       if(!($localeCatalogue instanceof ArrayIterator))
       {
          return self::throwNewException(448483839482,'Non è possibile creare il catalogo di traduzione per il locale '.$locale.' in '.$localePath);
       }
       
       $languageCatalogue =  new Application_LanguagesCatalogueData($locale,$domain,$localeCatalogue->getArrayCopy());
       
       /**
        * Storo il catalogo delle configurazioni su file, se non sono in debug e non voglio forzare il caching
        */
       if(!$force && !$this->getKernelDebugActive())
       {
          $this->getApplicationConfigs()->setConfigsCacheDirPath($configCacheDirectoryLocale)->storeConfigsCache($configFileName, $languageCatalogue);
          $this->getApplicationConfigs()->setConfigsCacheDirPath($configCacheDirectory);
       }
       
       return $languageCatalogue;
    }
        
}