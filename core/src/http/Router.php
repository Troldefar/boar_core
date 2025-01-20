<?php

/**
|----------------------------------------------------------------------------
| Application router
|----------------------------------------------------------------------------
| 
| The application send the current request to this object, which dispatches
| the appropriate controller / method
|
| @author RE_WEB
| @package core
|
*/

namespace app\core\src\http;

use \app\core\Application;

use \app\core\src\Controller;
use \app\core\src\File;

use \app\core\src\exceptions\InvalidTypeException;

use \app\core\src\factories\ControllerFactory;

use \app\core\src\miscellaneous\CoreFunctions;

final class Router {

    private const EXPECTED_CONTROLLER_METHOD_POSITION = 1;

    protected const INDEX_METHOD = 'index';
    protected const RESOURCE_INDICATOR = '/resources';
    
    protected array $arguments;
    protected string $method;

    public function __construct(
       private Request $request,
       private Application $app
    ) {
        $this->arguments = $request->getArguments();
    }

    private function checkRouteAndGoToDefault(): void {
        if (empty($this->arguments) || $this->request->getPath() === '/') 
            $this->app->getResponse()->redirect(CoreFunctions::first($this->app->getConfig()->get('routes')->unauthenticated)->scalar);
    }

    protected function createController(): void {
        $this->checkRouteAndGoToDefault();

        $handler = ucfirst(CoreFunctions::first($this->arguments)->scalar);
        if ($this->isResource($handler)) exit();

        $controller = (new ControllerFactory(compact('handler')))->create();
        if (!$controller) throw new InvalidTypeException('Invalid handler');

        $controllerMethod = $this->arguments[self::EXPECTED_CONTROLLER_METHOD_POSITION] ?? '';

        $this->app->setParentController($controller);
        $this->method = $controllerMethod === '' || !method_exists($controller, $controllerMethod) ? self::INDEX_METHOD : $controllerMethod;

        if (!method_exists($controller, $this->method)) $this->app->getResponse()->redirect($this->app->getConfig()->get('routes')->defaults->redirectTo);
    }

    private function isResource(string $handler): bool {
        return str_contains(strtolower($handler), strtolower(self::RESOURCE_INDICATOR));
    }

    protected function runMiddlewares(): void {
        foreach ($this->app->getParentController()->getMiddlewares() as $middleware) $middleware->execute();
    }

    protected function runController(): void {
        $controller = $this->app->getParentController();

        if (!IS_CLI) $controller->setChildren(['Header', 'Footer']);

        $controller->setChildData();
        $controller->{$this->method}();
    }

    protected function hydrateDOM(): void {
        $controller = $this->app->getParentController();
        echo $this->handleFrontendHydration($controller, $controller->getData());
    }

    private function handleFrontendHydration(Controller $controller, array $data) {
        extract($data, EXTR_SKIP);
        $layoutFile = $this->app::$ROOT_DIR .  File::LAYOUTS_FOLDER . $controller->getLayout() . File::TPL_FILE_EXTENSION; 
        ob_start();
            require_once $controller->getView();
        $viewContent = ob_get_clean();

        ob_start();
            require_once $controller->getDataKey('header');
            require_once $layoutFile;
            require_once $controller->getDataKey('footer');
        return str_replace('{{content}}', $viewContent, ob_get_clean());
    }

    public function resolve(): void {
        $this->createController();
        $this->runMiddlewares();
        $this->runController();
        $this->hydrateDOM();
    }

}