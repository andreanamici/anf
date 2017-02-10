<?php

use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Restituisce l'instanza del validator
 */
return  Validation::createValidator();