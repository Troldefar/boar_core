<?php

/**
|----------------------------------------------------------------------------
| Base of service providers
|----------------------------------------------------------------------------
|
| @author RE_WEB
| @package core
|
*/

namespace app\core\src\providers;

interface ServiceProvider {

    public function register(): void;
    public function boot(): void;

}