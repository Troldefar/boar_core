<?php

namespace app\core\src\utilities;

class Utilities {

    public static function appendToStringIfKeyNotLast(array $arrayKey, string|int $iterationKey, string $appender = ', '): null|string {
        return array_key_last($arrayKey) === $iterationKey ? null : $appender;
    }

    public static function stdFilterSpecialChars(int $type, string $input): mixed {
        return filter_input($type, $input, FILTER_SANITIZE_SPECIAL_CHARS);
    }

}