<?php

namespace app\core\src\database\querybuilder\src;

trait SubQuery {

    public function subQuery(\Closure $callback): self {
        $this->upsertQuery(Constants::SUBQUERY_OPEN);
        call_user_func($callback, $this);
        $this->upsertQuery(Constants::SUBQUERY_CLOSE);
        return $this;
    }

}