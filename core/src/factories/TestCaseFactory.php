<?php

namespace app\core\src\factories;

use \app\core\src\unittest\TestCase;

class TestCaseFactory extends AbstractFactory {

    protected const TEST_NAMESPACE = '\\app\tests\\';

    public function create(): ?TestCase {
        $test = self::TEST_NAMESPACE . $this->getHandler();
        if (!$this->validateObject($test)) return null;

        return new $test();
    }

}