<?php

namespace app\core\src\config;

class Cache {

    private array $data;

    public function __construct(
        private array $options = []
    ) {
        $this->bootstrap();
    }

    private function bootstrap() {
        foreach (scandir($this->getCacheDirectory()) as $name)
            if (isset($options[$name]))
                $this->data[$name] = $options[$name];
    }

    public function get(string $id): mixed {
        $file_name = $this->getFileName($id);

        if (!is_file($file_name) || !is_readable($file_name)) return false;

        $lines    = file($file_name);
        $lifetime = array_shift($lines);
        $lifetime = (int) trim($lifetime);

        if ($lifetime !== 0 && $lifetime < time()) {
            @unlink($file_name);
            return false;
        }

        $serialized = join('', $lines);

        return unserialize($serialized);
    }

    public function delete(string $id): bool {
        $file_name = $this->getFileName($id);
        return unlink($file_name);
    }

    public function save(string $id, $data, int $lifetime = 3600): int|false {
        $dir = $this->getDirectory($id);
        if (!is_dir($dir)) if (!mkdir($dir, 0755, true)) return false;
        
        $file_name  = $this->getFileName($id);
        $lifetime   = time() + $lifetime;
        $serialized = serialize($data);

        return file_put_contents($file_name, $lifetime . PHP_EOL . $serialized);
    }

    protected function getDirectory(string $id): string {
        $hash = hash(env('hash')->algo, $id, false);
        $dirs = [$this->getCacheDirectory(), substr($hash, 0, 2), substr($hash, 2, 2)];

        return join(DIRECTORY_SEPARATOR, $dirs);
    }

    protected function getCacheDirectory() {
        return env('cache')->defaultDir;
    }

    protected function getFileName(string $id): string {
        $directory  = $this->getDirectory($id);
        $hash       = hash(env('hash')->algo, $id, false);
        $file       = $directory . DIRECTORY_SEPARATOR . $hash . '.cache';

        return $file;
    }
}