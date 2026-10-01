<?php

namespace spec\Phore\Cli;

use PhpSpec\ObjectBehavior;

class CliConsoleSpec extends ObjectBehavior
{
    public function it_runs_mock_cli_help_as_a_real_console_process(): void
    {
        $command = escapeshellarg(PHP_BINARY)
            . ' '
            . escapeshellarg(__DIR__ . '/MockCli.php')
            . ' mock init --help 2>&1';

        exec($command, $output, $exitCode);
        $help = implode("\n", $output);

        if ($exitCode !== 0) {
            throw new \RuntimeException("Mock CLI failed with exit code {$exitCode}:\n{$help}");
        }
        if ( ! str_contains($help, 'Initializes the mock project')) {
            throw new \RuntimeException('Mock CLI help must show the command description.');
        }
        if ( ! str_contains($help, '--name <value>')) {
            throw new \RuntimeException('Mock CLI help must show the required parameter.');
        }
        if ( ! str_contains($help, '[--force]')) {
            throw new \RuntimeException('Mock CLI help must show the optional parameter.');
        }
    }

    public function it_executes_mock_cli_command_as_a_real_console_process(): void
    {
        $command = escapeshellarg(PHP_BINARY)
            . ' '
            . escapeshellarg(__DIR__ . '/MockCli.php')
            . ' mock init --name demo --force 2>&1';

        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            throw new \RuntimeException(
                'Mock CLI failed with exit code ' . $exitCode . ': ' . implode("\n", $output)
            );
        }
        if (trim(implode("\n", $output)) !== 'demo:force') {
            throw new \RuntimeException('Mock CLI command output is unexpected.');
        }
    }
}
