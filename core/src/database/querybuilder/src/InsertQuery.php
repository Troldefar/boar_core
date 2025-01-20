<?php

namespace app\core\src\database\querybuilder\src;

trait InsertQuery {

    public function insertBatch(array $fields, array $data): self {
        $this->upsertQuery("INSERT INTO {$this->table} (" . implode(',', $fields) . ") VALUES " . implode(',', array_map(function($row) {
            return '(' . implode(',', $row) . ')';
        }, $data)));
        return $this;
    }

    public function create(array|object $fields): self {
        $this->preparePlaceholdersAndBoundValues((array)$fields, 'insert');
        $this->upsertQuery("INSERT INTO {$this->table} ({$this->fields}) VALUES ({$this->placeholders})");
        return $this;
    }

    public function preparePlaceholdersAndBoundValues(array $fields, string $fieldSetter): self {
        foreach ($fields as $key => $field) {
            $this->fields .= $key . (array_key_last($fields) === $key ? '' : ',');
            $this->placeholders .= ($fieldSetter === 'insert' ? '' : $key . '=') . "?" . (array_key_last($fields) === $key ? '' : ',');
            $this->args[] = $field;
        }
        return $this;
    }

    public function initializeNewEntity(array $data): void {
        $this->bindValues($data);
        $this->create($data);
        $this->run();
    }
    
    public function bindValues(array $arguments): void {
        foreach($arguments as $selector => $value) {
            $this->upsertQuery((array_key_first($arguments) === $selector ? Constants::WHERE : Constants::AND) . $selector . Constants::BIND . $selector);
            $this->setArgumentPair($selector, $value);
        }
    }

    public function valueToPlaceholder(array $fields): self {
        foreach ($fields as $fieldKey => $fieldValue) {
            $this->upsertQuery(':' . ( array_key_last($fields) === $fieldKey ? $fieldKey : $fieldKey . ',' ));
            $this->updateQueryArgument($fieldKey, $fieldValue);
        }
        return $this;
    }

    public function setArgumentPair(string $key, mixed $value): self {
        $this->updateQueryArgument($key, $value);
        return $this;
    }

    public function insertWhereClause(): self {
        $this->upsertQuery(Constants::WHERE);
        return $this;
    }

    public function isolate(\Closure $callback): self {
        $this->upsertQuery(Constants::SUBQUERY_OPEN);
        call_user_func($callback, $this);
        $this->upsertQuery(Constants::SUBQUERY_CLOSE);
        return $this;
    }

    // Additional insert-related methods can go here
}