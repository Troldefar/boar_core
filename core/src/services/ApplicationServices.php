<?php

/**
|----------------------------------------------------------------------------
| Service trait
|----------------------------------------------------------------------------
|
| 
| @author RE_WEB
| @package \app\core\src\traits\application
|
*/

namespace app\core\src\services;

use \app\core\src\factories\ProviderFactory;

use \app\core\src\File;

use \app\core\src\providers\ServiceProvider;

use \app\core\src\contracts\Service;

use \app\core\src\exceptions\NotFoundException;

class ApplicationServices {

    private const PROVIDER_DIR = '/providers';
    private const INVALID_SERVICE_NAME = 'Service not found';

    private array $services = [];

    public function fetchAndRunServices(): void {
        array_map(function($file) {
            $this->createObject(
                preg_replace('/' . File::PHP_EXTENSION . '/', '', $file)
            )->register();
        }, $this->getProviderDir());
    }

    public function bind(string $service): void {
       $this->setServices($service, new $service());
    }

    public function getServices(): array {
        return $this->services;
    }

    public function getService(string $service): Service {
        if (!isset($this->services[$service])) throw new NotFoundException(self::INVALID_SERVICE_NAME);
        
        return $this->services[$service];
    }

    private function setServices(string $key, Service $service): void {
        $this->services[$key] = $service;
    }

    private function createObject(string $file): ServiceProvider {
        return (new ProviderFactory(['handler' => $file]))->create();
    }

    private function getProviderDir(): array {
        return File::getNonHiddenFiles(app()::$ROOT_DIR . self::PROVIDER_DIR);
    }

}