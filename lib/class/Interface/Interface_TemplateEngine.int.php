<?php

/**
 * Interfaccia per i template Engine supportati dal portale
 * Ongni tpl engine creato dovrà utilizzare questi metodi
 */
interface Interface_TemplateEngine
{
    /**
     * Default scadenza file caching del template
     */
    const     DEFAULT_CACHE_EXPIRE                = 86400;
    
    /**
     * Caching type template 
     */
    const     CACHING_TYPE_TEMPLATES              = 'template';
    
    /**
     * Caching type template html elaborati
     */
    const     CACHING_TYPE_TEMPLATES_COMPILED     = 'template_c';
    
    /**
     * Caching type tutte le tipologie
     */
    const     CACHING_TYPE_TEMPLATES_ALL          = 'template_all';
    
    
    /**
     * Effettua la configurazione necessaria al template Engine prima del drawTemplate()!
     * @return Boolean
     */
    public function configureTplEngine();

    /**
     * Effettua la compilazione del template
     * @return Boolean
     */
    public function drawTemplate();
    
    
    /**
     * Effettua la compilazione di una stringa e restituisce il codice compilato HTML
     * @param String $string
     * @return String
     */
    public function drawString($string,array $parameters = array());

    /**
     * Pulisce la cache dai template con un expire date scaduto
     * @return Boolean
     */
    public function clearCache();

    /**
     * Visualizza il template elaborato oppure restituisce l'output come stringa
     * @param String $getoutput Indica se ricevere l'output del template compilato oppure lo elabora direttamente
     */
     public function view();
}

