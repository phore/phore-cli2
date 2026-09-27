<?php

namespace Phore\Cli;

use Phore\Cli\Format\ExceptionVisualizer;
use Phore\Cli\Types\T_CommandSet;

/**
 * Backward compatible static access to the default CLI application.
 */
class CliDispatcher
{
    private static ?CliApplication $default = null;

    private static function getDefault(): CliApplication
    {
        return self::$default ??= new CliApplication();
    }

    public static function getCommandSet(): T_CommandSet
    {
        return self::getDefault()->getCommandSet();
    }

    public static function autoload(): void
    {
    }

    public static function addClass(string $className): void
    {
        self::getDefault()->addClass($className);
    }

    /**
     * Runs the default application and prints its output or a formatted error.
     *
     * Example: CliDispatcher::run($argv);
     *
     * @param array<int, string> $argv
     * @param int|null $argc Retained for compatibility.
     * @return void
     * @see CliApplication::run()
     */
    public static function run(array $argv, int $argc = null): void
    {
        try {
            echo self::getDefault()->run($argv);
        } catch (\Exception|\Error $exception) {
            (new ExceptionVisualizer())->visualize($exception);
        }
    }
}
