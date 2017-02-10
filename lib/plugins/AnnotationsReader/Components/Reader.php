<?php

namespace plugins\AnnotationsReader\Components;

use plugins\AnnotationsReader\Components\Annotation;
use plugins\AnnotationsReader\Exception\ReaderException;

/**
 * Classe che legge le annotazioni per un oggetto/classe
 */
class Reader
{    
    /**
     * Nome classe / Oggetto instanziato
     * @var Mixed
     */
    protected $class;

    /**
     * Array delle proprietà da leggere, default all
     * 
     * @var array
     */
    protected $properties = null;
    
    /**
     * Instanzia il reader
     * 
     * @param mixed $class classe/oggetto da analizzare
     */
    public function __construct($class = null)
    {
        $this->class = $class;
    }
    
    /**
     * Imposta la classe/oggetto da leggere
     * 
     * @param mixed $class classe/oggetto
     * 
     * @return \plugins\AnnotationsReader\Components\Reader
     * 
     * @throws ReaderException
     */
    public function setClass($class)
    {
        if(is_object($class))
        {
            $this->class = $class;
            return $this;
        }
        
        if(is_string($class) && class_exists($class))
        {
            $this->class = $class;
            return $this;
        }
        
        throw new ReaderException('Non è possibile leggere le annotazioni per il termine indicato '.print_r($class,true),47257548);
    }
    
    
    /**
     * Imposta le proprietà da leggere per le annotazioni
     * 
     * @param array $properties lista delle proprietà, null legge tutte
     * 
     * @return \plugins\AnnotationsReader\Components\Reader
     */
    public function setProperties($properties)
    {
        $this->properties = $properties;
        return $this;
    }
    
    /**
     * Legge le annotazioni 
     * 
     * @param String $methodNamePattern [OPZIONALE] Pattern dei metodi da analizzare, default tutti
     * @param Int    $methodType        [OPZIONALE] Tipologia di metodo, array delle costanti \ReflectionMethod::IS_*, default tutti
     * 
     * @return array
     */
    public function read($methodNamePattern = null,$methodType = null)
    {
        $reflectionClass = new \ReflectionClass($this->class);
        $methods         = $reflectionClass->getMethods();

        $annotations = array();
        $annotations += $this->parseAnnotation($reflectionClass->getDocComment()); //Carico le annotazioni sulla classe

        if($methods)
        {
            foreach($methods as $method)/*@var $method \ReflectionMethod*/
            {
                $read = true;
                
                if($methodNamePattern)
                {
                    $read = preg_match($methodNamePattern, $method->getName());
                }
                
                if($read && !is_null($methodType))
                {
                    $methodType = is_array($methodType) ? $methodType : array($methodType);
                    
                    foreach($methodType as $type)
                    {
                        if(!$read)
                        {
                            switch($type)
                            {
                                case \ReflectionMethod::IS_ABSTRACT:  $read = $method->isAbstract();   break;
                                case \ReflectionMethod::IS_FINAL:     $read = $method->isFinal();      break;
                                case \ReflectionMethod::IS_PRIVATE:   $read = $method->isPrivate();    break;
                                case \ReflectionMethod::IS_PROTECTED: $read = $method->isProtected();  break;
                                case \ReflectionMethod::IS_PUBLIC:    $read = $method->isPublic();     break;
                                case \ReflectionMethod::IS_STATIC:    $read = $method->isStatic();     break;
                            }
                        }
                    }
                }
                
                if($read)
                {
                    $annotations+=$this->parseAnnotation($method->getDocComment(),$method);
                }
            }
        }
        
        
        return $annotations;
    }
    
    /**
     * Effettua il parse delle annotazioni sull'oggetto
     * 
     * @param String            $comment    commento da analizzare
     * @param \ReflectionMethod $method     [OPZIONALE] metodo analizzato dell'oggetto, default NULL
     * 
     * @return Array
     * 
     * @throws ReaderException
     */
    protected function parseAnnotation($comment,\ReflectionMethod $method = null)
    {    
        $annotations = array();
        
        if(trim($comment)!= '')
        {
            $comment = preg_replace(array('/\/\*\*/','/\s\*/','/\s\//','/\n/'),'',$comment);
            $commentPiece = explode("@",$comment);
           
            if($commentPiece)
            {
                foreach($commentPiece as $property)
                {
                    $readProperty = false;
                    $jsonData     = null;
                    
                    if(preg_match('/([A-z]+)\((.*)?\)/',$property,$matches))
                    {
                        $property = $matches[1];
                        $jsonData = json_decode($matches[2],true);
                    }
                    
                    if((is_array($this->properties) && in_array($property,$this->properties)) || is_null($this->properties))
                    {
                        $readProperty = true;
                    }

                    if($readProperty)
                    {
                        if(!$jsonData)
                        {
                            throw new ReaderException('Non è possibile leggere l\'annotazione "'.$property.'" per l\'oggetto "'.$this->class.'" poichè contiene un valore che non è un JSON valido!',235923592358723526);
                        }
                        
                        $annotations[] = new Annotation($property, $jsonData,$this->class,$method ? $method->getName() : null);
                    }
                    
                }
            }
        }
        
        return $annotations;
    }
}