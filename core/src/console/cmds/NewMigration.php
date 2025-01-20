<?php

namespace app\core\src\console\cmds;

use \app\core\src\CLI;
use \app\core\src\File;

use \app\core\src\contracts\Console;

class NewMigration implements Console {

    public function __construct(
        private CLI $cli
    ) {
    }

    private function stdin(string $message, string $color): void {
        $this->cli->printWithColor($message, $color);
    }

    public function run(array $args): void {
        $key  = 'name';
        $type = 'type';

        $args[$key]  = first($args);
        $args[$type] = last($args);
        
        if (count($args) !== 2) exit($this->stdin(__CLASS__  . ' error: Arguments ('.implode(', ', [$key, $type]).') must be specified', 'red'));

        $this->createMigration($args[$key], $args[$type]);
    }

    private function formatMigrationName(string $name, string $type): string {
        return $type.'_'.strtolower($name).'_table_'.date('Y_m_d', strtotime('now')).'_0001';
    }

    private function createMigration(string $name, string $type): void {
        $migrationName = $this->formatMigrationName($name, $type);
        $tableNamespace = 'use \app\core\src\database\table\Table';

        $content = <<<EOT
        <?php

        /**
        |----------------------------------------------------------------------------
        | Automatically created migration
        |----------------------------------------------------------------------------
        |
        | Adjust table specifications to your needs
        |
        */

        $tableNamespace;
        use \app\core\src\database\Schema;

        class $migrationName {
            public function up() {
                (new Schema())->up('$name', function(Table \$table) {
                    \$table->increments('{$name}ID');
                    \$table->timestamp();
                    \$table->primaryKey('{$name}ID');
                });
            }

            public function down() {
                (new Schema())->down('$name');
            }
        }
        EOT;

        $fileName = "migrations/$migrationName.php";

        File::putContent($fileName, $content);

        $this->stdin('Created migration: ' . $fileName, 'green');
    }

}
