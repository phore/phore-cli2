<?php

namespace Phore\Cli;

use Phore\Cli\Types\T_CommandGroup;
use Phore\Cli\Types\T_CommandSet;

/**
 * An independent command registry and CLI runner.
 */
class CliApplication
{
    private T_CommandSet $commandSet;

    public function __construct()
    {
        $this->commandSet = new T_CommandSet();
    }

    /**
     * Returns this application's command registry.
     *
     * Example: $app->getCommandSet()->setName('tool');
     *
     * @return T_CommandSet The mutable registry belonging only to this instance.
     * @see T_CommandSet
     */
    public function getCommandSet(): T_CommandSet
    {
        return $this->commandSet;
    }

    /**
     * Registers the public actions of a class in this application.
     *
     * Classes sharing a CliScope contribute distinct actions to one group.
     * Duplicate action names in the same scope throw a CliException.
     *
     * Example: $app->addClass(ProjectActions::class);
     *
     * @param class-string $className Class to reflect and register.
     * @throws \\ReflectionException When the class cannot be reflected.
     * @throws \\Phore\\Cli\\Exception\\CliException On duplicate actions or conflicting options.
     * @see \Phore\Cli\Annotation\CliScope
     */
    public function addClass(string $className): void
    {
        $this->commandSet->addCommand(T_CommandGroup::CreateFromClassName($className));
    }

    /**
     * Executes argv and returns buffered echo output from the command or help.
     *
     * The first element is the executable name. Exceptions propagate to the
     * caller; no output is returned on failure. Action side effects still occur.
     *
     * Example: echo $app->run(['tool', 'project', 'create', '--name', 'demo']);
     *
     * @param array<int, string> $argv
     * @return string Captured echo output followed by a newline.
     * @throws \Throwable
     * @see CliDispatcher::run()
     */
    public function run(array $argv): string
    {
        $name = array_shift($argv);
        $this->commandSet->setName($name ?? '');

        $presetContainer = new CliPreset();
        $presetFile = getcwd() . '/cli_presets.ini';
        if (file_exists($presetFile)) {
            $presetContainer->loadPresets($presetFile);
        }
        $this->commandSet->setCliPreset($presetContainer);

        $arguments = [];
        if (str_starts_with($argv[0] ?? '', ':')) {
            $presetName = array_shift($argv);
            $newArgv = $presetContainer->getPreset($presetName, $arguments);
            if ($newArgv !== null) {
                array_unshift($argv, ...$newArgv);
            }
        }

        ob_start();
        try {
            $this->commandSet->dispatch($argv, $arguments);
            echo "\n";
            return (string) ob_get_clean();
        } catch (\Throwable $exception) {
            ob_end_clean();
            throw $exception;
        }
    }
}
