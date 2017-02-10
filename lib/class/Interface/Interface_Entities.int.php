<?php

/**
 * Interfaccia per le entità. Ogni entità dovrà avere questi metodi.
 */
interface Interface_Entities
{   
    /**
     * Ottiene l'array associativo per l'oggetto entitità specifico
     */
    function toArray();
    
    /**
     * Ottiene la stringa serialized per l'oggetto entitità specifico
     */
    function toString();
}

