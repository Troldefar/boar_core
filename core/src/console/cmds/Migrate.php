<?php

namespace app\core\src\console\cmds;

use \app\core\src\contracts\Console;
use \app\core\src\database\Migration;

class Migrate implements Console {

    public function run(array $args): void {
        (new Migration())->applyMigrations();
    }

}
