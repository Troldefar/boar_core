<?php

/**
|----------------------------------------------------------------------------
| Entity authorization defaults "methods"
|----------------------------------------------------------------------------
| 
| @author RE_WEB
| @package \app\core\src\gate
|
*/

namespace app\core\src\traits\gate;

use \app\core\src\database\Entity;
use \app\core\src\miscellaneous\CoreFunctions;
use \app\models\UserModel;

trait GateStaticMethodTrait {

    private const FAILED_AUTH_ATTEMPT = 'Failed gate attempt registered: ';
    private const INVALID_PARAMS = 'You must first specify a method KVP and then additional parameters';
    private const INVALID_METHOD = 'The requested method does not exist';
    private const METHOD_KEY = 'method';

    public static function dispatch(array $methodArguments): bool {
        self::validateMethodArguments($methodArguments);
        
        $staticMethodArguments = $methodArguments;
        unset($staticMethodArguments[self::METHOD_KEY]);

        $result = self::{$methodArguments[self::METHOD_KEY]}(CoreFunctions::first($staticMethodArguments));

        if ($result) return $result;
        
        return self::registerFailedAttempt(information: $methodArguments);
    }

    public static function validateMethodArguments(array $methodArguments): void {
        if (!isset($methodArguments[self::METHOD_KEY]) || count($methodArguments) < 2) 
            throw new \app\core\src\exceptions\EmptyException(self::INVALID_PARAMS);
        
        if (!method_exists(__CLASS__, $methodArguments[self::METHOD_KEY]))
            throw new \app\core\src\exceptions\ForbiddenException(self::INVALID_METHOD);
    }

    public static function canInteractWith(string $method, object $body) {
        return self::dispatch([self::METHOD_KEY => $method, $body]);
    }

    public static function isAuthenticatedUserAllowed(string $method, Entity $entity): bool {
        return self::dispatch([self::METHOD_KEY => $method, $entity]);
    }

    public static function isSpecificUserAllowed(string $method, UserModel $user, Entity $entity): bool {
        return self::dispatch([self::METHOD_KEY => $method, $user, $entity]);
    }

    public static function isEntityAllowed(string $method, Entity $entityFrom, Entity $entityTo): bool {
        return self::dispatch([self::METHOD_KEY => $method, $entityFrom, $entityTo]);
    }

    public static function registerFailedAttempt(array $information): bool {
        app()->addSystemEvent([self::FAILED_AUTH_ATTEMPT . json_encode($information)]);
        return false;
    }

}