<?php


/**
 * Catalogo di  traduzioni dei domini per un locale utilizzato dall'Application_Languages. 
 */
class Application_LanguagesCatalogueData
{    
    /**
     * Contiene tutto il catalogo
     * 
     * Ogni chiave è un dominio, e per ogni dominio vi sono le traduzioni (chiave => valore)
     * 
     * Array(
     *       [DOMINIO] => Array(
     *                           <chiave1> => "Traduzione1"
     *                           <chiave2> => "Traduzione2"
     *                    )
     * )
     * 
     * 
     * @var Array
     */
    protected $_languageCatalogueData  = Array();
    

    /**
     * Lista di tutti i domini presenti nel catalogo
     * 
     * @var Array
     */
    protected $_domains = Array();
    
    
    /**
     * Locale di riferimento
     * 
     * @var String
     */
    protected $_locale  = null;

    /**
     * Restituisce la lista di domini presenti in questo catalogo
     * 
     * @return Array
     */
    public function getAllDomains()
    {
        return $this->_domains;
    }
    
    /**
     * Verifica che il catalogo abbia il dominio specificato
     * 
     * @param String $domain Dominio
     * 
     * @return Boolean
     */
    public function isDomainExists($domain)
    {
        return in_array($domain,$this->_domains);
    }
    
    /**
     * Indica se il catalogo è vuoto
     * 
     * @return Boolean
     */
    public function isEmpty()
    {
        return empty($this->_languageCatalogueData);
    }
    
    /**
     * Restituisce tutte le traduzioni per un dominio specifico o per tutti
     * 
     * @param String $domain Dominio, default NULL tutti
     * 
     * @return Array  Traduzioni, FALSE se il dominio non esiste nel catalogo
     */
    public function getAllTranslations($domain = null)
    {
        if(is_null($domain))
        {
            return $this->_languageCatalogueData;
        }
        
        if(isset($this->_languageCatalogueData[$domain]))
        {
            return $this->_languageCatalogueData[$domain];
        }
        
        return false;
    }
    
    
    /**
     * Imposta il locale 
     * 
     * @param String $locale  Locale
     * 
     * @return \Application_LanguagesCatalogData
     */
    public function setLocale($locale)
    {
        $this->_locale = $locale;
        return $this;
    }
    
    /**
     * Restituisce il locale di riferimento per questo catalogo
     * 
     * @return String
     */
    public function getLocale()
    {
        return $this->_locale;
    }
    
    
    /**
     * Restituisce il valore dell'elemento del catalogo 
     * 
     * @param String $code      id stringa
     * @param String $domain    Dominio, default NULL (ricerca in tutto il catalogo
     * 
     * @return String
     */
    public function getValue($code, $domain = null)
    {
        if($this->isEmpty())
        {
            return false;
        }
        
        foreach($this->_languageCatalogueData as $currentDomain => $translations)
        {
            if(is_null($domain) || $domain == $currentDomain)
            {
                $translations = $this->getAllTranslations($currentDomain);
                
                if(isset($translations[$code]))
                {
                    return $translations[$code];
                }
            }
        }
        
        return false;
    }
    
    
    /**
     * Catalogo di  traduzioni dei domini per un locale utilizzato dall'Application_Languages. 
     * 
     * @param String $locale         Locale utilizzato per il catalogo
     * @param String $domain         Dominio utilizzato per le traduzioni
     * @param Array  $catalogData    [OPZIONALE] Array contente le traduzioni
     * 
     * @return Boolean
     */
    public function __construct($locale,$domain,array $catalogData = array())
    {
        $this->setLocale($locale);
        $this->buildCatalog($domain,$catalogData);
        
        return true;
    }
        
    
    /**
     * Aggiunge al dominio le traduzioni specificate
     * 
     * @param String   $domain         Dominio
     * @param Array    $translations   [OPZIONALE] Lista delle traduzioni
     * 
     * @return Application_LanguagesCatalogData
     */
    public function addTranslations($domain,array $translations = array())
    {        
        $this->buildCatalog($domain,$translations);
        
        return $this;
    }
    
    
    /**
     * Unisce i due cataloghi di traduzioni per un locale specifico
     * 
     * @param Application_LanguagesCatalogData $languageCatalogue
     * 
     * @return \Application_LanguagesCatalogData
     * 
     * @throws \Exception  Se i locale sono diversi tra il calogo attuale e quello indicato
     */
    public function mergeLanguageCatalogue(Application_LanguagesCatalogueData $languageCatalogue)
    {
       if($this->getLocale() != $languageCatalogue->getLocale())
       {
           return $this->throwNewException(2348208458394593459, 'Non è possibile unire i due cataloghi di traduzione poichè appartengono a due locale differenti');
       }
        
       if(!$languageCatalogue->isEmpty())
       {
          $catalogDomains = $languageCatalogue->getAllDomains();
       
          foreach($catalogDomains as $domain)
          {
             $translations = $languageCatalogue->getAllTranslations($domain);
             $this->buildCatalog($domain,$translations);
          }
       }
       
       return $this;
    }
    
    /**
     * Aggiunge al dominio le traduzioni specificate
     * 
     * @param String   $domain         Dominio
     * @param Array    $translations   [OPZIONALE] Lista delle traduzioni
     * 
     * @return Application_LanguagesCatalogData
     */
    private function buildCatalog($domain,array $translations = array())
    {
        if(count($translations) > 0)
        {
            $currentDomainTranslations = isset($this->_languageCatalogueData[$domain]) ? $this->_languageCatalogueData[$domain] : Array();
            $domainTranslations        = array_merge($currentDomainTranslations,$translations);
            
            $this->_languageCatalogueData[$domain] = $domainTranslations;

            if(!in_array($domain, $this->_domains))
            {
                $this->_domains[] = $domain;
            }
        }
        
        return $this;
    }
}