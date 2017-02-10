<?php

/**
 * Action Object Basilare invocato se non ne esiste uno per l'action elaborata.
 *
 * Lo scopo di questo oggetto è di rendere un singolo template invocabile mediante una rotta senza che esista un relativo actionObject. Il nome del template dovrà corrispondere
 * con il nome del template caricato nella cartella delle risorse del package specificato dalla rotta
 * 
 */
class Basic_ActionObject extends Abstract_ActionObject
{
  
    /**
     * Restituisco di default il package attulamente gestito dal routing, FALSE altrimenti
     * 
     * @return String | False se package è inesistente
     */
    public static function getPackage() 
    {
       return self::getApplicationRoutingCurrentRouteData()->getPackage();
    }
  
    
    public function getPackageInstance($default = null)
    {
        try
        {
            return $this->getApplicationKernel()->getPackageInstance(self::getPackage()) ?: $default;
        }
        catch(\Exception $e)
        {
            return $default;
        }
        
        return $default;
    }
}
