<?php

namespace app\core\src\factories;

class MigrationFactory extends AbstractFactory {

    public function create(): object {
        $controller = $this->getHandler();
        $this->validateObject($controller);
        return new $controller();
    }

}