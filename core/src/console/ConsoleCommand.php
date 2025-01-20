<?php

namespace app\core\src\console;

use \app\core\src\CLI;

use \app\core\src\console\cmds\CreateEntity;
use \app\core\src\console\cmds\CreateTest;
use \app\core\src\console\cmds\Migrate;
use \app\core\src\console\cmds\NewMigration;
use \app\core\src\console\cmds\SeedDatabase;
use \app\core\src\console\cmds\UnitTest;

use \app\core\src\contracts\Console;

class ConsoleCommand {

    private string $command;

    private array $commands = [
        'create-entity' => CreateEntity::class,
        'migrate'       => Migrate::class,
        'new-migration' => NewMigration::class,
        'seed-database' => SeedDatabase::class,
        'create-test'   => CreateTest::class,
        'unit-test'     => UnitTest::class,
    ];

    private Console $cmd;

    private const USAGE_TEXT = 'Unknow command was provided. Usage: php boar {{command}} {{args}' . PHP_EOL;

    public function __construct(
        private array $arguments,
        public CLI $cli = new CLI()
    ) {
        $this->setCommand();
        $this->removeRedundantArgs();
    }

    private function getCLI(): CLI {
        return $this->cli;
    }

    private function setCommand() {
        if (!isset($this->arguments[1])) return $this->help();

        $this->command = $this->arguments[1];
    }

    private function removeRedundantArgs() {
        unset($this->arguments[0], $this->arguments[1]);
    }

    public function setCmd(): self|string {
        if (!isset($this->commands[$this->command])) exit($this->printUsage());

        $this->cmd = new $this->commands[$this->command]($this->getCLI());

        return $this;
    }

    public function run(): void {
        $this->setCmd();
        $this->cmd->run($this->arguments);
    }

    public function help() {
        $cmds = implode(PHP_EOL, array_keys($this->commands));

        $help = <<<EOT
        Usage: php boar [options...]

        Current commands:

        $cmds
        EOT;

        exit(echoCLI($help));
    }

    protected function printUsage(): string {
        return self::USAGE_TEXT;
    }

}