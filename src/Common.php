<?php

namespace JermaineDavy\JsonValidator;

use ReflectionClass;

class Common{
    /**
     * Generates an array of all the public variables in a given object or
     * class.
     * 
     * @param object|string $class
     * 
     * @return array
    */
    public static function getClassPublicProperties(object|string $class): array{
        $reflection = new ReflectionClass($class);

        return $reflection->getDefaultProperties();
    }

    /**
     * Gets an array of all the default types for describing the specifications
     * 
     * @return array
    */
    public static function getDefaultDataTypes(): array{
        return ['string', 'double', 'integer', 'boolean', 'array'];
    }
}

?>