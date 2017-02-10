<?php

use \plugins\AnnotationsReader\Components\Reader;
use \plugins\AnnotationsReader\Components\Annotation;

$annotationReader = new Reader();

if(!empty($options['class']))
{
    $annotationReader->setClass($options['class']);
}

if(!empty($options['properties']))
{
    $annotationReader->setProperties($options['properties']);
}

return $annotationReader;