<?php

namespace app\core\src\database\querybuilder\src;

use \app\core\src\database\table\Table;
use \app\core\src\miscellaneous\CoreFunctions;
use \app\core\src\utilities\Parser;


trait WhereQuery {

    public function in(string $field, array $ins): self {
        $queryINString = array_map(function($fieldKey, $fieldValue) {
           $this->updateQueryArgument("inCounter$fieldKey", $fieldValue);
           return " :inCounter$fieldKey ";
       }, array_keys($ins), array_values($ins));

       $this->upsertQuery($this->checkStart() . " $field IN ( " . implode(', ', $queryINString) . " ) ");
       return $this;
   }

    public function where(array $arguments = []): self {
        foreach ($arguments as $selector => $sqlValue) {
            $dateField = str_contains($selector, Constants::DEFAULT_FRONTEND_DATE_FROM_INDICATOR) || str_contains($selector, Constants::DEFAULT_FRONTEND_DATE_TO_INDICATOR);

            if ($dateField) $this->handleDateClausing($selector, $sqlValue);
            else {
                list($comparison, $sqlValue) = Parser::sqlComparsion(($sqlValue ?? null), $this->getComparisonOperators());
                $key = preg_replace(Constants::DEFAULT_REGEX_REPLACE_PATTERN, '', $selector);

                $this->updateQueryArgument($key, $sqlValue);
                $this->upsertQuery($this->checkStart() . "{$selector} {$comparison} :{$key}");
            }
        }
        return $this;
    }

    private function handleDateClausing(string $selector, string $sqlValue) {
        list($order, $field) = explode('-', $selector);
        if (str_contains($order, '.')) $table = CoreFunctions::first(explode('.', $order))->scalar;

        $selector = preg_replace(Constants::DEFAULT_REGEX_REPLACE_PATTERN, '', $selector);
        $sqlValue = date(Constants::DEFAULT_SQL_DATE_FORMAT, strtotime($sqlValue));

        $this->upsertQuery($this->checkStart() . (isset($table) && $table ? $table . '.' : '') . "{$field} = :{$selector}");
        $this->updateQueryArgument($selector, $sqlValue);
    }

    public function or(array $arguments) {
        foreach ($arguments as $selector => $sqlValue) {
            list($comparison, $sqlValue) = Parser::sqlComparsion(($sqlValue ?? ''), $this->getComparisonOperators());
            $key = trim(Constants::OR) . preg_replace(Constants::DEFAULT_REGEX_REPLACE_PATTERN, '', $selector);
            $this->updateQueryArgument($key, $sqlValue);
            $this->upsertQuery(Constants::OR . " {$selector} {$comparison} :{$key}");
        }
        return $this;
    }

    public function forceWhere(array $arguments = []): self {
        foreach ($arguments as $selector => $sqlValue) {
            list($comparison, $sqlValue) = Parser::sqlComparsion(($sqlValue ?? ''), $this->getComparisonOperators());
            $key = preg_replace(Constants::DEFAULT_REGEX_REPLACE_PATTERN, '', $selector);

            $this->updateQueryArgument($key, $sqlValue);
            $this->upsertQuery(Constants::WHERE . " {$selector} {$comparison} :{$key}");
        }
        return $this;
    }

    public function between(string|int $from, string|int $to): self {
        $this->upsertQuery($this->checkStart() . Constants::BETWEEN . " :from AND :to ");
        $this->updateQueryArguments(compact('from', 'to'));
        
        return $this;
    }

    public function notBetween(string|int $from, string|int $to): self {
        $this->upsertQuery($this->checkStart() . Constants::NOT . Constants::BETWEEN . " :from " . Constants::AND . " :to ");
        $this->updateQueryArguments(compact('from', 'to'));
        
        return $this;
    }

    public function dateBetween(string $column, string $from, string $to, $dateFormat = '%Y-%m-%d'): self {
        $formattedColumn = str_replace('.', '_', $column);
        
        $this->upsertQuery($this->checkStart() . " $column " . Constants::BETWEEN . " STR_TO_DATE(:fromDateRange_$formattedColumn, '$dateFormat') " . Constants::AND . " STR_TO_DATE(:toDateRange_$formattedColumn, '$dateFormat')");
        $this->updateQueryArguments([
            "fromDateRange_$formattedColumn" => $from,
            "toDateRange_$formattedColumn" => $to,
        ]);

        return $this;
    }

