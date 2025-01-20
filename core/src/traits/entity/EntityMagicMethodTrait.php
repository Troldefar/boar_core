<?php

/**
|----------------------------------------------------------------------------
| Magic methods
|----------------------------------------------------------------------------
|
*/

namespace app\core\src\traits\entity;

use app\core\src\miscellaneous\CoreFunctions;

trait EntityMagicMethodTrait {

    private const INVALID_ENTITY_KEY    = 'Invalid entity key';
    private const INVALID_ENTITY_STATIC_METHOD = 'Invalid static method';
    private const INVALID_ENTITY_METHOD = 'Invalid non static method method';

    private const OVERLOAD_ARGC_NEW_ENTITY  = 1;
    private const OVERLOAD_ARGC_EDIT_ENTITY = 2;

    public static function __callStatic($name, $arguments) {
        throw new \app\core\src\exceptions\NotFoundException(self::INVALID_ENTITY_STATIC_METHOD . " [{$name}]");
    }

    public function __get(string $key) {
        return $this->getData()[$key] ?? new \Exception(self::INVALID_ENTITY_KEY);
    }

    public function __toString() {
        $result = get_class($this) . "($this->key): \n";
        foreach ($this->getData() as $key => $value) $result .= "[$key]:$value\n";
        return $result;
    }

    public function __call(string $method, array $arguments): ?array {
        if (!$this->checkAvailableCallMethods($method)) return null;

        $argc = count($arguments);
        $this->checkOverloadArgumentCount($argc, [self::OVERLOAD_ARGC_NEW_ENTITY, self::OVERLOAD_ARGC_EDIT_ENTITY]);

        $data = (array)CoreFunctions::first($arguments);

        unset($data['eg-csrf-token-label']);
        unset($data['action']); 

        if ($argc === self::OVERLOAD_ARGC_NEW_ENTITY) {
            $cEntity = new $this();
            $cEntity->set($data);
            $cEntity->save();
        } else if ($argc === self::OVERLOAD_ARGC_EDIT_ENTITY) {
            $this->set($data);
            $this->save();
            $cEntity = $this;
        }

        return isset($cEntity) && method_exists($cEntity, 'safeFieldsDescription') ? $cEntity->safeFieldsDescription() : ['message' => 'OK'];
    }

}