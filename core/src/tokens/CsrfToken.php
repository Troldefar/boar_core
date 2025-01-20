<?php

/**
 * Bootstrap CsrfToken
 * Hardware based validation
 * AUTHOR: RE_WEB
 * @package app\core\token
 */

namespace app\core\src\tokens;

class CsrfToken {

    private string $formTokenLabel = 'eg-csrf-token-label';
    private string $sessionTokenLabel = 'EG_CSRF_TOKEN_SESS_IDX';
    private string $hashAlgo = 'sha256';
    private object $post;
    private object $session;
    private array  $server;
    private array  $excludeUrl = [];
    private bool   $hmac_ip = true;

    public function __construct($excludeUrl = null) {
        if (!is_null($excludeUrl)) $this->excludeUrl = $excludeUrl;
        $app = app();
        $this->post = $app->getRequest()->getBody();
        $this->server = $app->getRequest()->getServerInformation();
        $this->session = $app->getSession();
    }

    public function setToken(): void {
        $this->session->set('csrf', $this->generateRandom());
    }

    public function getToken(): string {
        if (!$this->session->get('csrf')) $this->setToken();
        $token = $this->hmac_ip !== false ? $this->hMacWithIp($this->session->get($this->sessionTokenLabel)) : $this->session->get($this->sessionTokenLabel);
        return $token;
    }

    protected function generateRandom(): string {
        return bin2hex(openssl_random_pseudo_bytes(32));
    }

    public function insertHiddenToken(): string {
        return "<input type=\"hidden\"" . " name=\"" . $this->xssafe($this->formTokenLabel) . "\"" . " value=\"" . $this->xssafe($this->getToken()) . "\"" . " />";
    }

    public function xssafe($data, $encoding = 'UTF-8'): string {
        return htmlspecialchars($data, ENT_QUOTES | ENT_HTML401, $encoding);
    }

    private function hMacWithIp(string $token): string {
        return hash_hmac($this->hashAlgo, app()->getConfig()->get('tokens')->csrf->hMacData, $token);
    }

    private function getCurrentRequestUrl(): string {
        return $this->server['REQUEST_SCHEME'] ?? 'https' . '://' . $this->server['HTTP_HOST'] . $this->server['REQUEST_URI'];
    }

    public function validate(): bool {
        if (!in_array($this->getCurrentRequestUrl(), $this->excludeUrl)) 
            if (!empty($this->post)) 
                return $this->validateRequest();
        return false;
    }

    public function isValidRequest(): bool {
        $isValid = false;
        $currentUrl = $this->getCurrentRequestUrl();
        if (!in_array($currentUrl, $this->excludeUrl))
            if (!empty($this->post))
                $isValid = $this->validateRequest();
        return $isValid;
    }
    
    public function validateRequest(): bool {
        if ($this->session->get($this->sessionTokenLabel)) return false;
        if (!empty($this->post->{$this->formTokenLabel})) $token = $this->post->{$this->formTokenLabel};
        else return false;
        $expected = $this->hmac_ip ? $this->hMacWithIp($this->session->get($this->sessionTokenLabel)) : $this->session->get($this->sessionTokenLabel);
        return hash_equals($token, $expected);
    }

    public function unsetToken() {
        if ($this->session->get($this->sessionTokenLabel)) $this->session->unset($this->sessionTokenLabel);
    }

}