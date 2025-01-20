<?php

/**
|----------------------------------------------------------------------------
| Third party communication base
|----------------------------------------------------------------------------
|
| Base for communicating with external services
|
| @author RE_WEB
| @package app\core\src\thirdpartycommunication
|
*/

namespace app\core\src\thirdpartycommunication;

use \app\core\src\http\Curl;

abstract class ThirdPartyCommunication {

    public function __construct(
        protected array $arguments = [],
        protected Curl $curl = new Curl()
    ) {
    }
    
    abstract public function sendAndReceive();

    protected function getArguments(): object {
        return (object)$this->arguments;
    }

}