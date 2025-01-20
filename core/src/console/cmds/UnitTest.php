<?php

namespace app\core\src\console\cmds;

use \app\core\src\CLI;

use \app\core\src\contracts\Console;

use \app\core\src\File;

use \app\core\src\factories\TestCaseFactory;

class UnitTest implements Console {

    private array $testFiles = [];

    private int $nestedDirDepth = 4;

    private int $succededTests = 0;
    private int $failedTests = 0;

    public function __construct(
        private CLI $cli
    ) {
        $this->testFiles = File::getNonHiddenFiles(dirname(__DIR__, $this->nestedDirDepth) . '/tests');
        
        $this->stdin(PHP_EOL . '🚀 Ready to run tests: ' . count($this->testFiles), 'cyan');
    }

    public function run(array $args): void {
        array_map(function($file) {
            $handler = preg_replace('/' . File::PHP_EXTENSION . '/', '', $file);
            
            $start = hrtime(true);

            $test = (new TestCaseFactory(compact('handler')))->create();
            $result = $test->run();

            $result ? $this->succededTests++ : $this->failedTests++;

            $end = hrtime(true);

            $timeTaken = ($end - $start) / 1e9;

            $this->displayResultViaCLI($result, $handler, $timeTaken);
        }, $this->testFiles);

        $this->stdin('🎉 Tests (' . $this->succededTests . ') completed' . PHP_EOL, 'cyan');
        $this->stdin('😿 Tests (' . $this->failedTests . ') failed' . PHP_EOL, 'cyan');
    }

    private function displayResultViaCLI(mixed $result, string $handler, float $timeTaken): void {
        $status = $result ? '✅ SUCCESS' : '❌ FAILURE';

        $doneAt = date('d-m-Y H:i:s', time());

        $timeTakenFormatted = number_format($timeTaken, 6, '.', '');

        $output = <<<EOT

        ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        🔍 Test File: $handler
        📋 Status: $status
        🕣 Executed At: $doneAt
        ⏳ Duration: $timeTakenFormatted seconds
        ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        
        EOT;

        $this->stdin($output, 'yellow');
    }

    private function stdin(string $message, string $color): void {
        $this->cli->printWithColor($message, $color);
    }
}
