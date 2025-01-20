<?php

namespace app\core\src\factories;

use \app\core\src\miscellaneous\CoreFunctions;

class CronjobFactory extends AbstractFactory {

    public function create(): \app\core\src\scheduling\Cron {
        $cCronjob = ('\\app\core\\src\\scheduling\\' . $this->getHandler());
        $this->validateObject($cCronjob);
        return new $cCronjob();
    }

}