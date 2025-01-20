<?php

/**
|----------------------------------------------------------------------------
| Entity query trait
|----------------------------------------------------------------------------
|
| This file is meant as a convenient way to do mundane queries and abstract 
| away some of the repetitive tasks
| 
| @author RE_WEB
| @package \app\core\src\traits
|
*/

namespace app\core\src\traits\entity;

use \app\core\src\database\Entity;
use \app\core\src\database\querybuilder\QueryBuilder;
use \app\core\src\database\table\Table;
use \app\core\src\database\EntityMetaData;
use \app\models\FileModel;
use \app\models\LanguageModel;

trait EntityQueryTrait {

    private const INVALID_ENTITY_DATA   = 'Data can not be empty';
    private const INVALID_ENTITY_STATUS = 'This entity does not have a status';
    private const FIND_OR_CREATE_NEW_DATA_ENTRY = ' was created due to a data entry';
    private const INVALID_ENTITY = 'Invalid entity';
    private const SQL_IS_NOT_NULL = 'IS NOT NULL';
    private const SQL_FETCH_MODE_FETCH = 'fetch';

    public function patchEntity(): self {
        $this->getQueryBuilder()->patch(fields: $this->data, primaryKeyField: $this->getKeyField(), primaryKey: $this->key())->run(fetchMode: self::SQL_FETCH_MODE_FETCH);
        return $this;
    }

    public function patchField(array|object $data): self {
        $data = (array)$data;

        unset($data['eg-csrf-token-label']);
        unset($data['action']);
        
        $this->getQueryBuilder()->patch(fields: $data, primaryKeyField: $this->getKeyField(), primaryKey: $this->key())->run(fetchMode: self::SQL_FETCH_MODE_FETCH);
        return $this;
    }
    
    public function createEntity() {
        $this->getQueryBuilder()->create(fields: $this->data)->run();
        $this->setKey(app()->getConnection()->getLastInsertedID());
        return $this;
    }

    public function init() {
		return $this->getQueryBuilder()->initializeNewEntity(data: $this->data);
	}

    public function softDelete(): self {
		$this->patchField([Table::DELETED_AT_COLUMN => new \DateTime(datetime: 'Y-m-d H:i:s')]);
        return $this;
	}

    public function restore(): self {
	    $this->patchField([Table::DELETED_AT_COLUMN => null]);
        return $this;
	}

    public function query(): QueryBuilder {
        return (new QueryBuilder(class: get_called_class(), table: $this->getTableName(), keyID: $this->getKeyField()));
    }

    public function delete(): array|object {
        return $this->getQueryBuilder()->delete()->where(arguments: [$this->getKeyField() => $this->key()])->run();
    }

    public function deleteWhere(array $where): array|object {
        return $this->getQueryBuilder()->delete()->where(arguments: $where)->run(); 
    }

     public function truncate(): array|object {
        return $this->getQueryBuilder()->truncate()->run();
    }

     public function trashed(): array|object {
        return $this->getQueryBuilder()->select()->where(arguments: [Table::DELETED_AT_COLUMN => self::SQL_IS_NOT_NULL])->run();
    }

    public function getQueryBuilder(?string $table = null): QueryBuilder {
        $table ??= $this->getTableName();
        return new QueryBuilder(class: get_called_class(), table: $table, keyID: $this->getKeyField());
    }

    private function bootstrapQuery(array $fields = ['*']): QueryBuilder {
        return $this->query()->select();
    }

    public function find(string $field, string $value): Entity {
        return $this->bootstrapQuery()->where(arguments: [$field => $value])->run(fetchMode: 'fetch');
    }

    public function findOne(string $field, string $value): Entity {
        return $this->bootstrapQuery()->where(arguments: [$field => $value])->run(fetchMode: 'fetch');
    }

    public function findFirst(string $field, string $value): Entity {
        $tmp = $this->findOne($field, $value);
        $tmp->requireExistence();

        return $this->bootstrapQuery()->where([$field => $value])->orderBy($tmp->getKeyField(), 'ASC')->limit(1)->run('fetch');
    }

    public function findLast(string $field, string $value): Entity {
        $tmp = $this->findOne($field, $value);
        $tmp->requireExistence();

        return $this->bootstrapQuery()->where([$field => $value])->orderBy($tmp->getKeyField(), 'DESC')->limit(1)->run('fetch');
    }

