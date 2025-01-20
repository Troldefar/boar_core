<?php

namespace app\core\src\http;

class WebApplicationFirewall {
    protected $inputData;

    protected const SQL_MESSAGE = 'SQL Injection attempt detected';
    protected const XSS_MESSAGE = 'XSS attempt detected';
    protected const DIE_MESSAGE = 'Request blocked: ';

    public function __construct() {
        $this->inputData = $_REQUEST;
        $this->sanitizeInput($this->inputData);
        $this->detectSQLInjection();
        $this->detectXSS();
    }

    protected function sanitizeInput(array $requestData) {
        foreach ($requestData as $key => $value)
            if (is_array($requestData[$key])) {
                unset($this->inputData[$key]);
                $this->sanitizeInput($requestData[$key]);
            } else {
                $this->inputData[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
    }

    protected function detectSQLInjection() {
        foreach ($this->inputData as $value) {
            if (
                preg_match("/SELECT.*FROM|UNION.*SELECT|DROP.*TABLE|INSERT.*INTO|DELETE.*FROM|ALTER.*TABLE/i", $value) ||
                preg_match("/\b(AND|OR)\b.+\b(HAVING|FROM|JOIN|INTO|WHERE)\b/i", $value)
            ) $this->blockRequest(self::SQL_MESSAGE);
        }
    }

    protected function detectXSS() {
        foreach ($this->inputData as $value) {
            if (
                preg_match("/<script|<img|onerror|javascript:|document.cookie|eval\(|<iframe/i", $value) ||
                preg_match("/\b(alert|confirm|prompt)\(/i", $value)
            ) $this->blockRequest(self::XSS_MESSAGE);
        }
    }

    protected function blockRequest($reason) {
        die(self::DIE_MESSAGE . $reason);
    }
}