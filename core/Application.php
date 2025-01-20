<?php

/**
|----------------------------------------------------------------------------
| Bootstrap application
|----------------------------------------------------------------------------
|
| @author RE_WEB
| @package core
|
*/

namespace app\core;

use \app\core\src\contracts\Service;

use \app\core\src\database\adapters\MySQL;

use \app\core\src\database\Connection;

use \app\core\src\factories\ControllerFactory;

use \app\core\src\miscellaneous\CoreFunctions;
use \app\models\SystemEventModel;
use \app\models\UserModel;

use \app\core\src\traits\application\ApplicationGetterTrait;
use \app\core\src\traits\application\ApplicationStaticMethodTrait;

use \app\core\src\services\ApplicationServices;

use Throwable;

final class Application {

    use ApplicationGetterTrait;
    use ApplicationStaticMethodTrait;

    protected src\http\Router $router;
    protected src\http\Request $request;
    protected src\http\Response $response;
    protected src\http\Session $session;
    protected src\http\View $view;

    protected ApplicationServices $appServices;

    protected Connection $connection;

    protected src\I18n $i18n;

    protected src\config\Config $config;

    protected src\utilities\Logger $logger;

    protected src\Controller $parentController;

    public static string $ROOT_DIR;
    public static self $app;
    
    public function __construct() {
        new src\http\WebApplicationFirewall();

        self::$app = $this;
        self::$ROOT_DIR = dirname(__DIR__);

        $this->config       = new src\config\Config();
        $this->session      = new src\http\Session();
        $this->request      = new src\http\Request();
        
        $this->setConnection();

        $this->response     = new src\http\Response();
        $this->router       = new src\http\Router($this->request, $this);
        $this->view         = new src\http\View();
        $this->logger       = new src\utilities\Logger();

        $this->checkLanguage();
        $this->validateUserSession();
        $this->i18n         = new src\I18n();
        $this->appServices  = new ApplicationServices();
    }

    protected function setConnection() {
        $this->connection = Connection::getInstance(new MySQL());
    }

    public function checkLanguage() {
        if (IS_CLI) return;

        if (!$this->session->get('language')) $this->session->set('language', self::$app->config->get('locale')->default);
    }

    public function setLanguage(string $language): void {
        $cLanguage = new \app\models\LanguageModel();
        $search = $cLanguage->search(['Code' => $language]);

        if (empty($search)) $this->response->notFound();

        $this->session->set('language', $language);
    }

    private function validateUserSession() {
        if (IS_CLI) return;
        
        $validSession = (new UserModel())->hasActiveSession();

        $defaultUnauthenticatedRoute = $this->getConfig()->get('routes')->unauthenticated;

        if (!in_array($this->request->getPath(), $defaultUnauthenticatedRoute) && !$validSession) 
            $this->response->redirect(CoreFunctions::first($defaultUnauthenticatedRoute)->scalar);
    }

    public function getParentController(): src\Controller {
        return $this->parentController;
    }

    public function setParentController(src\Controller $controller): void {
        $this->parentController = $controller;
    }

    public function addSystemEvent(array|string $data): void {
        (new SystemEventModel(['Data' => is_string($data) ? $data : json_encode($data)]))->save(addMetaData: false);
    }

    public function log(string $message, bool $exit = false): void {
        echo date('Y-m-d H:i:s') . ' ' . $message . PHP_EOL;
        if ($exit) exit();
    }

    public function getService(string $service): Service {
        return $this->appServices->getService($service);
    }

    private function displayError(Throwable $applicationError) {
        $error = (new ControllerFactory(['handler' => 'Error']))->create();
        $this->setParentController($error);
        
        $error->setChildren(['Header']);
        $error->setChildData();

        $error?->index($applicationError);
        $this->logger->log($applicationError);
    }

    public function bootstrap(): void {
        try {
            $this->appServices->fetchAndRunServices();
            $this->router->resolve();
        } catch (Throwable $applicationError) {
            $this->displayError($applicationError);
        }
    }
    
}