    /**
     * 
     * @param string $field
     * @param string $value
     * @return [Entity]
     */

    public function findMultiple(string $field, string $value): array {
        return $this->bootstrapQuery()->where(arguments: [$field => $value])->run();
    }

    public function findByMultiple(array $conditions): array {
        return $this->bootstrapQuery()->where(arguments: $conditions)->run();
    }

    public function addMetaData(array $data, string $type = null): self {
        if (empty($data)) throw new \InvalidArgumentException(message: self::INVALID_ENTITY_DATA);

        (new EntityMetaData())
            ->set(data: [
                Table::ENTITY_TYPE_COLUMN => $this->getTableName(), 
                Table::ENTITY_ID_COLUMN => $this->key() ?? 0,
                'Data' => json_encode(value: $data),
                'Type' => $type ?? 'Default',
                'IP' => app()->getRequest()->getIP()
            ])
            ->save(addMetaData: false);

        return $this;
    }

    public function getTableColumns(): array|object {
        return (new QueryBuilder(class: get_called_class(), table: $this->getTableName(), keyID: $this->getKeyField()))->select()->run(); 
    }

    public function getMetaData(): QueryBuilder {
        return (new EntityMetaData())->getQueryBuilder()->select()->where(arguments: [Table::ENTITY_TYPE_COLUMN => $this->getTableName(), Table::ENTITY_ID_COLUMN => $this->key()]);
    }

    public function setStatus(int $status): self {
        if (!$this->get(Table::STATUS_COLUMN)) throw new \app\core\src\exceptions\ForbiddenException(self::INVALID_ENTITY_STATUS);
        $this->set([Table::STATUS_COLUMN => $status])->save();
        return $this;
    }

    public function coupleEntity(Entity $entity): void {
		$entity->set(data: [$this->getKeyField() => $this->key()]);
		$entity->init();
	}

    public function setSortOrder(int $sortOrder): self {
        $this->set([Table::SORT_ORDER_COLUMN => $sortOrder]);
        return $this;
    }

    public function patchSortOrder(int $sortOrder): self {
        $this->patchField([Table::SORT_ORDER_COLUMN => $sortOrder]);
        return $this;
    }

    public function setRelationelTableSortOrder(string $table, int $sortOrder, $additionalConditions = []): void {
        $this->getQueryBuilder($table)
            ->patch(fields: [Table::SORT_ORDER_COLUMN => $sortOrder])
            ->where(arguments: $additionalConditions)
            ->run();
    }

    public function all(): array {
        return (new QueryBuilder(class: get_called_class(), table: $this->getTableName(), keyID: $this->getKeyField()))->select()->run();
    }

    public function search(array $arguments): array {
        return $this->bootstrapQuery()->where(arguments: $arguments)->run();
    }

    public function findOrCreate(string $whereKey, string $whereValue, array $data = []): Entity {
        $lookup = $this->find($whereKey, $whereValue);
        if ($lookup->exists()) return $lookup;

        $cEntity = new $this();
        $cEntity->setData($data);
        $cEntity->save();

        return $cEntity;
    }

    public function complete(): void {
		$this->patchField([Table::COMPLETED_COLUMN => 1]);
	}

    public function add(object $arguments): ?array {
        return $this->crud($arguments);
    }

    public function edit(object $arguments): ?array {
        return $this->crud($arguments, 'edit');
    }

    public function getEntityTableFields(): self {
        $this->bootstrapQuery()->where()->limit(limit: 1)->run();
        return $this;
    }

    public function appendHistory(array $data): self {
        return $this->addMetaData($data, 'History');
    }

    public function history(): array|object {
        return $this->getMetaData()->where(arguments: ['Type' => 'History'])->run();
    }

    public function files() {
		return $this->hasManyToMany(FileModel::class, 'file_entity')->run();
	}

    public function attachToLanguage(int $languageID): void {
        $cLanguage = new LanguageModel($languageID);
        $cLanguage->requireExistence();

        $this->createCustomPivot($this->languagePivot(), ['EntityType' => $this->getTableName(), 'EntityID' => $this->key(), 'LanguageID' => $cLanguage->key()]);
    }

}