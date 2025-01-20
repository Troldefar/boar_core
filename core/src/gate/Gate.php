<?php

/**
|----------------------------------------------------------------------------
| Entity authorization
|----------------------------------------------------------------------------
| From here you can control access between entities and actions
| 
| @author RE_WEB
| @package \app\core\src\gate
|
*/

namespace app\core\src\gate;

use \app\core\src\database\Entity;
use \app\core\src\miscellaneous\CoreFunctions;
use \app\core\src\traits\gate\GateStaticMethodTrait;

class Gate {

    use GateStaticMethodTrait;

    protected static function canViewProduct(Entity $product): bool {
        $user = CoreFunctions::applicationUser();
        
        return $product->user()->key() === $user->key() || $user->isAdmin();
    }

    protected static function playground(object $requestBody): bool {
        return 
            isset($requestBody->body->playgroundKey) && 
            $requestBody->body->playgroundKey === app()->getConfig()->get('playgroundKey') /*&&
            CoreFunctions::applicationUser()->isAdmin()*/;
    }

}