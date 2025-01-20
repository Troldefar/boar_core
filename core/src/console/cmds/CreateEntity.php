<?php

namespace app\core\src\console\cmds;

use \app\core\src\contracts\Console;

use \app\core\src\miscellaneous\CoreFunctions;

use \app\core\src\File;

class CreateEntity implements Console {

    protected array $entityTypes = ['controller', 'model', 'migration', 'view'];

    public function run(array $args): void {
        $entityName = CoreFunctions::first($args)->scalar;
        
        $this->checkEntityExistence($entityName);

        echo "Creating entity: $entityName\n";

        foreach ($this->entityTypes as $type) {
            $method = "create" . ucfirst($type);
            if (method_exists($this, $method)) $this->$method(ucfirst($entityName));
        }
    }

    private function checkEntityExistence(string $entityName): void {
        $filename = "models/{$entityName}Model.php";

        if (!file_exists($filename)) return;

        exit('Entity already exists - Aborting operation');
    }

    protected function createController(string $name): void {
        $content = <<<EOT
        <?php

        namespace app\controllers;

        use \app\core\src\Controller;

        final class {$name}Controller extends Controller {

            public function index() {
                return \$this->setFrontendTemplateAndData('$name', []);
            }

        }
        EOT;

        $fileName = "controllers/{$name}Controller.php";

        File::putContent($fileName, $content);

        echo "Created controller: $fileName\n";
    }

    protected function createView(string $name): void {
        $content = <<<EOT
        Im a template file for $name!
        EOT;

        $fileName = "views/{$name}.tpl.php";

        File::putContent($fileName, $content); 

        echo "Created view: $fileName\n";
    }

    protected function createModel(string $name): void {
        $content = <<<EOT
        <?php

        namespace app\models;

        use \app\core\src\database\Entity;

        final class {$name}Model extends Entity {

            public function getTableName(): string {
                return '{$name}';
            }
                
            public function getKeyField(): string {
                return '{$name}ID';
            }
            
        }
        EOT;

        $fileName = "models/{$name}Model.php";

        File::putContent($fileName, $content);

        echo "Created model: $fileName\n";
    }

    private function formatMigrationName(string $name): string {
        return 'add_'.strtolower($name).'_table_'.date('Y_m_d', strtotime('now')).'_0001';
    }

    protected function createMigration(string $name): void {
        $migrationName = $this->formatMigrationName($name);
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

        echo "Created migration: $fileName\n";
    }
}