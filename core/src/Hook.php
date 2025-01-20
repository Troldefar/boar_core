<?php

/**
|----------------------------------------------------------------------------
| Default hook
|----------------------------------------------------------------------------
| 
| @author RE_WEB
| @package app\core\src
|
*/

namespace app\core\src;

class Hook {
    protected static $actions = [];

    public static function addAction($hookName, $callback, $priority = 10) {
        if (!isset(self::$actions[$hookName]))
            self::$actions[$hookName] = [];

        self::$actions[$hookName][] = ['callback' => $callback, 'priority' => $priority];
        
        usort(self::$actions[$hookName], function($a, $b) {
            return $a['priority'] <=> $b['priority'];
        });
    }

    public static function doAction($hookName, ...$args) {
        if (!isset(self::$actions[$hookName])) return;

        foreach (self::$actions[$hookName] as $action)
            call_user_func_array($action['callback'], $args);
    }

    public static function removeAction($hookName, $callback) {
        if (!isset(self::$actions[$hookName])) return;

        foreach (self::$actions[$hookName] as $key => $action)
            if ($action['callback'] === $callback)
                unset(self::$actions[$hookName][$key]);

        self::$actions[$hookName] = array_values(self::$actions[$hookName]);
    }
}
