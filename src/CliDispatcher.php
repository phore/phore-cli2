<?php

namespace Phore\Cli;

class CliDispatcher
{



    public static function autoload() {

    }

    public static function addClass(string $className)
    {
        $module->registerCommands();
    }

    public static function run(array $argv, int $argc)
    {

        if ($command === null)
            throw new \InvalidArgumentException("Module CommandModule from library 'brace/command' is not part of app. Run addModule() to add it.");

        array_shift($argv);
        $cmd = array_shift($argv);
        if ($cmd === null)
            $cmd = "help";

        if ( ! isset ($command->commands[$cmd])) {
            echo "Command undefined '$cmd'!\n";
            exit(255);
        }
        $command->runCommand($cmd, $argv);
        echo "\n";
    }
}
