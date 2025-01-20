<?php

/**
|----------------------------------------------------------------------------
| Table building
|----------------------------------------------------------------------------
| 
| @author RE_WEB
| @package \app\core\src\database\table
|
*/

namespace app\core\src\database\table;

use \app\core\src\miscellaneous\CoreFunctions;

class Table {

    protected string $name;
    protected array $columns = [];
    protected string $columnName;

    public const COMPLETED_COLUMN   = 'Completed';
    public const DELETED_AT_COLUMN  = 'DeletedAt';
    public const CREATED_AT_COLUMN  = 'CreatedAt';
    public const STATUS_COLUMN      = 'Status';
    public const SORT_ORDER_COLUMN  = 'SortOrder';
    public const ENTITY_TYPE_COLUMN = 'EntityType';
    public const ENTITY_ID_COLUMN   = 'EntityID';
    public const SORT_ASC           = 'ASC';
    public const SORT_DESC          = 'DESC';

    private const INT_COLUMN_TYPE       = 'INT';
    private const FLOAT_COLUMN_TYPE     = 'FLOAT';
    private const VARCHAR_COLUMN_TYPE   = 'VARCHAR';
    private const TEXT_COLUMN_TYPE      = 'TEXT';
    private const UUID_COLUMN_NAME      = 'UUID';
    private const BOOLEAN_COLUMN_TYPE   = 'BOOLEAN';
    private const ENUM_COLUMN_TYPE      = 'ENUM';
    private const PRIMARY_KEY           = 'PRIMARY_KEY';
    private const TIMESTAMP             = 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP';

    private const FOREIGN_KEY_TYPE = 'FOREIGN_KEY';

    public const  MAX_COLUMN_LENGTH      = 255;
    private const DEFAULT_UUID_LENGTH   = 128;
    private const DEFAULT_VARCHAR_LIMIT = 75;
    private const DEFAULT_INTEGER_LIMIT = 10;

    public function __construct($name) {
        $this->name = $name;
    }

    public function createColumn(string $columnName, string $type, array $options = []) {
        $this->columns[] = new Column($columnName, $type, $options);
        $this->setColumnName($columnName);
    }

    public function increments(string $columnName): self {
        $this->createColumn($columnName, self::INT_COLUMN_TYPE, ['AUTO_INCREMENT' => null]);
        return $this;
    }

    public function varchar(string $columnName, int $length = self::DEFAULT_VARCHAR_LIMIT): self {
        $this->createColumn($columnName, self::VARCHAR_COLUMN_TYPE, ['LENGTH' => '('.$length.')']);
        return $this;
    }

    public function text(string $columnName): self {
        $this->createColumn($columnName, self::TEXT_COLUMN_TYPE);
        return $this;
    }

    public function integer(string $columnName, int $length = self::DEFAULT_INTEGER_LIMIT): self {
        $this->createColumn($columnName, self::INT_COLUMN_TYPE, ['LENGTH' => '('.$length.')']);
        return $this;
    }

    public function float(string $columnName, int $length = self::DEFAULT_INTEGER_LIMIT): self {
        $this->createColumn($columnName, self::FLOAT_COLUMN_TYPE, ['LENGTH' => '('.$length.')']);
        return $this;
    }

    public function primaryKey(string $columnName): self {
        $this->createColumn($columnName, self::PRIMARY_KEY);
        return $this;
    }

    public function timestamp(string $columnName = 'CreatedAt'): self {
        $this->createColumn($columnName, self::TIMESTAMP);
        return $this;
    }

    public function uuid(): self {
        $this->createColumn(self::UUID_COLUMN_NAME, self::VARCHAR_COLUMN_TYPE, ['LENGTH' => '('.self::DEFAULT_UUID_LENGTH.')']);
        return $this;
    }

    public function boolean(string $columnName): self {
        $this->createColumn($columnName, self::BOOLEAN_COLUMN_TYPE);
        return $this;
    }

    public function foreignKey(string $columnName, string $foreignTable, string $foreignColumn = ''): self {
        if (!$foreignColumn) $foreignColumn = $columnName;
        $this->columns[] = new ForeignKey($columnName, $foreignTable, $foreignColumn);
        return $this;
    }

    public function getLastForeignKey(): ?object {
        $lastForeignKey = null;
        foreach ($this->getColumns() as $column)
            if ($column->get('type') === SELF::FOREIGN_KEY_TYPE) $lastForeignKey = $column;
            
        return $lastForeignKey;
        
    }

    public function onDeleteCascade(): self {
        $foreignKeyCheck = $this->getLastForeignKey();
        if (!$foreignKeyCheck) return $this;

        $foreignKeyCheck->addConstraint(['ON DELETE CASCADE' => null]);
        return $this;
    }

    public function onUpdateCascade(): self {
        $foreignKeyCheck = $this->getLastForeignKey();
        if (!$foreignKeyCheck) return $this;

        $foreignKeyCheck->addConstraint(['ON UPDATE CASCADE' => null]);
        return $this;
    }

    public function enum(string $columnName): self {
        $this->createColumn($columnName, self::ENUM_COLUMN_TYPE);
        return $this;
    }

    private function getFormattedColumnIndexName() {
        return strtolower(CoreFunctions::last($this->getColumns())->get('name').'_idx');
    }

    public function addIndex(): self {
        $this->createColumn($this->getFormattedColumnIndexName(), 'ADD_INDEX', ['name' => CoreFunctions::last($this->getColumns())->get('name')]);
        return $this;
    }

    public function dropIndex(string $index): self {
        $this->createColumn($index, 'DROP_INDEX'); 
        return $this;
    }

    public function add() {
        CoreFunctions::last($this->getColumns())->setType('ADD_COLUMN');
    }

    public function drop() {
        CoreFunctions::last($this->getColumns())->setType('DROP_COLUMN');
    }

    public function dropColumns(array|string $columns) {
        if (is_string($columns)) (array)$columns;
        
        foreach ( $columns as $column )
            new Column($column, 'drop');
    }

    public function getColumnName() {
        return $this->columnName;
    }

    public function setColumnName(string $columnName) {
        $this->columnName = $columnName;
    }

    public function getColumns(): array {
        return $this->columns;
    }

    public function getName(): string {
        return $this->name;
    }

}