<?php

namespace app\core\src\database\querybuilder\src;

trait DeleteQuery {

    public function delete(): self {
        $this->upsertQuery(Constants::DELETE_FROM . $this->table);
        return $this;
    }

    public function truncate(): self {
        $this->upsertQuery(Constants::TRUNCATE . $this->table);
        return $this;
    }

    public function truncateSpecific(string $table): self {
        $this->upsertQuery(Constants::TRUNCATE . $table);
        return $this;
    }

    // Additional delete-related methods can go here
}
