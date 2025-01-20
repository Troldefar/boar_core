<?php

namespace app\core\src\database\querybuilder\src;

trait PartitionQuery {

    public function partitionByClause(\closure $callback = null): self {
        call_user_func($callback, $this);
        $this->upsertQuery(Constants::SUBQUERY_CLOSE);

        return $this;
    }

    public function partitionBy(string $partitionBy): self {
       $this->upsertQuery(Constants::PARTITION_BY . ' ' . $partitionBy); 
       return $this;
    }

}