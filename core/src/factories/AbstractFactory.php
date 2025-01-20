<?php

/**
|----------------------------------------------------------------------------
| Factory base
|----------------------------------------------------------------------------
|
| @author RE_WEB
| @package app\core\src\factories
|
*/

namespace app\core\src\factories;

use \app\core\src\miscellaneous\CoreFunctions;

abstract class AbstractFactory {

    public function __construct(
        protected array $arguments = []
    ) {
        
    }
    
    abstract public function create(): ?object;

    public function getHandler(): string {
        return CoreFunctions::getIndex($this->arguments, 'handler')->scalar;
    }

    public function validateObject(string $class): bool {
        return class_exists($class);
    }

    public function getKey(): ?string {
        return CoreFunctions::getIndex($this->arguments, 'key')->scalar ?? null;
    }

}