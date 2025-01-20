<?php

namespace app\core\src\database\table;

class Column {

    protected const PRIMARY_KEY = 'PRIMARY_KEY';
    protected const FOREIGN_KEY = 'FOREIGN_KEY';
    protected const DROP_COLUMN = 'DROP_COLUMN';
    protected const DROP_TABLE  = 'DROP_TABLE';
    protected const ADD_COLUMN  = 'ADD_COLUMN';
    protected const ADD_INDEX   = 'ADD_INDEX';
    protected const DROP_INDEX  = 'DROP_INDEX';
    protected const ON_DELETE_CASCADE  = 'ON_DELETE_CASECADE';
    protected const ON_UPDATE_CASCADE  = 'ON_UPDATE_CASCADE';

    protected string $name;
    protected string $type;
    protected string $previousType;
    protected string $optionString;

    protected array  $options = [];
    protected array  $exclude = ['LENGTH'];

    public function __construct(string $name, string $type, array $options = []) {
        $this->name = $name;
        $this->type = $type;
        $this->options = $options;
        $this->optionString = '';
    }

    public function get(string $key): string|array {
        return $this->{$key} ?? 'Invalid';
    }

    public function setType(string $type) {
        $this->previousType = $this->type;
        $this->type = $type;
    }

    private function getOptionsArray(): array {
        return $this->options;
    }

    private function getForeignKeyPrefix(): string {
        return 'fk_';
    }

    private function setOptionsString() {
        foreach ($this->get('options') as $optionKey => $option)
            $this->optionString .= ' ' . (in_array($optionKey, $this->exclude) ? '' : $optionKey) . ' ' . ($option ?? '');
    }

    private function getOptionsString() {
        return $this->optionString;
    }

    public function queryString(bool $isAlteringTable = false) {
        try {
            
            $this->setOptionsString();

            switch ($this->type) {
                case self::PRIMARY_KEY:
                    $query = " PRIMARY KEY ($this->name) ";
                    break;
                case self::FOREIGN_KEY:
                    $query = 
                        ( $isAlteringTable ? 'ADD CONSTRAINT ' . $this->getForeignKeyPrefix() . $this->foreignColumn : '' ) . 
                        " FOREIGN KEY ($this->name) REFERENCES $this->foreignTable($this->foreignColumn) " . $this->getOptionsString();
                    break;
                case self::DROP_COLUMN:
                    $query = 'DROP COLUMN ' . $this->type . ' ' . $this->name;
                    break;
                case self::ADD_COLUMN:
                    $query = 'ADD COLUMN ' . $this->name . ' ' . $this->previousType . $this->getOptionsString();
                    break;
                case self::ADD_INDEX:
                    $columnName = $this->getOptionsArray()['name'] ?? 'Invalid';
                    $query = " INDEX $this->name($columnName) ";
                    break;
                case self::DROP_INDEX:
                    $query = " DROP INDEX $this->name ";
                    break;
                default:
                    $query = $this->name . ' ' .  $this->type . $this->getOptionsString();
                    break;
            }

            return $query;
        } catch (\Exception $e) {
            throw new \app\core\src\exceptions\NotFoundException("Column generation failed: " . $e->getMessage());
        }
    }

}