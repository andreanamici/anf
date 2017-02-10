<?php

namespace plugins\AnnotationsReader\Components;

/**
 * Contenitore dell'annotazione
 */
class Annotation
{
    
    /**
     * Nomde dell'annotazione
     * @var string
     */
    private $name = null;
    
    /**
     * Valore
     * @var mixed
     */
    private $value = null;
    
    
    /**
     * Nome dell'oggetto
     * @var String
     */
    private $class = null;
    
    
    /**
     * Metodo
     * @var String
     */
    private $method = null;
    
    /**
     * Contenitore dell'annotazione
     */
    public function __construct($name,$value,$class,$method = null)
    {
        $this->name   = $name;
        $this->value  = $value;
        $this->class  = $class;
        $this->method = $method;
    }
    
    /**
     * Restituisce il nome dell'annotazione
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Restituisce il valore dell'annotazione
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
    
    public function getClass()
    {
        return $this->class;
    }
    
    public function getMethod()
    {
        return $this->method;
    }
}