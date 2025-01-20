<?php

/**
 * Bootstrap Request 
 * AUTHOR: RE_WEB
 * @package app\core\Request
 */

 namespace app\core\src\http;

use \app\core\src\miscellaneous\CoreFunctions;

class Request {

    private object $requestConfig;
    private array $args = [];
    public object $clientRequest;
    
    private const SECONDS_THROTTLER = 60;
    private const METHOD_GET = 'get';
    private const METHOD_POST = 'post';
    private const REQUEST_MADE_KEY = 'requestsMade';
    private const INITIAL_INDEX_ATTEMPT = '-0';

    public array $redundantQuerySearchKeys = ['page', 'orderBy', 'sortBy'];

    protected string $allowedRequestAmount;
    protected string $allowedRequestMinutes;
    protected string $requestAttempts;
    protected string $attempts;
    protected string $allowedSecondsForRequestInterval;
    protected string $subtractedSeconds;

    public function __construct() {
        $this->clientRequest = $this->getCompleteRequestBody();
        $this->setArguments();
        $this->requestConfig = app()->getConfig()->get('request')->limit;
        $this->checkAmountOfRequest();
    }

    public function getPath(): string {
        $path = $this->getServerInformation()['REQUEST_URI'] ?? '/';
        $position = strpos($path, '?');
        if (!$position) return $path;
        return substr($path, 0, $position);
    }

    public function setArguments(): void {
        $this->args = explode('/', trim($this->getPath(), '/'));
    }

    public function getArguments(): array {
        return $this->args;
    }
    
    public function getArgument(int|string $index): mixed {
        return CoreFunctions::getIndex($this->args, $index);
    }

    public function getQueryParameters(): array {
        try {

            if (empty($this->getQueryString())) return [];

            $parameters = [];
            foreach (explode('&', $this->getServerInformation()['QUERY_STRING']) as $parameter) {
                if ($parameter === '' || $parameter === '_' || $parameter === '__') continue;
                [$param, $value] = explode('=', $parameter);
                $parameters[$param] = $value;
            }
            return $parameters;
        } catch (\Exception $e) {
            return [];
        }
    }
    
    public function getReferer(): string {
        return $this->getServerInformation()['HTTP_REFERER'];
    }
    
    public function getHost(): string {
        return $this->getServerInformation()['HTTP_HOST'];
    }

    public function method(): string {
        return strtolower($this->getServerInformation()['REQUEST_METHOD'] ?? self::METHOD_GET);
    }

    public function isGet(): bool {
        return $this->method() === self::METHOD_GET;
    }

    public function isPost(): bool {
        return $this->method() === self::METHOD_POST;
    }

    public function getCompleteRequestBody() {
        $obj = ['files' => $_FILES, 'body' => $this->getBody()];
        return (object)$obj;
    }

    public function getServerInformation() {
        return $_SERVER;
    }

    public function getBody(): object {
        $body = [];        
        $type = $this->method() === self::METHOD_GET ? INPUT_GET : INPUT_POST;
        foreach ($_REQUEST as $key => $_) {
            if (is_array($_)) foreach ($_ as $k => $v) $body[$key][] = (string)$v;
            else $body[$key] = $_;
        }
        return (object)$body;
    }

    public function getIP() {
        return $this->getServerInformation()['REMOTE_ADDR'] ?? php_sapi_name();
    }
    
    public function getPHPInput() {
        return json_decode(file_get_contents('php://input'));
    }

    public function setHeaders(array $headers): void {
        array_map(fn($header) => header($header), $headers);
    }

    private function checkAmountOfRequest(): void {
        if (IS_CLI) return;
        $this->setRatelimiting();
        $this->checkRateLimit();
    }

    protected function setRatelimiting(): void {
        $this->allowedRequestMinutes = $this->requestConfig->minutes;
        $this->allowedRequestAmount  = $this->requestConfig->amount;
    }

    protected function checkRateLimit(): void{
        $app = app();
        $session = $app->getSession();
        $this->requestAttempts = $session->get(self::REQUEST_MADE_KEY);
        if (!$this->requestAttempts) return;
        $this->validateCurrentSessionRateLimit();
        $this->handleCurrentSessionRateLimit();
    }

    protected function validateCurrentSessionRateLimit(): void {
        $this->updateCurrentSessionRateLimit();
        list($initialUnixSessionRateLimitInstance, $requestAttemptCounter) = explode('-', $this->requestAttempts);
        $this->subtractedSeconds = (strtotime('now') - (int)$initialUnixSessionRateLimitInstance);
        app()->getSession()->set(self::REQUEST_MADE_KEY, str_replace(('-'.$requestAttemptCounter), ('-'.($requestAttemptCounter+1)), $this->requestAttempts));
    }

    protected function handleCurrentSessionRateLimit(): void {
        if ($this->requestAttempts > $this->allowedRequestAmount) app()->getResponse()->requestLimitReached();
        if ($this->subtractedSeconds > $this->allowedSecondsForRequestInterval) app()->getSession()->set(self::REQUEST_MADE_KEY, $this->attempts);
    }

    protected function updateCurrentSessionRateLimit(): void {
        $this->allowedSecondsForRequestInterval = ($this->allowedRequestMinutes * self::SECONDS_THROTTLER);
        $this->attempts = ((string)strtotime('+'.$this->allowedRequestMinutes.' minutes') . self::INITIAL_INDEX_ATTEMPT);
        if (!$this->requestAttempts) app()->getSession()->set(self::REQUEST_MADE_KEY, $this->attempts);
    }

    public function getPageOffset(): int {
        $parameters = $this->getQueryParameters();
        return ($parameters['page'] ?? 0) * app()->getConfig()->get('frontend')->table->maximumPageInterval;
    }

    public function getOrderBy(): ?string {
        return $this->getQueryParameters()['orderBy'] ?? null;
    }

    public function getSortOrder(): ?string {
        return $this->getQueryParameters()['sortBy'] ?? null;
    }

    public function getPage(): ?string {
        return $this->getQueryParameters()['page'] ?? null;
    }

    public function checkQueryStart() {
        return (strpos($this->getQueryString(), '?') ? '' :  '?');
    }

    public function getQueryString(): string {
        return $this->getServerInformation()['QUERY_STRING'] ?? '';
    }

    public function getQuerySearchParameters(): array {
        $parameters = $this->getQueryParameters();
        
        foreach ($this->redundantQuerySearchKeys as $key) unset($parameters[$key]);
        foreach ($parameters as $key => &$value) {
            if ($value === '') unset($parameters[$key]);
            $value = urldecode($value);
        }

        return $parameters;
    }

    public function querySearchParametersAsString(): string {
        $str = '';
        $fields = $this->getQuerySearchParameters();
        foreach ($fields as $field => $value) 
            $str .= $field . '=' . $value . (array_key_last($fields) === $field ? '' : '&');
        return $str;
    }

    public function querySearchParamsAndValues(): string {
        $params = '';
        foreach ($this->getQuerySearchParameters() as $key => $value) 
            $params .= '&' . $key . '=' . $value;

        return $params;
    }

}