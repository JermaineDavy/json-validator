<?php

namespace JermaineDavy\JsonValidator\Abstractions;

use Closure;

abstract class CheckerAbstraction{
    abstract protected function typeCheck(string $specificationValue, mixed $jsonKey, mixed $jsonValue): void;
    // abstract protected function requiredCheck();
    abstract protected function minCheck(int|float $specificationValue, mixed $jsonKey, mixed $jsonValue): void;
    abstract protected function maxCheck(int|float $specificationValue, mixed $jsonKey, mixed $jsonValue): void;
    abstract protected function regexCheck(string|array $specificationValue, mixed $jsonKey, string $jsonValue): void;
    abstract protected function enumCheck(array $specificationValue, mixed $jsonKey, mixed $jsonValue): void;
    abstract protected function dateFormatCheck(string $format, string $key, string $value): void;
    abstract protected function customFunction(Closure $function, string $name, mixed $value): void;
}

?>