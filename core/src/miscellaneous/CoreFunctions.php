<?php

/**
|----------------------------------------------------------------------------
| Convenience for those getters that are used frequently throughout the system
|----------------------------------------------------------------------------
|
| @author RE_WEB
| @package app\core\src\miscellaneous
|
*/

namespace app\core\src\miscellaneous;

final class CoreFunctions {

    public static function displayDD($input, $title = 'Debugging'): void {
        if (self::app()::isCli()) exit($input);
        
        echo '<style>* {margin:0;padding:0;box-sizing:border-box;color:black;font-weight:100;}</style>';
        echo '<pre style="background-color: #a3b18a; color: white; text-wrap:wrap;width:100vw;height:100vh;display:flex;justify-content:center;align-items:center;flex-direction:column;" class="debug">';
        echo '<h2 class="text-center">' . $title . '</h2><hr><p style="font-size:1.5rem;">';
        var_dump($input);
        if ($title) echo '</p><hr /><h2 class="text-center">End of ' . $title . '</h2></pre>';
    }
      
    public static function dd(mixed $input, $title = ''): void {
        self::displayDD($input, $title);
        exit;
    }
      
    public static function d(mixed $input, $title = ''): void {
        self::displayDD($input, $title);
        echo '<hr />';
    }
      
    public static function hs($input): string {
        return htmlspecialchars($input);
    }
      
    public static function app(): object {
        return \app\core\Application::$app;
    }
      
    public static function validateCSRF(): bool {
        return (new \app\core\src\tokens\CsrfToken())->validate();
    }
      
    public static function nukeSession(): void {
        self::app()->getSession()->nullAll();
    }

    public static function restartSession(): void {
        self::app()->getSession()->restart();
    }
      
    public static function ths(string $input): string {
        return self::hs(self::app()->getI18n()->translate($input));
    }
      
    public static function first(array|object $iterable): ?object {
        if (empty($iterable)) return null;
        return (object)$iterable[array_key_first($iterable)];
    }

    public static function last(array|object $iterable): ?object {
        if (empty($iterable)) return null;
        return (object)$iterable[array_key_last($iterable)];
    }
      
    public static function getIndex(array|object $iterable, int|string $expectedIndex): ?object {
          if (!isset($iterable[$expectedIndex])) return (object)['scalar' => 'Invalid'];
          return (object)$iterable[$expectedIndex];
    }
      
    public static function loopAndEcho(array|object $iterable, bool $echoKey = false): void {
        foreach ($iterable as $key => $value) echo $echoKey ? $key : $value;
    }
      
    public static function applicationUser(): ?\app\models\UserModel {
        return self::app()->getUser();
    } 

    public static function browseEntities(array $iterableEntities, string $identifierKey, string $identifierValue): array {
        $result = [];
        
        foreach ($iterableEntities as $iterableEntity)
            if ($iterableEntity->get($identifierKey) === $identifierValue)
                $result[] = $iterableEntity;

        return $result;
    }

}