<?php

namespace app\core\src\traits\application;

use \app\models\UserModel;

trait ApplicationStaticMethodTrait {
    
    /**
    |----------------------------------------------------------------------------
    | Static methods
    |----------------------------------------------------------------------------
    |
    */

    public static function isCLI(): bool {
        return php_sapi_name() === 'cli';     
    }

    public static function isGuest(): bool {
        return !(new UserModel())->hasActiveSession();
    }

    public static function isDevSite(): bool {
        return self::$app->getConfig()->get('inDevelopment') === true;
    }

}