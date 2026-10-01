<?php

namespace Phore\Cli\Types;

use Phore\Cli\CliPreset;
use Phore\Cli\Exception\CliException;

class T_CommandSet extends T_CommandGroup
{
    public $cliPresets = [];

    private ?CliPreset $runtimePreset = null;

    public function __construct(string $desc = '')
    {
        parent::__construct('', $desc);
    }

    public function addCommand(T_Command $command): void
    {
        if ($command instanceof T_CommandGroup) {
            foreach ($this->commands as $existing) {
                if ($existing->name === $command->name && $existing instanceof T_CommandGroup) {
                    $existing->merge($command);
                    return;
                }
            }
        }
        $this->commands[] = $command;
    }

    public function addCliPreset(CliPreset $preset): void
    {
        $this->cliPresets[] = $preset;
    }

    public function setCliPreset(CliPreset $preset): void
    {
        if ($this->runtimePreset !== null) {
            $this->cliPresets = array_values(array_filter(
                $this->cliPresets,
                fn(CliPreset $entry) => $entry !== $this->runtimePreset
            ));
        }
        $this->runtimePreset = $preset;
        $this->cliPresets[] = $preset;
    }

    public function getHelp(bool $detailed = true, int $nameWidth = 0): string
    {
        $sig = "\n" . $this->name . " [group_name] [--parameters] [command]\n\n" . $this->desc . "\n";
        $nameWidth = 0;
        foreach ($this->commands as $command) {
            $nameWidth = max($nameWidth, strlen($command->name));
        }
        foreach ($this->commands as $command) {
            $sig .= $command->getHelp($detailed, $nameWidth);
        }
        foreach ($this->cliPresets as $preset) {
            $sig .= $preset->getHelp();
        }
        return $sig;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function dispatch(array $argv, array &$arguments = [], $object = null): void
    {
        if (($argv[0] ?? null) === "-h" || ($argv[0] ?? null) === "--help") {
            echo $this->getHelp(true);
            return;
        }

        $command = $this->getNextCommand($argv, $arguments);
        if ($command === null) {
            echo $this->getHelp(false);
            return;
        }
        foreach ($this->commands as $cmd) {
            if ($cmd->name === $command) {
                $cmd->dispatch($argv, $arguments, $object);
                return;
            }
        }
        throw new CliException("Command '$command' not found.");
    }
}
