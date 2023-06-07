<?php

namespace Phore\Cli;

class CliDispatcher
{


    private static CommandRegistry $commandRegistry;


    public static function getCommandRegistry() : CommandRegistry {
        if ( ! isset (self::$commandRegistry))
            self::$commandRegistry = new CommandRegistry();
        return self::$commandRegistry;
    }

    public static function autoload() {

    }

    public static function addClass(string $className)
    {
        self::getCommandRegistry()->addClass($className);
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
