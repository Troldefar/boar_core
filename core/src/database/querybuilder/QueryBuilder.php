<?php

/**
|----------------------------------------------------------------------------
| Query builder initial extension
|----------------------------------------------------------------------------
|
| @author RE_WEB
| @package core
|
*/

namespace app\core\src\database\querybuilder;

use \app\core\src\miscellaneous\CoreFunctions;
use \app\core\src\database\querybuilder\src\Constants;

class QueryBuilder extends QueryBuilderBase {

    use src\SelectQuery;
    use src\WhereQuery;
    use src\DeleteQuery;
    use src\InsertQuery;
    use src\UpdateQuery;
    use src\JoinQuery;
    use src\AggregateQuery;
    use src\PartitionQuery;
    use src\SubQuery;

    public function getComparisonOperators(): array {
        return Constants::COMPARISON_OPERATORS;
    }

    public function debugQuery() {
        CoreFunctions::dd('Currently debugging query:' . PHP_EOL . PHP_EOL . $this->getQuery() . PHP_EOL . PHP_EOL . PHP_EOL . json_encode($this->getArguments()));
    }

    public function fetchRow(?array $criteria = null) {
        $this->select()->where($criteria);
        $response = $this->app->getConnection()->execute($this->getQuery(), $this->getArguments(), Constants::PDO_FETCH_ONE_MODE);
        $this->resetQuery();
        return $response;
    }

    public function run(string $fetchMode = Constants::PDO_FETCH_ALL_MODE, $cache = true) {
        $response = $this->app->getConnection()->execute($this->getQuery(), $this->getArguments(), $fetchMode, $cache);
        $this->resetQuery();

        if($fetchMode === Constants::PDO_FETCH_ONE_MODE)
            return new $this->class((array)$response);

        return array_map(function($object) {
            return new $this->class((array)$object);
        }, (array)$response);
    }

}