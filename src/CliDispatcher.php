<?php

namespace Phore\Cli;

use Phore\Cli\Exception\CliException;
use Phore\Cli\Types\T_CommandGroup;
use Phore\Cli\Types\T_CommandSet;

class CliDispatcher
{


    private static T_CommandSet $commandSet;


    public static function getCommandSet() : T_CommandSet {
        if ( ! isset (self::$commandSet))
            self::$commandSet = new T_CommandSet();
        return self::$commandSet;
    }

    public static function autoload() {

    }

    public static function addClass(string $className)
    {
        self::getCommandSet()->addCommand(T_CommandGroup::CreateFromClassName($className));
    }

    public static function run(array $argv, int $argc)
    {
        $name = array_shift($argv);
        self::getCommandSet()->setName($name);

        try {
            self::getCommandSet()->dispatch($argv);
            echo "\n";
        } catch (CliException $e) {
            $e->visualize();
        }
    }
}
