<?php

namespace JermaineDavy\JsonValidator;

use Closure;
use JermaineDavy\JsonValidator\Common;
use JermaineDavy\JsonValidator\Response;
use JermaineDavy\JsonValidator\Specification;
use JermaineDavy\JsonValidator\Checker;
use JermaineDavy\JsonValidator\Exceptions\JsonException;

class Validator{
    /**
     *  Store an array of specifications which would need to be validated.
     */
    private array $specification;

    /**
     * Stores any errors encountered while validating the specification.
    */
    private array $specificationErrors;

    /**
     * Retrieves a list of all the specification arrays produced when the 
     * specifications were validated.
     * 
     * @return array
    */
    public function getSpecificationErrors(): array{
        return $this->specificationErrors;
    }

    /**
     * Converts the json string to an array that the be processed further.
     * 
     * @param mixed $json
     * 
     * @return array
    */
    private function convertToArray(mixed $json): array{
        if(gettype($json) == 'string'){
            $json = json_decode($json, true);
        }
        
        if(!is_array($json)){
            throw new JsonException();
        }

        return $json;
    }

    /**
     * Gets all publicly declared variables and sets those to be the specifications
     * against which the json data would be validated against.
     * 
     * @return void
    */
    private function setSpecification(): void{
        $this->specification = Common::getClassPublicProperties($this);
    }
    
    /**
     * Handles the processing of the JSON validation.
     * 
     * @param array $json
     * 
     * @return JermaineDavy\JsonValidator\Response|null
    */
    private function processValidation(array $json): Response{
        $spec = new Specification();

        if(!$spec->verifySpecification($this::class, $this->specification)){
            $this->specificationErrors = $spec->getSpecificationErrors();

            return new Response(false, "JSON Specification failed validation");
        }

        $checker = new Checker();

        return $checker->run($json, $this->specification);
    }

    /**
     * The main call for validating the json data against the provided
     * specification in the subclass. The return type is specified as mixed
     * because it could either return JermaineDavy\JsonValidator\Response or
     * whatever the return value of the optional Closure is.
     * 
     * @param mixed $json
     * @param callable|null $closure
     * 
     * @return mixed
    */
    final public function validate(mixed $json, Closure $closure = null): mixed{
        try{
            $response = $this->processValidation($this->convertToArray($json));

            if($closure != null && $response->status)
                return $closure($json, $response);
            
            return $response;
        }catch(JsonException){
            return new Response('false', 'Invalid JSON data sent.');
        }
    }

    public function __construct(){
        $this->setSpecification();
    }
}

?>