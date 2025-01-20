<?php

namespace app\core\src\traits\file;

trait FileStaticMethodTrait {

    private const INVALID_STYLESHEET = 'Stylesheet not found';
    private const INVALID_SCRIPT = 'Script not found';

    public static function base64Encode(string $filePath) {
        if (!file_exists($filePath)) return 'data:image/jpeg;base64,';

        return 'data:image/jpeg;base64,' . base64_encode(file_get_contents($filePath));
    }

    public static function getResourceDir() {
        return app()::$ROOT_DIR . '/public/resources/';
    }

    public static function buildStylesheet(string $src): string {
        $location = self::getResourceDir() . self::CSS_EXTENSION . '/' . $src . '.' . self::CSS_EXTENSION;

        if (!file_exists($location)) 
            throw new \app\core\src\exceptions\NotFoundException(self::INVALID_STYLESHEET);

        return '<link rel="stylesheet" href="'.str_replace(self::getResourceDir(), '/resources/', $location).'">';
    }

    public static function buildScript(string $src): string {
        $location = self::getResourceDir() . self::JS_EXTENSION . '/' . $src .'.' . self::JS_EXTENSION;
        
        if (!file_exists($location)) 
            throw new \app\core\src\exceptions\NotFoundException(self::INVALID_SCRIPT);

        return '<script defer src="' . str_replace(self::getResourceDir(), '/resources/', $location) . '"></script>';
    }

    public static function putContent(string $fileName, string $content): int|false {
        return file_put_contents($fileName, $content);
    }

    public static function getNonHiddenFiles(string $dir): array {
        return preg_grep('/^([^.])/', scandir($dir));
    }

}