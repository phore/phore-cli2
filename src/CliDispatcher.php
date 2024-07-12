<?php

namespace Phore\Cli;

use Http\Client\Exception;
use Phore\Cli\Exception\CliException;
use Phore\Cli\Format\ExceptionVisualizer;
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


    /**
     * Run a command
     * 
     * If used manually: pass each argument as array element
     * 
     * @param array $argv
     * @param int|null $argc
     * @return void
     */
    public static function run(array $argv, int $argc = null)
    {
        if ($argc === null)
            $argc = count($argv);
        $exceptionVisualizer = new ExceptionVisualizer();

        try {
            $name = array_shift($argv);
            self::getCommandSet()->setName($name);

            $presetContainer = new CliPreset();
            self::getCommandSet()->addCliPreset($presetContainer);

            if (file_exists(getcwd() . "/cli_presets.ini")) {
                $presetContainer->loadPresets(getcwd() . "/cli_presets.ini");
            }

            // Check for preset
            $arguments = [];
            if (str_starts_with($argv[0] ?? "", ":")) {
                $presetName = array_shift($argv);
                $newArgv = $presetContainer->getPreset($presetName, $arguments);
                if ($newArgv !== null) {
                    array_unshift($argv, ...$newArgv);
                }
            }


            self::getCommandSet()->dispatch($argv, $arguments);
            echo "\n";

        } catch (\Exception|\Error $e) {
            $exceptionVisualizer->visualize($e);
        }

    }
}
