<?php

namespace app\core\src\unittest;

use \app\core\src\contracts\UnitTest;

use \app\core\src\unittest\src\Assert;

class TestCase implements UnitTest {

    use Assert;

    public function run(): mixed {
        return '';
    }

}