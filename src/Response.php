<?php

namespace JermaineDavy\JsonValidator;

use Stringable;

class Response implements Stringable{
    /**
     * The status of the validation. Should the validation of the json input be
     * successful then the status would be true and false if it should fail for
     * any reason
    */
    public bool $status;

    /**
     * A descriptive message of result of the validation.
    */
    public string $message;

    public function __toString(): string{
        return json_encode($this);
    }

    public function __construct(bool $status, string $message){
        $this->status = $status;
        $this->message = $message;
    }
}

?>