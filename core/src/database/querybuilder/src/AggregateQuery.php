<?php

namespace app\core\src\database\querybuilder\src;

trait AggregateQuery {

    public function sum(string $field, string $alias = 'sum'): self {
        $this->upsertQuery("SELECT SUM({$field}) AS {$alias} FROM {$this->table}");
        return $this;
    }
    
    public function avg(string $field, string $alias = 'avg'): self {
        $this->upsertQuery("SELECT AVG({$field}) AS {$alias} FROM {$this->table}");
        return $this;
    }
    
    public function min(string $field, string $alias = 'min'): self {
        $this->upsertQuery("SELECT MIN({$field}) AS {$alias} FROM {$this->table}");
        return $this;
    }
    
    public function max(string $field, string $alias = 'max'): self {
        $this->upsertQuery("SELECT MAX({$field}) AS {$alias} FROM {$this->table}");
        return $this;
    }

}