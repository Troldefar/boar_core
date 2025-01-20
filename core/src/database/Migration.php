<?php

/**
|----------------------------------------------------------------------------
| Migration handler
|----------------------------------------------------------------------------
| 
| @author RE_WEB
| @package core
|
*/

namespace app\core\src\database;

use \app\models\MigrationModel;
use \app\core\src\database\seeders\DatabaseSeeder;
use \app\core\src\factories\MigrationFactory;

class Migration {

    private const SUCCESSFULL_MIGRATION = 'Successfully applied new migration: ';
    private const INVALID_MIGRATION_NAME = 'Invalid migration name ';
    private const MIGRATION_FORMAT = ', must be formatted: migration_yyyy_mm_dd_xxxx';
    
    protected const MIGRATION_DIR = '/migrations/';
    protected const MIGRATION_DATE_LENGTH = 10;
    protected const MIGRATION_DATE_OFFSET = -19;

    public function getAppliedMigrations(): array {
        return (new MigrationModel())->all();
    }

    public function createMigrationsTable() {
        (new Schema())->up('Migrations', function(table\Table $table) {
            $table->increments('MigrationID');
            $table->varchar('Migration', table\Table::MAX_COLUMN_LENGTH);
            $table->timestamp();
            $table->primaryKey('MigrationID');
        });
    }

    public function applyMigrations() {
        $this->createMigrationsTable();
        $appliedMigrations = $this->getAppliedMigrations();

        $migrationsFolder = app()::$ROOT_DIR . self::MIGRATION_DIR;
        $migrations = scandir($migrationsFolder);
        $mappedMigrations = array_map(fn($object) => $object->Migration, $appliedMigrations);
        $missingMigrations = [];

        foreach ($migrations as $migration) {
            $migrationFile = $migrationsFolder . $migration;
            $actualMigration = str_replace('.php', '', $migration);
            
            if (!is_file($migrationFile) || in_array($actualMigration, $mappedMigrations)) continue;

            $date = preg_replace('/\_/', '-', substr(substr($migration, self::MIGRATION_DATE_OFFSET), 0, self::MIGRATION_DATE_LENGTH));
            if (!strtotime($date)) app()->log(self::INVALID_MIGRATION_NAME . ($migration) . self::MIGRATION_FORMAT, exit: true);

            isset($missingMigrations[strtotime($date)]) ? $missingMigrations[strtotime($date)+1] = $migration : $missingMigrations[strtotime($date)] = $migration;
        }
        
        ksort($missingMigrations);

        $this->iterateMigrations($missingMigrations);
    }

    public function iterateMigrations(array $toBeAppliedMigrations): void {
        $app = app();

        if (!count($toBeAppliedMigrations)) $app->log('No migrations to be applied', exit: true);

        foreach ($toBeAppliedMigrations as $migration) {
            require_once $app::$ROOT_DIR . self::MIGRATION_DIR . $migration;
            $handler = pathinfo($migration, PATHINFO_FILENAME);

            if (strlen($handler) > table\Table::MAX_COLUMN_LENGTH) $app->log("Classname ($handler) is too long!", exit: true);

            (new MigrationFactory(compact('handler')))->create()->up();
            (new MigrationModel())->set(['Migration' => $handler])->save(addMetaData: false);

            $app->log(self::SUCCESSFULL_MIGRATION . $handler);
        }

        $app->log('Done');
    }

    public function seedLanguage() {
       (new DatabaseSeeder())->up('Language', 1); 
    }

}