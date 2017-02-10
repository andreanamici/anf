<?php

require_once 'Trait_Utilities.trait.php';

/**
 * Questo Trait si occupa di fornire i metodi comuni agli oggetti/controllers/Entità del portale
 * <br>
 * Questo trait include anche:
 * <ul>
 *    <li>Trait_Exception</li>
 *    <li>Trait_LogsManager</li>
 * </ul>
 */
trait Trait_ObjectUtilities
{
    
    use Trait_Exception,
            
        Trait_ApplicationLogsManager,
            
        Trait_Utilities;
    

   /**
     * Imposta la propriertà statica dell'oggetto sul quale è invocato
     * 
     * @param String $propertyName Nome della proprietà,es: $_http_root => ::getStatic('http_root');
     * 
     * @param Mixed  $value        Valore della proprietà
     * 
     * @return Boolean
     */
    public static function setStaticProperty($propertyName,$value)
    {
       $property =  strstr($propertyName,'$_') ? str_replace('$','',$propertyName) : '_'.$propertyName;
       return static::$$property = $value;
    }
    
    /**
     * Restituisce la proprietà statica dell'oggetto sul quale è invocato
     * 
     * @param String $propertyName Nome della proprietà,es: $_http_root => ::getStatic('http_root'), ::getStatic('$_http_root')
     * 
     * @return Mixed
     */
    public function getStaticProperty($propertyName)
    {
       $property =  strstr($propertyName,'$_') ? str_replace('$','',$propertyName) : '_'.$propertyName;
       
       if(isset(static::$$property))
       {
          return static::$$property;
       }
       
       return  self::throwNewException(323234234234234," Proprietà: ".$propertyName." Non Definita!");
    }

    /**
     * Imposta l'attributo 
     * 
     * @param String $property  Nome dell'attributo dell'oggetto
     * @param Mixed  $value     Valore
     * 
     * @return Mixed
     */
    public function setProperty($property,$value)
    {
        $this->{$property} = $value;
        return $this;
    }
    
    /**
     * Imposta più attributi insieme come array
     * 
     * @param Array  $properties Array "proprietò" => "valore"
     * 
     * @return Mixed
     */
    public function setProperties(array $properties)
    {
        foreach($properties as $property => $value)
        {
            $this->setProperty($property, $value);
        }
        
        return $this;
    }
    
    /**
     * Ricerca il valore della proprietà dell'oggetto
     * 
     * @param string $property Nome della proprietà
     * 
     * @return Mixed|Boolean Valore
     * 
     * @throw \Exception
     */
    public function getProperty($property)
    {
        $oriProperty = $property;
        
        if(isset($this->{$property}))
        {
            return $this->{$property};
        }
        
        $property = '_'.$oriProperty;

        if(isset($this->{$property}))
        {
            return $this->{$property};
        }
        
        $property = preg_replace("/^\_(.*)$/","$1",$oriProperty);

        if(isset($this->{$property}))
        {
            return $this->{$property};
        }
        
        throw new \Exception('Property "'.$oriProperty.'" non presente per l\'oggetto '.get_called_class());
    }
    
    public function getAllProperties()
    {
        return get_object_vars($this);
    }
}