    public function dateNotBetween(string $column, string $from, string $to, $dateFormat = '%Y-%m-%d'): self {
        $formattedColumn = str_replace('.', '_', $column);
        
        $this->upsertQuery($this->checkStart() . " $column " . Constants::NOT . Constants::BETWEEN . " STR_TO_DATE(:fromDateRange_$formattedColumn, '$dateFormat') " . Constants::AND . " STR_TO_DATE(:toDateRange_$formattedColumn, '$dateFormat') ");
        $this->updateQueryArguments([
            "fromDateRange_$formattedColumn" => $from,
            "toDateRange_$formattedColumn" => $to,
        ]);

        return $this;
    }

    public function isNull(string $field): self {
        $this->upsertQuery($this->checkStart() . $field . Constants::IS_NULL);
        return $this;
    }
    
    public function isNotNull(string $field): self {
        $this->upsertQuery($this->checkStart() . $field . Constants::IS_NOT_NULL);
        return $this;
    }

    public function before(string $field): self {
        $this->where([$field => '< ' . date('Y-m-d')]);
        return $this;
    }

    public function isToday(string $field): self {
        $this->upsertQuery('DATE('.$field.') = ' . Constants::CURDATE);
        return $this; 
    }

    public function isNotToday(string $field): self {
        $this->upsertQuery('DATE('.$field.') = ' . Constants::NOT_TODAY);
        return $this;
    }

    public function isTodayOrLater(string $field): self {
        $this->upsertQuery('DATE('.$field.') = ' . Constants::HIGHER_THAN_OR_TODAY);
        return $this;
    }

    public function beforeToday(string $field = 'CreatedAt'): self {
        $this->where([$field => Constants::LOWER_THAN_CURRENT_DAY]);
        return $this;
    }

    public function after(string $field): self {
        $this->where([$field => Constants::HIGHER_THAN_CURRENT_DAY]);
        return $this;
    }

    public function afterToday(string $field = 'CreatedAt'): self {
        $this->where([$field => Constants::HIGHER_THAN_CURRENT_DAY]);
        return $this;
    }

    public function limit(int $limit = Constants::DEFAULT_LIMIT): self {
        $this->upsertQuery(Constants::LIMIT . ' :limit ');
        $this->updateQueryArguments(compact('limit'));
        return $this;
    }

    public function offset(int $offset = Constants::DEFAULT_OFFSET): self {
        $this->upsertQuery(Constants::OFFSET . ' :offset ');
        $this->updateQueryArguments(compact('offset'));
        return $this;
    }

    private function checkStart(): string {
        return (strpos($this->getQuery(), Constants::WHERE) === false ? Constants::WHERE : Constants::AND);
    }

    public function groupBy(string $group): self {
        $this->upsertQuery(Constants::GROUP_BY . $group);
        return $this;
    }

    public function from(?string $from = null): self {
        $this->upsertQuery(Constants::FROM . ($from ?? $this->table));
        return $this;
    }

    public function orderBy(string|array $field, string $order = Constants::DEFAULT_ASCENDING_ORDER): self {
        if (is_iterable($field)) $field = implode(',', $field);
        $this->upsertQuery(Constants::ORDER_BY . $field . ' ' . $order);
        return $this;
    }

    public function orderBySortOrder(string $order = Constants::DEFAULT_ASCENDING_ORDER): self {
        $this->upsertQuery(Constants::ORDER_BY . Table::SORT_ORDER_COLUMN . ' ' . $order);
        return $this;
    }

    public function like(array $arguments): self {
        return $this->likeClause($arguments);
    }

    public function likeOr(array $arguments): self {
        return $this->likeClause($arguments, Constants::OR);
    }

    public function isolatedLikeOr(array $arguments): self {
        return $this->likeClause($arguments, Constants::ISOLATED);
    }

    private function likeClause(array $arguments, string $type = ''): self {
        foreach ($arguments as $selector => $sqlValue) {
            $formattedColumn = str_replace('.', '_', $selector);
            list($_, $sqlValue) = Parser::sqlComparsion(($sqlValue ?? ''), $this->getComparisonOperators());

            $this->updateQueryArgument($formattedColumn, $sqlValue);
            $innerQuery = " {$selector} LIKE CONCAT('%', :{$formattedColumn}, '%') ";

            $sql = match ($type) {
                Constants::OR => 
                    (array_key_first($arguments) === $selector ? $this->checkStart() : '') . 
                    $innerQuery . 
                    (count($arguments) && array_key_last($arguments) !== $selector ? $type : ''),
                Constants::ISOLATED =>
                    $innerQuery .
                    (count($arguments) && array_key_last($arguments) !== $selector ? Constants::OR : ''),
                default => 
                    $this->checkStart() . $innerQuery,
            };            
            
            $this->upsertQuery($sql);
        }

        return $this;
    }

    // Additional select-related methods can go here
}