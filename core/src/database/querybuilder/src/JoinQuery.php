<?php

namespace app\core\src\database\querybuilder\src;

trait JoinQuery {

    public function innerJoin(string $table, string $using = ''): self {
        if ($using !== '') $using = " USING({$using}) ";
        $this->upsertQuery(Constants::INNER_JOIN . " {$table} {$using} ");
        return $this;
    }

    public function innerJoins(array $innerJoinConditions): self {
        foreach ($innerJoinConditions as $table => $using)
            $this->innerJoin($table, $using);
        return $this;
    }

    public function leftJoin(string $table, string $on, array $and = []): self {
        $implodedAnd = (count($and) > 0 ? Constants::AND : '') . implode(Constants::AND, $and);
        $this->upsertQuery(Constants::LEFT_JOIN . "{$table} ON({$on}) {$implodedAnd} ");
        return $this;
    }

    public function rightJoin(string $table, string $on, array $and = []): self {
        $implodedAnd = (count($and) > 0 ? Constants::AND : '') . implode(Constants::AND, $and);
        $this->upsertQuery(Constants::RIGHT_JOIN . "{$table} ON({$on}) {$implodedAnd} ");
        return $this;
    }

    // Additional join-related methods can go here
}
