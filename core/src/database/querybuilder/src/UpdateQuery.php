<?php

namespace app\core\src\database\querybuilder\src;

trait UpdateQuery {

    public function patch(array $fields, ?string $primaryKeyField = null, ?string $primaryKey = null): self {
        $this->upsertQuery("UPDATE {$this->table} SET ");

        foreach ($fields as $fieldKey => $fieldValue) {
            $this->updateQueryArgument($fieldKey, $fieldValue);
            $this->upsertQuery(" $fieldKey = :$fieldKey " . (array_key_last($fields) === $fieldKey ? '' : ','));
        }

        if ($primaryKeyField && $primaryKey) {
            $this->upsertQuery(Constants::WHERE . " $primaryKeyField = :primaryKey ");
            $this->updateQueryArgument('primaryKey', $primaryKey);
        }

        return $this;
    }

    // Additional update-related methods can go here
}
