<?php

/**
|----------------------------------------------------------------------------
| Schema
|----------------------------------------------------------------------------
|
| @author RE_WEB
| @package core\src\database
|
*/

namespace app\core\src\database;

use \app\core\src\database\adapters\Adapter;
use \app\core\src\database\table\Table;

use \app\core\src\utilities\Utilities;

use \app\models\MigrationModel;

class Schema {

    private function getAdapter(): Adapter {
        return app()->getConnection()->getAdapter();
    }

    public function down(string $table) {
        $query = $this->getAdapter()->dropTable . $table;
        (new MigrationModel())->query()->rawSQL($query)->run();
    }

    public function up($table, \Closure $callback): void {
        $table = new Table($table);
        $callback($table);
        $this->createIfNotExists($table);
    }

    public function createIfNotExists(Table $table) {
        $query = $this->getAdapter()->createTable . $table->getName() . '(';
        $columns = $table->getColumns();

        foreach ($columns as $columnKey => $columnOptions) {
            $columnType = $columnOptions->get('type');
            $query .= $columnOptions->queryString();
            $query .= Utilities::appendToStringIfKeyNotLast($columns, $columnKey);
        }

        $query .= ')';
        (new MigrationModel())->query()->rawSQL($query)->run();
    }

    public function table($table, \Closure $callback): void {
        $table = new Table($table);
        $callback($table);
        $query = $this->getAdapter()->alterTable . $table->getName() . ' ';

        foreach ($table->getColumns() as $columnKey => $columnOptions)
            $query .=
                ($columnOptions->queryString(isAlteringTable: true) . 
                Utilities::appendToStringIfKeyNotLast($table->getColumns(), $columnKey));
                
        (new MigrationModel())->query()->rawSQL($query)->run();
    }

}