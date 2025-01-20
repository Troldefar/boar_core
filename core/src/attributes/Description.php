<?php

namespace app\core\src\attributes;

#[\Attribute]
class Description {
    public function __construct(
        public string $summary,
        public ?string $author = null,
        public ?string $package = null
    ) {}
}