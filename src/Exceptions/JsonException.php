<?php

namespace JermaineDavy\JsonValidator\Exceptions;

use Exception;

class JsonException extends Exception{
    public function errorMessage(): string{
        return "Invalid JSON data.";
    }
}

?>