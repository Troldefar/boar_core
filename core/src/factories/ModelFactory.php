<?php

namespace app\core\src\factories;

use \app\core\src\database\Entity;

class ModelFactory extends AbstractFactory {

    protected const MODEL_PREFIX = 'Model';
    protected const MODEL_NAMESPACE = '\\app\models\\';

    public function create(): ?Entity {
        $model = self::MODEL_NAMESPACE . $this->getHandler() . self::MODEL_PREFIX;
        if (!$this->validateObject($model)) return null;

        return new $model();
    }

}