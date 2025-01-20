<?php

/**
|----------------------------------------------------------------------------
| Entity relations
|----------------------------------------------------------------------------
| Describes the relationships betwen entities
| 
| @author RE_WEB
| @package \app\core\src\database
|
*/

namespace app\core\src\traits\entity;

use \app\core\src\database\querybuilder\QueryBuilder;
use \app\core\src\database\table\Table;
use \app\core\src\miscellaneous\CoreFunctions;
use \app\core\src\factories\ModelFactory;

trait EntityRelationsTrait {

    /**
     * Return entity
     */

    private function getInstanceOf(string $class) {
        $handler = preg_replace('/Model/', '', CoreFunctions::last(explode('\\', $class))->scalar);
        return (new ModelFactory(compact('handler')))->create();
    }

    /**
     * Find entities on the child table where the parent has a corresponding primary key
     */
    
    public function hasMany(string $related): QueryBuilder {
        $instance = $this->getInstanceOf($related);
        return $instance->query()->select()->where([$this->getKeyField() => $this->key()]);
    }

    /**
     * Find entity on the parent where the child has a param primary key
     */

    public function hasOne(string $entity, string $entityKey) {
        $instance = $this->getInstanceOf($entity);
        $queryBuilder = new QueryBuilder($entity, $this->getTableName(), $this->key());
        return $queryBuilder->select()->where([$instance->getKeyField() => $entityKey])->run('fetch');
    }

    /**
     * Find entities on a param table where the key and value is a match
     */

    public function attachedTo($entity, string $table, string $key, string $value) {
        $queryBuilder = new QueryBuilder($entity, $table, $key);
        return $queryBuilder->select()->where([$key => $value])->run();
    }

    /**
     * Find entities on a param table where the parent primary key exist
     */

    public function connectedWith(string $relatedEntity, string $table) {
        $queryBuilder = new QueryBuilder($relatedEntity, $table, '');
        return $queryBuilder->select()->where([$this->getKeyField() => $this->key()])->run();
    }

    /**
     * Find entities on param entity where parent key is a match
     */
    
    public function belongsTo(string $related) {
        $instance = $this->getInstanceOf($related);
        return $instance->find($this->getKeyField(), $this->key());
    }

    /**
     * Find entity on parent table where the param key is a match
     */

    public function isBasedOn(string $relatedEntity, string $key) {
        $queryBuilder = new QueryBuilder($relatedEntity, $this->getTableName(), $this->key());
        return $queryBuilder->select()->where([$this->getKeyField() => $key])->run();
    }

    /**
     * Create a pivot relation with N amount of KVPs
     */

    public function createPivot(...$keys) {
        $queryBuilder = new QueryBuilder(get_called_class(), $this->getPivot(), '');
        $queryBuilder->create(CoreFunctions::first($keys))->run();
        return app()->getConnection()->getLastInsertedID();
    }

    /**
     * Create a pivot relation with N amount of KVPs
     */

     public function createCustomPivot($table, ...$keys) {
        $queryBuilder = new QueryBuilder(get_called_class(), $table, '');
        return $queryBuilder->create(CoreFunctions::first($keys))->run();
    }

    /**
     * Upsert a pivot relation with N amount of KVPs
     */

     public function upsertCustomPivot($table, string $identifier, ...$keys) {
        $queryBuilder = new QueryBuilder(get_called_class(), $table, '');
        $pivotExists = $queryBuilder->select()->where((array)CoreFunctions::first($keys))->run('fetch');
        return $pivotExists->exists() ? 
            $queryBuilder->patch((array)CoreFunctions::first($keys), $identifier, $pivotExists->get($identifier))->run() :
            $queryBuilder->create(CoreFunctions::first($keys))->run();
    }

    /**
     * Update a pivot relation with N amount of KVPs
     */

    public function updateCustomPivot($table, $keys, ?string $entityKeyField = null, ?string $entityKeyValue = null) {
        $queryBuilder = new QueryBuilder(get_called_class(), $table, '');
        return $queryBuilder->patch($keys, $entityKeyField, $entityKeyValue)->run();
    }

    /**
     * Update a pivot relation with N amount of KVPs based on current entity
     */

