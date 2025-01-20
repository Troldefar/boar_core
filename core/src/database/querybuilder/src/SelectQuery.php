<?php

namespace app\core\src\database\querybuilder\src;

use \app\core\src\database\table\Table;
use \app\core\src\miscellaneous\CoreFunctions;
use \app\core\src\utilities\Parser;


trait SelectQuery {

   public function on(string $field): self {
       $this->upsertQuery(" ON {$field} ");
       return $this;
   }

    public function select(array $fields = ['*']): self {
        $this->upsertQuery(Constants::SELECT . implode(', ', $fields) . Constants::FROM . $this->table);
        return $this;
    }

    public function selectFieldsFrom(array $fields, string $from = ''): self {
        $this->upsertQuery(Constants::SELECT . implode(', ', $fields) . Constants::FROM . $from);
        return $this;
    }

    public function selectFields(array $fields): self {
        $this->upsertQuery(Constants::SELECT . implode(', ', $fields));
        return $this;
    }

    public function selectFromSubQuery(string $fields = '*'): self {
        $this->upsertQuery(Constants::SELECT . $fields . Constants::FROM);
        return $this;
    }

    public function distinct(string $distinct): self {
        $this->upsertQuery(Constants::DISTINCT . $distinct);
        return $this;
    }

    public function distinctFrom(): self {
        $this->upsertQuery(Constants::SELECT . Constants::DISTINCT . $this->fields . Constants::FROM . $this->table);
        return $this;
    }

    public function count(string $count, string $countName = 'count'): self {
        $this->upsertQuery(Constants::COUNT . " ({$count}) " . Constants::AS . " {$countName} ");
        return $this;
    }

    public function countFrom(string $count, string $countName = 'count'): self {
        $this->upsertQuery(Constants::SELECT . ' ' . Constants::COUNT . "({$count}) " . Constants::AS . " {$countName} " . Constants::FROM . " {$this->table}");
        return $this;
    }

    public function rawSQL(string $sql): self {
        $this->upsertQuery($sql);
        return $this;
    }

    public function as(string $as): self {
        $this->upsertQuery(Constants::AS . $as);
        return $this;
    }

    public function having(array $conditions): self {
        foreach ($conditions as $field => $value) {
            $this->upsertQuery(Constants::HAVING . " {$field} = :{$field}");
            $this->updateQueryArgument($field, $value);
        }
        return $this;
    }

    public function with(string $temp): self {
        $this->upsertQuery(Constants::WITH . $temp . Constants::AS);
        return $this;
    }

    public function over(): self {
       $this->upsertQuery(' OVER ( '); 
       return $this;
    }

    public function appendParenthesisStart(): self {
        $this->upsertQuery(" ( ");
        return $this;
    }

    public function appendParenthesisEnd(): self {
        $this->upsertQuery(" ) ");
        return $this;
    }

    public function union() {
        $this->upsertQuery(Constants::UNION);
    }

    public function unionAll() {
        $this->upsertQuery(Constants::UNION_ALL);
    }

    // Additional select-related methods can go here
}