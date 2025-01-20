<?php

namespace app\core\src\database\querybuilder\src;

class Constants {

    public const ISOLATED       = 'isolated';

    public const COUNT          = ' COUNT';
    public const DISTINCT       = ' DISTINCT ';
    public const WHERE          = ' WHERE ';
    public const AND            = ' AND ';
    public const OR             = ' OR ';
    public const NOT            = ' NOT ';
    public const LIMIT          = ' LIMIT ';
    public const OFFSET         = ' OFFSET ';
    public const BIND           = ' = :';
    public const INNER_JOIN     = ' INNER JOIN ';
    public const LEFT_JOIN      = ' LEFT JOIN ';
    public const RIGHT_JOIN     = ' RIGHT JOIN ';
    public const SUBQUERY_OPEN  = ' ( ';
    public const SUBQUERY_CLOSE = ' ) ';

    public const UNION = ' UNION ';
    public const UNION_ALL = ' UNION ALL ';

    public const WITH = ' WITH ';
    public const HAVING = ' HAVING ';
    public const AS = ' AS ';
    public const DELETE_FROM = ' DELETE FROM ';
    public const TRUNCATE = ' TRUNCATE TABLE ';
    public const FROM = ' FROM ';
    public const SELECT = ' SELECT ';
    public const IS_NULL = ' IS NULL ';
    public const IS_NOT_NULL = ' IS NOT NULL ';
    public const PARTITION_BY = ' PARTITION BY ';

    public const BETWEEN      = ' BETWEEN ';
    public const SQL_DESCRIBE = ' DESCRIBE ';
    public const GROUP_BY     = ' GROUP BY ';
    public const ORDER_BY     = ' ORDER BY ';

    public const DEFAULT_ASCENDING_ORDER = ' ASC ';
    public const DEFAULT_DESCENDING_ORDER = ' DESC ';
    public const DEFAULT_SQL_DATE_FORMAT = 'Y/m/d';
    public const DEFAULT_FRONTEND_DATE_FROM_INDICATOR = 'from-';
    public const DEFAULT_FRONTEND_DATE_TO_INDICATOR = 'to-';

    public const CURDATE = ' CURDATE() ';
    public const LOWER_THAN_CURRENT_DAY = ' < CURDATE() ';
    public const HIGHER_THAN_CURRENT_DAY = ' > CURDATE() ';
    public const HIGHER_THAN_OR_TODAY = ' >= CURDATE() ';
    public const LOWER_THAN_OR_TODAY = ' <= CURDATE() ';
    public const NOT_TODAY = ' != CURDATE() ';

    public const DEFAULT_LIMIT = 20;
    public const DEFAULT_OFFSET = 0;

    public const COMPARISON_OPERATORS = ['=', '<>', '!=', '>', '<', '>=', '<='];

    public const DEFAULT_REGEX_REPLACE_PATTERN = '/[^a-zA-Z0-9]/';

    public const PDO_FETCH_ALL_MODE = 'fetchAll';
    public const PDO_FETCH_ONE_MODE = 'fetch';

}