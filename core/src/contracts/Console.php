<?php

namespace app\core\src\contracts;

interface Console {

    public function run(array $args): void;

}