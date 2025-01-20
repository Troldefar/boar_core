<?php

namespace app\core\src\exceptions;

class ForbiddenException extends \Exception {

    protected $code = 403;
    protected $message = 'Forbidden page';
    
}