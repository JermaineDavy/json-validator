# Json-Validator

## Installation

To install this package use the following command:

```sh
composer install JermaineDavy\JsonValidator
```

If you don't have the Composer package manager installed, you could download it at this [link](https://getcomposer.org/download/)


## Usage

This packages allows json strings to be evaluated against models represented by classes.

### Basic Usage

It's simple to use and only requires that 2 things be done.

1. Define a model
    - The model must be a child class of the Validator class.

```php

use JermaineDavy\JsonValidator\Validator;

class Test extends Validator{
    public array $state = [
        "type" => "boolean",
        "required" => true
    ];

    public array $message = [
        "type" => "string",
        "min" => 3,
        "max" => 100
    ];
}

```

2. Run the json validation against the model.

```php

$validator = new Test();

$response = $validator->validate($json);

```

The validate method returns an object containing only 2 properties. The `status` of the validation and a `message` explaining the status. If the status returned it `true` then the validation has been successful, otherwise the validation has failed and more information could be deemed from the message.

### What checks are allowed?
- type(string)-> Checks that the type specified in the JSON matches that of the type in the model
- required(boolean) -> Checks that a field of the model is within the JSON
- min(double|integer) -> Checks that the string length or number is less than what is specified in the model
- max(double|integer) -> Checks that the string length or number is greater than what is specified in the model
- enum(array) -> Checks that the JSON value matches one of the values in a given array
- regex(string|array) -> Performs 1 or more checks to see if the value matches a regular expression
- dateFormat(string) -> Checks that the JSON value matches a given date format
- custom(Closure) -> Used for custom checks which might not be defined by this library. The closure should return an empty string is successful or an error string if it failed.

The type value could also refer to Other Models which extend the Validator class. Eg.

```php

use JermaineDavy\JsonValidator\Validator;

class Test extends Validator{
    public array $state = [
        "type" => "boolean",
        "required" => true
    ];

    public array $object = [
        "type" => SecondTest::class
    ];
}

```
This syntax would work for both a single instance of `SecondTest` or an array of `SecondTest`