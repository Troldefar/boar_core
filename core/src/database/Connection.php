<?php

/**
|----------------------------------------------------------------------------
| Application database connection
|----------------------------------------------------------------------------
| 
| @author RE_WEB
| @package core
|
*/

namespace app\core\src\database;

use \app\core\src\database\adapters\Adapter;

use app\core\src\exceptions\InvalidTypeException;

use InvalidArgumentException;

class Connection {
    
    private static ?Connection $instance = null;

    private const DEFAULT_SQL_QUERY_FETCH_TYPE = 'fetchAll';

    private \Pdo $pdo;
    protected Adapter $adapter;
    
    protected function __construct(
        Adapter $adapter,
        private Cache $cache = new Cache
    ) {
        $this
            ->setAdapter($adapter)
            ->connect();
    }

    public function getAdapter(): Adapter {
        return $this->adapter;
    }

    private function setAdapter(Adapter $adapter): self {
        $this->adapter = $adapter;
        return $this;
    }

    private function connect() {
        $this->setPDO(
            $this->createAdapter()->connect(
                app()->getConfig()->get('database')
            )
        );
    }

    private function setPDO(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    private function createAdapter(): object {
        if (!is_object($this->adapter)) throw new InvalidTypeException('Invalid adapter was provided');

        return $this->adapter;
    }

    public static function getInstance(Adapter $adapter) {
        if (!self::$instance) self::$instance = new self($adapter);
        return self::$instance;
    }

    public function execute(
        #[\SensitiveParameter] string $query,
        #[\SensitiveParameter] array $args = [],
        string $fetchType = self::DEFAULT_SQL_QUERY_FETCH_TYPE,
        $cache = true
    ) {
        try {
            $this->validateFetchType($fetchType);

            $cacheKey = $this->generateCacheKey($query, $args);

            $cachedResult = $this->cache->get($cacheKey);
            
            if ($cache && $cachedResult) return $cachedResult;

            $result = $this->performQuery($query, $args, $fetchType);

            if ($cache && !empty($result)) $this->cache->set($cacheKey, $result);

            return $result;
        } catch (\PDOException $pdoException) {
            app()->getLogger()->log($pdoException);
        }
    }

    private function generateCacheKey(string $query, array $args): string {
        $serializedArgs = array_map(fn($arg) => $arg instanceof \SimpleXMLElement ? (string)$arg : $arg, $args);
        return md5($query . serialize($serializedArgs));
    }

    private function validateFetchType(string $fetchType): void {
        if (method_exists(\PDOStatement::class, $fetchType)) return;

        throw new InvalidArgumentException('Invalid fetch type');
    }

    private function performQuery(string $query, array $args, string $fetchType): mixed {
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($args);
        return $stmt->{$fetchType}();
    }

    public function getLastInsertedID(): string|false {
        return $this->pdo->lastInsertId();
    }

    public function beginTransaction(): void {
        $this->pdo->beginTransaction();
    }

    public function commit(): void { 
        $this->pdo->commit();
    }
    
    public function rollback(): void { 
        $this->pdo->rollback();
    }

    protected function __clone() {
        throw new \app\core\src\exceptions\ForbiddenException('Can not clone ' . get_called_class());
    }

    public function __wakeup() {
        throw new \Exception('Can not unserialize ' . get_called_class());
    }

    public function __call(string $method, array $params = []) {
        return method_exists($this, $method) ? call_user_func_array([$this, $method], $params) : "PDO::$method does not exists.";
    }
    
}