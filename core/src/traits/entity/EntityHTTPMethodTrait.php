<?php

namespace app\core\src\traits\entity;

trait EntityHTTPMethodTrait {

    private const INVALID_ARGUMENTS = 'Arguments can\'t be empty';
    private const INVALID_METHOD    = 'Your action does not exists within the valid scope of the [allowedHTTPMethods] property';

    protected array $allowedHTTPMethods;

    protected function setValidHTTPMethods(array $allowedHTTPMethods): void {
        $this->allowedHTTPMethods = $allowedHTTPMethods;
    }

    protected function validateHTTPAction(mixed $httpBody, string $httpRequestEntityMethod) {
        if (empty($httpBody) || empty($httpRequestEntityMethod)) 
			app()->getResponse()->notAllowed();
        
		if (!isset($this->allowedHTTPMethods) || !in_array($httpRequestEntityMethod, $this->allowedHTTPMethods))
            app()->getResponse()->notAllowed();
    }

}