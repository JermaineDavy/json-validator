<?php

namespace JermaineDavy\JsonValidator;

use JermaineDavy\JsonValidator\Common;
use JermaineDavy\JsonValidator\Exceptions\ValidatorClassException;

class Specification{
    private int $errorLimit;

    /**
     * A set of allowed specification values. These are compared to the provided
     * specifications to ensure that this library would be able to parse the
     * inputted data.
    */
    private array $allowedSpecificationValues = [
        "type" => "string",
        "required" => "boolean",
        "min" => [
            "double",
            "integer"
        ],
        "max" => [
            "double",
            "integer"
        ],
        "enum" => "array",
        "regex" => [
            "string",
            "array"
        ],
        "dateFormat" => "string",
        "custom" => "object"
        // "define" => "array"
    ];

    /**
     * Stores an errors encountered while validating the specification.
    */
    private array $errors = [];

    /**
     * Validate that the datatypes used in the specification matches that of
     * what is allowed to be used for the given specification.
     * 
     * @param mixed $value
     * @param string $key
     * @param mixed $allowedTypes
     * 
     * @return bool
    */
    private function verifySpecificationDataTypes(mixed $value, string $key, mixed $allowedTypes): bool{
        if(gettype($allowedTypes) == 'array'){
            return in_array(gettype($value), $allowedTypes);
        }

        if($key == 'type' && !in_array($value, Common::getDefaultDataTypes())){
            if(class_exists($value)){
                if(!is_subclass_of($value, 'JermaineDavy\JsonValidator\Validator')){
                    throw new ValidatorClassException();
                }

                $this->verifySpecification($value, get_class_vars($value));
                
                return true;
            }

            return false;
        }

        return (gettype($value) == $allowedTypes);
    }

    /**
     * Validates the rules of the provided specification against the rules and
     * datatypes allowed by this library.
     * 
     * @param string $fqcn - The Fully Qualified Class Name. Used to give more 
     * accurate descriptions of where the issue is
     * @param array $providedSpecification
     * 
     * @return bool
    */
    public function verifySpecification(string $fqcn, mixed $providedSpecification): bool{
        /**
         * Array walk is used over the standard foreach loop because it allows
         * me to provide more information to the nested code which results
         * in more detailed errors for debugging.
        */
        array_walk($providedSpecification, function($specification, $specificationKey, $fqcn){
            foreach($specification as $key => $value){
                /**
                 * In some cases the developer may only want to display a certain
                 * number of errors per given request. Setting a limit on the 
                 * number of rules verified gives the developer more control
                 * over how the data is to be handled;
                */
                if($this->errorLimit > 0 && $this->errorLimit == count($this->errors)){
                    break;
                }

                if(!array_key_exists($key, $this->allowedSpecificationValues)){
                    array_push($this->errors, sprintf("Invalid Specification value provided at: %s::$%s", $fqcn, $key));
                }else{
                    if(!$this->verifySpecificationDataTypes($value, $key, $this->allowedSpecificationValues[$key])){
                        array_push($this->errors, sprintf("Invalid Data Type used at: %s::$%s[%s]", $fqcn, $specificationKey, $key));
                    }
                }
            }
        }, $fqcn);

        if(count($this->errors) > 0){
            return false;
        }

        return true;
    }

    /**
     * Retrieves an error of errors store in this class should there be any.
     * 
     * @return array
    */
    public function getSpecificationErrors(): array{
        return $this->errors;
    }

    /**
     * Validates the specification of which the json should be validated
     * against. The constructor takes in a number of arguments that allows
     * for configuration of how the data should be validated.
     * 
     * @param int $errorLimit - The number of errors that should be kept
     * for further revision. If set to 0, it will keep track of **ALL**
     * the specification errors encountered.
    */
    public function __construct(int $errorLimit = 0){
        $this->errorLimit = $errorLimit;
    }
}

?>