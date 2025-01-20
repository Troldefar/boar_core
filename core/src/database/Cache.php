<?php

namespace app\core\src\database;

class Cache {

    private const TIMESTAMP_KEY = 't';
    public const RESULT_KEY = 'r';

    public function __construct(
        #[\SensitiveParameter] private array $cache = [],
        #[\SensitiveParameter] private int $CACHE_TTL = 60,
        #[\SensitiveParameter] private int $MAX_CACHE_SIZE = 1000
    ) {}

    public function get(string $key): mixed {
        if (!isset($this->cache[$key])) return null;

        $entry = $this->cache[$key];
        if ((time() - $entry[self::TIMESTAMP_KEY]) < $this->CACHE_TTL) return $entry[self::RESULT_KEY];

        $this->evict($key);

        return 'invalid key: ' . $key;
    }

    public function set(string $key, mixed $result): void {
        if (count($this->cache) >= $this->MAX_CACHE_SIZE) array_shift($this->cache);

        $this->cache[$key] = [self::RESULT_KEY => $result, self::TIMESTAMP_KEY => time()];
    }

    public function evict(string $key): void {  
        unset($this->cache[$key]);
    }

}