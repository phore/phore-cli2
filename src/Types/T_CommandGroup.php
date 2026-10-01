<?php

namespace Phore\Cli\Types;

use Phore\Cli\Annotation\CliScope;
use Phore\Cli\Exception\CliException;

class T_CommandGroup extends T_Command
{
    /** @var T_Command[] */
    public array $commands = [];

    /** @var array<string, self> */
    private array $commandOwners = [];

    public function __construct(
        string $name,
        string $desc = '',
        private ?\ReflectionClass $reflectionClass = null
    ) {
        parent::__construct($name, $desc);
    }

    public function addCommand(T_Command $command): void
    {
        if (isset($this->commandOwners[$command->name])) {
            throw new CliException("Command '{$this->name} {$command->name}' is already registered.");
        }
        $this->commands[] = $command;
        $this->commandOwners[$command->name] = $this;
    }

    /**
     * Adds another class's actions under the same scope, retaining their owner.
     *
     * Example: $group->merge(T_CommandGroup::CreateFromClassName(OtherActions::class));
     *
     * @param self $other Group whose distinct actions become available here.
     * @throws CliException On duplicate actions or conflicting Boolean options.
     * @see \\Phore\\Cli\\Annotation\\CliScope
     */
    public function merge(self $other): void
    {
        foreach ($other->commands as $command) {
            if (isset($this->commandOwners[$command->name])) {
                throw new CliException("Command '{$this->name} {$command->name}' is already registered.");
            }
        }
        foreach ($other->parameters as $parameter) {
            foreach ($this->parameters as $existing) {
                if ($existing->name === $parameter->name && $existing->isBoolean() !== $parameter->isBoolean()) {
                    throw new CliException("Conflicting scope option '{$parameter->getLongName()}' in '{$this->name}'.");
                }
            }
        }

        foreach ($other->commands as $command) {
            $this->commands[] = $command;
            $this->commandOwners[$command->name] = $other;
        }
        foreach ($other->parameters as $parameter) {
            if (!array_filter($this->parameters, fn(T_Parameter $existing) => $existing->name === $parameter->name)) {
                $this->parameters[] = $parameter;
            }
        }
    }

    public function getHelp(bool $detailed = true, int $nameWidth = 0): string
    {
        $stub = "\n" . $this->name . "\t" . $this->desc;
        foreach ($this->parameters as $parameter) {
            if ($detailed || ! $parameter->isOptional) {
                $stub .= "\n\t" . $parameter->getHelp();
            }
        }
        $nameWidth = 0;
        foreach ($this->commands as $command) {
            $nameWidth = max($nameWidth, strlen($command->name . ($command->hasArgvParameters ? " [argv]" : "")));
        }
        foreach ($this->commands as $command) {
            $stub .= $command->getHelp($detailed, $nameWidth);
        }
        return $stub;
    }

    public function dispatch(array $argv, array &$arguments, $object = null): void
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
        $owner = $this->commandOwners[$command] ?? null;
        if ($owner === null) {
            throw new CliException("Command '$command' not found.");
        }

        $reflection = $owner->reflectionClass;
        $instance = $reflection->newInstance(...$owner->buildParametersFor($reflection->getConstructor(), $arguments));
        foreach ($this->commands as $action) {
            if ($action->name === $command) {
                $action->dispatch($argv, $arguments, $instance);
                return;
            }
        }
    }

    public static function CreateFromClassName(string $className): self
    {
        $reflection = new \ReflectionClass($className);
        $attributes = $reflection->getAttributes(CliScope::class);
        $name = $attributes === []
            ? strtolower($reflection->getShortName())
            : $attributes[0]->newInstance()->name;
        $group = new self($name, '', $reflection);

        foreach ($reflection->getConstructor()?->getParameters() ?? [] as $parameter) {
            $group->addParameter(T_Parameter::CreateFromReflection($parameter));
        }
        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->getName() !== '__construct') {
                $group->addCommand(T_Command::CreateFromReflection($method));
            }
        }
        return $group;
    }
}
