<?php

namespace app\core\src\factories;

use \app\core\src\providers\ServiceProvider;

class ProviderFactory extends AbstractFactory {

    protected const PROVIDER_NAMESPACE = '\\app\providers\\';

    public function create(): ?ServiceProvider {
        $provider = self::PROVIDER_NAMESPACE . $this->getHandler();
        if (!$this->validateObject($provider)) return null;
        
        return new $provider();
    }

}