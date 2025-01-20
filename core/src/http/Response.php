<?php

/**
 * Bootstrap Response 
 * AUTHOR: RE_WEB
 * @package app\core\src\Response
 */

 namespace app\core\src\http;

final class Response {

    private const HTTP_OK = 200;
    private const HTTP_CREATED = 201;
    private const HTTP_LOCATION_PERM = 301;
    private const HTTP_LOCATION_TEMP = 302;
    private const HTTP_BAD_REQUEST = 400;
    private const HTTP_AUTHENTICATION_FAILED = 401;
    private const HTTP_UNAUTHORIZED = 403;
    private const HTTP_NOT_FOUND = 404;
    private const HTTP_METHOD_NOT_ALLOWED = 405;
    private const HTTP_DATA_CONFLICT = 409;
    private const HTTP_TO_MANY_REQUEST = 429;

    public function setStatusCode(int $code) {
        http_response_code($code);
    }

    public function redirect(string $location) {
        $path = app()->getRequest()->getPath();
        if($path === '/admin') app()->getSession()->set('redirect', $path);

        $this->setStatusCode(self::HTTP_LOCATION_PERM);
        header('Location: ' . $location);
    }

    public function redirectClient(string $location) {
        $this->setResponse(self::HTTP_OK, ['redirect' => $location]);
    }
    
    public function setContentType(string $type) {
        header('Content-Type: ' . $type);
    }
    
    public function setResponse(int $code, array $message = []) {
        if (empty($message)) $message = $this->returnStdSuccesMessage();

        $this->setStatusCode($code);
        $this->setContentType('application/json');
        
        exit(json_encode($message));
    }

    public function returnStdSuccesMessage(): array {
        return ['responseJSON' => 'Success'];
    }

    public function returnMessage(string $message): array {
        return [$message];
    }

    public function customOKResponse(string $key, string|array $message = '') {
        $this->setResponse(self::HTTP_OK, [$key => $message]);
    }

    public function customResponse(string $key, string|array $message = '', int $state = self::HTTP_OK) {
        $this->setResponse($state, [$key => $message]);
    }

    public function ok(string|array $message = '') {
        if (empty($message)) $message = $this->returnStdSuccesMessage();
        
        $this->setResponse(self::HTTP_OK, ['responseJSON' => $message]);
    }

    public function created() {
        $this->setResponse(self::HTTP_CREATED);
    }

    public function badToken() {
        $this->setResponse(self::HTTP_BAD_REQUEST, $this->returnMessage('Bad token'));
    }

    public function unauthorized(string $message = 'Unauthorized') {
        $this->setResponse(self::HTTP_AUTHENTICATION_FAILED, $this->returnMessage($message));
    }

    public function notAllowed() {
        $this->setResponse(self::HTTP_UNAUTHORIZED, $this->returnMessage('Not allowed'));
    }

    public function notFound(string $message = 'Resource not found') {
        $this->setResponse(self::HTTP_NOT_FOUND, [$message]);
    }

    public function methodNotAllowed() {
        $this->setResponse(self::HTTP_METHOD_NOT_ALLOWED, $this->returnMessage('Method not allowed'));
    }

    public function dataConflict(string $message = 'Invalid input. Please try something else') {
        $this->setResponse(self::HTTP_DATA_CONFLICT, $this->returnMessage($message));
    }

    public function requestLimitReached() {
        $this->setResponse(self::HTTP_TO_MANY_REQUEST, $this->returnMessage('Too many requests')); 
    }

}