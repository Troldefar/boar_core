<?php

namespace app\core\src\exceptions;

class NotFoundException extends \Exception {

    protected $code = 404;
    protected $message = 'Not found';

}