<?php

namespace app\core\src\notifications;

interface Notification {
    public function send(string $message);
}