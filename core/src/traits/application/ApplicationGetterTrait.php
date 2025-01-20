<?php

namespace app\core\src\traits\application;

use \app\models\UserModel;

trait ApplicationGetterTrait {
    
    /**
    |----------------------------------------------------------------------------
    | Protected property getters
    |----------------------------------------------------------------------------
    |
    */

    public function getConfig(): \app\core\src\config\Config {
        return $this->config;
    }

    public function getConnection(): \app\core\src\database\Connection {
        return $this->connection;
    }

    public function getSession(): \app\core\src\http\Session {
        return $this->session;
    }

    public function getResponse(): \app\core\src\http\Response {
        return $this->response;
    }

    public function getRequest(): \app\core\src\http\Request {
        return $this->request;
    }

    public function getI18n(): \app\core\src\I18n {
        return $this->i18n;
    }

    public function getServiceProvider(): \app\core\src\services\ApplicationServices {
        return $this->appServices;
    }

    public function getView(): \app\core\src\http\View {
        return $this->view;
    }

    public function getLogger(): \app\core\src\utilities\Logger {
        return $this->logger;
    }

    public function getUser(): ?UserModel {
        if (!$this->session->get('user')) return null;
        return new UserModel($this->session->get('user'));
    }

}