<?php

namespace JermaineDavy\JsonValidator;

use Closure;
use DateTime;
use JermaineDavy\JsonValidator\Response;
use JermaineDavy\JsonValidator\Common;
use JermaineDavy\JsonValidator\Abstractions\CheckerAbstraction;

class Checker extends CheckerAbstraction{
    private array $checkedSpecs = [];

    /**
     * Placeholder for the error message that could be generated in this class
    */
    private string $errorMessage = '';

    /**
     * Checks that the datatype of the json field matches that of the specification
     * 
     * @param string $specificationValue
     * @param mixed $jsonKey
     * @param mixed $jsonValue
     * 
     * @return void
    */
    protected function typeCheck(string $specificationValue, mixed $jsonKey, mixed $jsonValue): void{
        if(class_exists($specificationValue) && gettype($jsonValue) == 'array'){
            if(count($jsonValue) > 1){
                foreach($jsonValue as $object){
                    $this->run($object, Common::getClassPublicProperties($specificationValue));
                }
            }else{
                $this->run($jsonValue, Common::getClassPublicProperties($specificationValue));
            }
        }else{
            $datatype = gettype($jsonValue);

            if($datatype != $specificationValue){
                $this->errorMessage = "Invalid data type used in field `{$jsonKey}`. Expected type `{$specificationValue}`, `{$datatype}` provided.";
            }
        }
    }

    /**
     * Checks that the value in the given JSON field is within an array of 
     * allowed values.
     * 
     * @param array $specificationValue
     * @param mixed $jsonKey
     * @param mixed $jsonValue
     * 
     * @return void
    */
    protected function enumCheck(array $specificationValue, mixed $jsonKey, mixed $jsonValue): void{
        if(!in_array($jsonValue, $specificationValue)){
            $this->errorMessage = "Data provided in field `{$jsonKey}` is not within the allowed parameters.";
        }
    }

    /**
     * Checks the a string or number is a given JSON field is less than what is
     * in the specification.
     * 
     * @param int|float $specificationValue
     * @param mixed $jsonKey
     * @param mixed $jsonValue
     * 
     * @return void
    */
    protected function minCheck(int|float $specificationValue, mixed $jsonKey, mixed $jsonValue): void{
        if(gettype($jsonValue) == 'string'){
            if(strlen($jsonValue) < $specificationValue){
                $this->errorMessage = "Field `{$jsonKey}` cannot be less than {$specificationValue} characters";
            }
        }else{
            if($jsonValue < $specificationValue){
                $this->errorMessage = "Field `{$jsonKey}` cannot be less than {$specificationValue}";
            }
        }
    }

    /**
     * Checks the a string or number is a given JSON field is greater than what
     * is in the specification.
     * 
     * @param int|float $specificationValue
     * @param mixed $jsonKey
     * @param mixed $jsonValue
     * 
     * @return void
    */
    protected function maxCheck(int|float $specificationValue, mixed $jsonKey, mixed $jsonValue): void{
        if(gettype($jsonValue) == 'string'){
            if(strlen($jsonValue) > $specificationValue){
                $this->errorMessage = "Field `{$jsonKey}` cannot be more than {$specificationValue} characters";
            }
        }else{
            if($jsonValue > $specificationValue){
                $this->errorMessage = "Field `{$jsonKey}` cannot be greater than {$specificationValue}";
            }
        }
    }

    private function runRegexCheck(string $pattern, mixed $jsonKey, string $jsonValue): void{
        if(preg_match($pattern, $jsonValue) != 1){
            $this->errorMessage = "Field `{$jsonKey}` does not match any expected patterns";
        }
    }

    /**
     * Checks a given string against one or many regular expression
     * 
     * @param string|array $specificationValue
     * @param mixed $jsonKey
     * @param string $jsonValue
     * 
     * @return void
    */
    protected function regexCheck(string|array $specificationValue, mixed $jsonKey, string $jsonValue): void{
        if(gettype($specificationValue) == 'array'){
            foreach($specificationValue as $value){
                $this->runRegexCheck($value, $jsonKey, $jsonValue);
            }
        }else{
            $this->runRegexCheck($specificationValue, $jsonKey, $jsonValue);
        }
    }

    /**
     * Checks the format of the date in a json and compares it to the
     * specification.
     * 
     * @param string $format
     * @param string $key
     * @param string $value
     * 
     * @return void
    */ 
    protected function dateFormatCheck(string $format, string $key, string $value): void{
        $comparator = DateTime::createFromFormat($format, $value);

        if(!$comparator || $comparator->format($value) !== $value){
            $this->errorMessage = "Invalid datetime format used in field `{$key}`";
        }
    }

    /**
     * Runs the closure provided in the specification and passes it the name
     * and value of the json field that triggered it.
     * 
     * @param Closure $function
     * @param string $name
     * @param mixed $value
     * 
     * @return void
    */
    protected function customFunction(Closure $function, string $name, mixed $value): void{
        $this->errorMessage = $function($name, $value);
    }

    /**
     * Triggers the appropriate function call based on the supplied specification
     * key
     * 
     * @param mixed $jsonKey
     * @param mixed $jsonValue
     * @param mixed $specificationKey
     * @param mixed $specificationValue
     * 
     * @return void
    */
    private function triggerCheck(mixed $jsonKey, mixed $jsonValue, mixed $specificationKey, mixed $specificationValue): void{
        switch($specificationKey){
            case 'type':
                $this->typeCheck($specificationValue, $jsonKey, $jsonValue);
                break;
            case 'enum':
                $this->enumCheck($specificationValue, $jsonKey, $jsonValue);
                break;
            case 'min':
                $this->minCheck($specificationValue, $jsonKey, $jsonValue);
                break;
            case 'max':
                $this->maxCheck($specificationValue, $jsonKey, $jsonValue);
                break;
            case 'regex':
                $this->regexCheck($specificationValue, $jsonKey, $jsonValue);
                break;
            case 'dateFormat':
                $this->dateFormatCheck($specificationValue, $jsonKey, $jsonValue);
                break;
            case 'custom':
                $this->customFunction($specificationValue, $jsonKey, $jsonValue);
        }
    }

    /**
     * Triggers a check on the json field for each specification supplied.
     * 
     * The function breaks out of its loop should it find that a single
     * error has been recorded in the private errorMessage variable 
     * 
     * @param mixed $key
     * @param mixed $value
     * @param array $specifications
     * 
     * @return void
    */
    private function check(mixed $key, mixed $value, array $specifications): void{
        foreach($specifications as $specificationKey => $specificationValue){
            if($this->errorMessage != ''){
                break;
            }

            $this->triggerCheck($key, $value, $specificationKey, $specificationValue);
        }
    }

    /**
     * Iterates over the JSON data while passing it along to helper functions
     * for processing.
     * 
     * @param array $json;
     * @param array $specification
     * 
     * @return JermaineDavy\JsonValidator\Response
    */
    public function run(array $json, array $specification): Response{
        array_walk($json, function($value, $key, $specification){
            $this->check($key, $value, $specification[$key]);
            array_push($this->checkedSpecs, $key);
        }, $specification);

        /**
         * TODO: Add an additional check to ensure that all required specs
         * were indeed checked.
        */

        if($this->errorMessage != ''){
            return new Response(false, $this->errorMessage);
        }

        return new Response(true, 'JSON data has successfully been validated.');
    }
}

?>