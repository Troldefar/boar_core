<?php

namespace app\core\src\facades;

use \app\core\src\database\querybuilder\QueryBuilder;
use \app\core\src\exceptions\NotFoundException;

class DB {

    public function __construct(
        protected $data
    ) {}

    public function get(?string $key = null): mixed {
        return $key ? 
            (
                !isset($this->data[$key]) ? throw new NotFoundException('Invalid key') : $this->data[$key]
            )
            : $this->data;
    }

    public function getData(): array {
        return $this->data;
    }

    public static function table(string $table, string $class = __CLASS__, string|int $primaryKey = ''): QueryBuilder {
        return (new QueryBuilder($class, $table, $primaryKey));
    }

    public static function dump(array $tables = []): void {
        $config = app()->getConfig()->get('database');
        $dsn = \app\core\src\miscellaneous\CoreFunctions::last(explode(';', $config->dsn));
        $db = str_replace('dbname=', '', $dsn->scalar);
        
        $fileName = app()::$ROOT_DIR . '/' . time() . 'test.sql';

        exec(sprintf(
            'mysqldump -u %s -p%s -h 127.0.0.1 %s %s > %s',
            escapeshellarg($config->user),
            escapeshellarg($config->password),
            escapeshellarg($db),
            implode(' ', array_map('escapeshellarg', $tables)),
            escapeshellarg($fileName) 
        ));
    }

}