<?php

namespace app\core\src\attributes;

#[\Attribute]
class ParameterDetails {
    public function __construct(
        public string $description,
        public string $type,
        public bool $required = true,
        public mixed $default = null,
        public mixed $example = null
    ) {}
}