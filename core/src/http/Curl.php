<?php

/**
|----------------------------------------------------------------------------
| Bootstrap CURL
|----------------------------------------------------------------------------
|
| @author RE_WEB
| @package app\core\src
|
*/

namespace app\core\src\http;

final class Curl {

	private const POST_METHOD = 'post';
	private const GET_METHOD = 'get';
	private const AUTHENTICATION_METHOD_KEY = 'authenticationMethod';
	private const AUTHENTICATION_CREDENTIALS_KEY = 'credentials';

	protected string $url = '';
	protected string $method = 'get';
	
	protected $content;
	protected $handler = null;
	protected $info = [];
	protected $data = [];

	protected array $headers = [];
	protected array $auth = [];
	protected array $responseCookies;
	protected array $requestCookies;
	
	public function setUrl(string $url = ''): self {
		$this->url = $url;
		return $this;
	}
	
	public function setData(array $data = [], bool $jsonEncode = false): self {
		$this->data = ($jsonEncode ? json_encode($data) : $data);
		return $this;
	}
	
	public function setMethod(string $method = self::GET_METHOD): self {
		$this->method = $method;
		return $this;
	}
	
	public function setHeaders(array $headers): self {
		foreach ($headers as $header) $this->headers[] = $header;
		return $this;
	}

	public function setAuthenticationMechanism(string $authenticationMethod, string|array $credentials): self {
		$this->auth = [
			self::AUTHENTICATION_METHOD_KEY => $authenticationMethod,
			self::AUTHENTICATION_CREDENTIALS_KEY => $credentials
		];
		return $this;
	}

	protected function checkHandler(): void {
		if ($this->handler === null) $this->handler = curl_init();
	}

	protected function initializeDefaultHandlerProperties(): void {
		curl_setopt_array($this->handler, [
			CURLOPT_URL => $this->url,
			CURLOPT_HTTPHEADER => $this->headers,
			CURLOPT_HEADER => 1,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true
		]);
	}

	protected function prepareRequest(bool $appendOnlyFirstDataIndex): void {
		$this->initializeDefaultHandlerProperties();

		switch (strtolower($this->method)) {
			case self::POST_METHOD:
				curl_setopt_array($this->handler, [
					CURLOPT_POST => count((array)$this->data),
					CURLOPT_POSTFIELDS => (!$appendOnlyFirstDataIndex ? $this->data : $this->data[array_key_first($this->data)])
				]);
			break;
			default:
				$urlWithQuery = $this->url . (count($this->data) ? '?' . http_build_query($this->data) : '');
				curl_setopt($this->handler, CURLOPT_URL, $urlWithQuery);
				curl_setopt($this->handler, CURLOPT_HTTPGET, true);
			break;
		}

		$this->checkAuthenticationMechanism();
	}

	protected function checkAuthenticationMechanism() {
		if (empty($this->auth)) return;
		curl_setopt($this->handler, CURLOPT_HTTPAUTH, $this->auth[self::AUTHENTICATION_METHOD_KEY]);
		curl_setopt($this->handler, CURLOPT_USERPWD, $this->auth[self::AUTHENTICATION_CREDENTIALS_KEY]);
	}

	private function sendAndReceiveRequest(): void {
		$this->content = curl_exec($this->handler);
		$this->info = curl_getinfo($this->handler);
		$this->setResponseCookies();
	}

	private function setResponseCookies() {
		preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $this->content, $matches);
		$this->responseCookies = $matches[1];
	}

	private function checkCookies() {
		if (empty($this->requestCookies)) return;
		curl_setopt($this->handler, CURLOPT_COOKIE, implode(',', $this->requestCookies));
	}

	public function send(bool $appendOnlyFirstDataIndex = false): self {
		try {
			$this->checkHandler();
			$this->prepareRequest($appendOnlyFirstDataIndex);
			$this->checkCookies();
			$this->sendAndReceiveRequest();
			return $this;
		} catch(\Exception $e) {
			debug($e);
		}
	}
	
	public function getData() {
		return $this->data;
	}

	public function getInfo(): array {
		return $this->info;
	}

	public function getContent(): string|bool {
		return $this->content;
	}

	public function getResponseCookies(): array {
		return $this->responseCookies;
	}

	public function getRequestCookies(): array {
		return $this->requestCookies;
	}

	public function setRequestCookies(array $cookies): self {
		$this->requestCookies = $cookies;
		return $this;
	}

	public function close(): void {
	   curl_close($this->handler);
	   $this->headers = $this->data = $this->auth = $this->info = [];
	   $this->handler = $this->content = $this->info = null;
	}

	public function debug(): void {
		echo '<pre>';
		var_dump($this);
	}
	
}