    public function updateCustomPivotBasedOnEntity($table, $keys) {
        $queryBuilder = new QueryBuilder(get_called_class(), $table, '');
        return $queryBuilder->patch($keys, $this->getKeyField(), $this->key())->run();
    }

    /**
     * Find entites on pivot table where parent primary key is a match
     */

    public function manyToMany(string $relatedEntity) {
        $queryBuilder = new QueryBuilder($relatedEntity, $this->getPivot(), '');
        return $queryBuilder->select()->where([$this->getKeyField() => $this->key()]);
    }

    /**
     * Find entity on table where key and value match whatever
     */

    public function entityFromTableBasedOnKeyValuePair(string $relatedEntity, string $column, string $value, string $table) {
        $queryBuilder = new QueryBuilder($relatedEntity, $table, '');
        return $queryBuilder->select()->where([$column => $value]);
    }

    /**
     * Target specific pivot
     */

    public function hasManyToMany(string $relatedEntity, string $pivot) {
        $queryBuilder = new QueryBuilder($relatedEntity, $pivot, '');
        return $queryBuilder->select()->where([Table::ENTITY_TYPE_COLUMN => $this->getTableName(), Table::ENTITY_ID_COLUMN => $this->key()]);
    }

    /**
     * Target specific pivot by some polymorhic relation
     */

     public function hasManyToManyIntrovertedPolymorphic(string $relatedEntity, string $pivot, ...$keys) {
        $queryBuilder = new QueryBuilder($relatedEntity, $pivot, '');
        return $queryBuilder->select()->where(...$keys);
    }

    /**
     * hasManyToMany relationship with custom callback (i.e. to join tables to this)
     */

     public function findByHasManyToMany(string $relatedEntity, string $pivot, \Closure $callback) {
        $queryBuilder = new QueryBuilder($relatedEntity, $pivot, '');
        $queryBuilder->select();
        call_user_func($callback, $queryBuilder);
        return $queryBuilder->where([Table::ENTITY_TYPE_COLUMN => $this->getTableName(), Table::ENTITY_ID_COLUMN => $this->key()]);
    }

    /**
     * Find entities on a table where the column and value is a match
     */

    public function oneHasMany(string $class, string $table, string $column, string $value): QueryBuilder {
        $queryBuilder = new QueryBuilder($class, $table, $this->key());
        return $queryBuilder->select()->where([$column => $value]);
    }

    /**
     * Find entites on a polymorphic table where parent entity and primary key is match
     */

    public function hasManyPolymorphic(string $class) {
        $polyMorphicEntity = $this->getInstanceOf($class);
        return $polyMorphicEntity->search([Table::ENTITY_TYPE_COLUMN => $this->getTableName(), Table::ENTITY_ID_COLUMN => $this->key()]);
    }

    /**
     * Target specific pivot
     */

     public function hasOnePolymorphic(string $relatedEntity, string $pivot) {
        $queryBuilder = new QueryBuilder($relatedEntity, $pivot, '');
        return $queryBuilder->select()->where([Table::ENTITY_TYPE_COLUMN => $this->getTableName(), Table::ENTITY_ID_COLUMN => $this->key()]);
    }

    /**
     * Find parent entity where parent entity key is directly on child table
     */

    public function directTableObjectRelation(string $related, int $key) {
        $instance = $this->getInstanceOf($related);
        return new $instance($key);
    }

    /**
     * Remove specific relation
     */

    public function deleteRelation(array $keys) {
        $queryBuilder = new QueryBuilder(get_called_class(), $this->getPivot(), $this->key());
        return $queryBuilder->delete()->where($keys)->run();
    }

    /**
     * Remove custom relation
     */

     public function deleteCustomRelation($table, array $keys) {
        $queryBuilder = new QueryBuilder($this, $table, $this->key());
        return $queryBuilder->delete()->where($keys)->run();
    }

    /**
     * Remove specific relation
     */

     public function deleteTableRelation(string $table, array $keys) {
        $queryBuilder = new QueryBuilder(get_called_class(), $table, $this->key());
        return $queryBuilder->delete()->where($keys)->run();
    } 

}