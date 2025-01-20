<?php

/**
|----------------------------------------------------------------------------
| Query builder base
|----------------------------------------------------------------------------
|
| @author RE_WEB
| @package core
|
*/

namespace app\core\src\database\querybuilder;

use app\core\Application;

abstract class QueryBuilderBase {

    protected string $query  = '';
    protected string $where  = '';
    protected string $fields = '';
    protected string $placeholders = '';

    protected string $lastQueryPart = '';

    protected array $args = [];

    protected Application $app;
    
    public function __construct(
        public string $class, 
        public string $table, 
        public string $keyID
    ) {
        $this->resetQuery();
        $this->app = app();
    }

    public function upsertQuery(string $query): void {
        $this->query .= $query;
        $this->insertLastQueryPart($query);
    }

    private function insertLastQueryPart(string $query) {
        $this->lastQueryPart = $query;
    }

    /**
     * LIFO observer for most recent query part
     * @return ?string
     */

    public function getLastQueryPart(): ?string {
        return $this->lastQueryPart ?? null;
    }

    private function checkQueryKey(string $key): void {
        if (!isset($this->args[$key])) return;
        
        debug('Your key: ' . $key . ' is already set in the current query');
    }

    public function updateQueryArguments(array $arguments): void {
        foreach ($arguments as $key => $value) {
            $this->checkQueryKey($key);

            $this->args[$key] = $value;
        }
    }

    public function updateQueryArgument(string $key, ?string $value): void {
        $this->checkQueryKey($key);
        
        $this->args[$key] = $value;
    }

    public function getQuery(): string {
        return $this->query;
    }

    public function getArguments(): array {
        return $this->args;
    }

    public function resetQuery() {
        $this->where = '';
        $this->query = '';
        $this->fields = '';
        $this->args = [];
        $this->placeholders = '';
    }

}