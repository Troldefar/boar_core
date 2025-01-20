<?php

/**
 * Bootstrap AuthMiddleware 
 * AUTHOR: RE_WEB
 * @package app\core\AuthMiddleware
*/

namespace app\core\src\middlewares;

class AuthMiddleware extends Middleware {

    public array $actions = [];

    public function __construct(array $actions = []) {
        $this->actions = $actions;
    }

    public function execute() {
        if (app()::isGuest()) 
            if (empty($this->actions) || in_array(app()::$app->controller->action, $this->actions)) 
                app()->getResponse()->redirect('/');
    }

}
