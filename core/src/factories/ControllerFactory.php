<?php

namespace app\core\src\factories;

use \app\controllers\AssetsController;
use \app\core\src\Controller;

class ControllerFactory extends AbstractFactory {

    protected const CONTROLLER_AFFIX = 'Controller';
    protected const CONTROLLER_NAMESPACE = '\\app\controllers\\';

    public function create(): ?Controller {
        $controller = self::CONTROLLER_NAMESPACE . $this->getHandler() . self::CONTROLLER_AFFIX;
        if (!$this->validateObject($controller)) return null;
        
        $app = app();
        return new $controller($app->getRequest(), $app->getResponse(), new AssetsController());
    }

}