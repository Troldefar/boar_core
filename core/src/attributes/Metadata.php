<?php

namespace app\core\src\attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY)]
class Metadata {
    public function __construct(
        public string $type,
        public ?string $description
    ) {}
}