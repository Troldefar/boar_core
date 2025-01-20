<?php

/**
 * Bootstrap Session 
 * AUTHOR: RE_WEB
 * @package app\core\Session
 */

namespace app\core\src\http;

final class Session {

    protected const FLASH_ARRAY = 'FLASH_MESSAGES';

    public function __construct() {
        $this->checkFlashMessages();
    }

    public function checkFlashMessages() {
        $flashMessages = $this->getAllFlashMessages();
        foreach ($flashMessages as &$flashMessage) $flashMessage['remove'] = true;
        $_SESSION[self::FLASH_ARRAY] = $flashMessages;
    }

    public function setFlashMessage(string $key, string $message) {
        $_SESSION[self::FLASH_ARRAY][$key] = [
            'remove' => false,
            'value' => $message
        ];
    }

    public function getFlashMessage(string $key): string {
        return $_SESSION[self::FLASH_ARRAY][$key]['value'] ?? '';
    }

    public function getAllFlashMessages(): array {
        return $_SESSION[self::FLASH_ARRAY] ?? [];
    }

    public function set(string $key, mixed $value): void {
        $_SESSION[$key] = $value;
    }

    public function get(string $key): string|bool {
        return $_SESSION[$key] ?? false;
    }

    public function unset(string|array $key): void {
        if (is_string($key)) $key = [$key];
        foreach ($key as $unsetKey) unset($_SESSION[self::FLASH_ARRAY][$unsetKey]);
    }

    public function getAll(): array {
        return $_SESSION;
    }

    public function nullAll(): void {
        foreach ($this->getAll() as $key => &$value) $value = null;
    }

    public function restart(): void {
        session_destroy();
        session_start();
    }

    public function __destruct() {
        $flashMessages = $this->getAllFlashMessages();
        foreach ($flashMessages as $key => &$flashMessage) if ($flashMessage['remove']) $this->unset($flashMessages[$key]);
        $_SESSION[self::FLASH_ARRAY] = $flashMessages;
    }

}