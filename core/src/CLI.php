<?php

/**
|----------------------------------------------------------------------------
| Base for CLI behaviour
|----------------------------------------------------------------------------
|
| @author RE_WEB
| @package core\src
|
*/

namespace app\core\src;

use \app\core\src\exceptions\NotFoundException;

class CLI {

    private array $colorCodes = [
        'red' => "\033[31m",
        'green' => "\033[32m",
        'yellow' => "\033[33m",
        'blue' => "\033[34m",
        'magenta' => "\033[35m",
        'cyan' => "\033[36m",
        'reset' => "\033[0m"
    ];

    private static function getJobs(): array {
        return [
            'CronjobScheduler' => function() {
                (new \app\core\src\scheduling\Cron())->run();
            },
            'DatabaseMigration' => function() {
                (new \app\core\src\database\Migration())->applyMigrations();
            },
            'WebsocketInit' => function() {
                \app\core\src\websocket\Websocket::getInstance();
            } 
        ];
    }

    private static function checkValidity(string $task): void {
        if (!array_key_exists($task, self::getJobs()))
            throw new NotFoundException('CLI TOOL NOT FOUND' . PHP_EOL);
    }

    public static function checkTask(string $task): void {
        self::checkValidity($task);

        exit(self::getJobs()[$task]());
    }

    public function printWithColor(string $message, string $color): void {
        $colorCode = $colorCodes[$color] ?? $this->colorCodes['reset'];
        
        echoCLI($colorCode . $message . $this->colorCodes['reset']);
    }
}
