<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Phore\Cli\Annotation\CliCommand;
use Phore\Cli\Annotation\CliParameter;
use Phore\Cli\Annotation\CliScope;
use Phore\Cli\CliApplication;

#[CliScope('mock')]
final class MockCli
{
    #[CliCommand('init', 'Initializes the mock project', 'Initializes the mock project for console testing.')]
    public function init(
        #[CliParameter('name', 'Project name')]
        string $name,
        #[CliParameter('force', 'Force initialization')]
        bool $force = false,
    ): void {
        echo $name . ($force ? ':force' : '');
    }
}

$app = new CliApplication();
$app->addClass(MockCli::class);
$app->run($argv);
