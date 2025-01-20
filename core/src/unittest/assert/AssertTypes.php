<?php

namespace app\core\src\unittest\assert;

trait AssertTypes {
    public function assertInstanceOf(mixed $value, string $className): bool {
        return $value instanceof $className;
    }

    public function assertIsArray(mixed $value): bool {
        return is_array($value);
    }

    public function assertIsList(mixed $value): bool {
        return is_array($value) && array_is_list($value);
    }

    public function assertIsBool(mixed $value): bool {
        return is_bool($value);
    }

    public function assertIsCallable(mixed $value): bool {
        return is_callable($value);
    }

    public function assertIsFloat(mixed $value): bool {
        return is_float($value);
    }

    public function assertIsInt(mixed $value): bool {
        return is_int($value);
    }

    public function assertIsIterable(mixed $value): bool {
        return is_iterable($value);
    }

    public function assertIsNumeric(mixed $value): bool {
        return is_numeric($value);
    }

    public function assertIsObject(mixed $value): bool {
        return is_object($value);
    }

    public function assertIsResource(mixed $value): bool {
        return is_resource($value);
    }

    public function assertIsScalar(mixed $value): bool {
        return is_scalar($value);
    }

    public function assertIsString(mixed $value): bool {
        return is_string($value);
    }

    public function assertNull(mixed $value): bool {
        return is_null($value);
    }
}