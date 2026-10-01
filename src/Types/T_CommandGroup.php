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
        foreach ($this->commands as $existing) {
            if ($existing->name !== $command->name) {
                continue;
            }

            if ($existing instanceof self && $command instanceof self) {
                $existing->merge($command);
                return;
            }

            throw new CliException("Command '{$this->name} {$command->name}' is already registered.");
        }

        $this->commands[] = $command;
        if (! $command instanceof self) {
            $this->commandOwners[$command->name] = $this;
        }
    }

    /**
     * Adds another class's actions under the same scope, retaining their owner.
     *
     * Example: $group->merge(T_CommandGroup::CreateFromClassName(OtherActions::class));
     *
     * @param self $other Group whose distinct actions become available here.
     * @throws CliException On duplicate actions or conflicting Boolean options.
     * @see \Phore\Cli\Annotation\CliScope
     */
    public function merge(self $other): void
    {
        foreach ($other->parameters as $parameter) {
            foreach ($this->parameters as $existing) {
                if ($existing->name === $parameter->name && $existing->isBoolean() !== $parameter->isBoolean()) {
                    throw new CliException("Conflicting scope option '{$parameter->getLongName()}' in '{$this->name}'.");
                }
            }
        }

        foreach ($other->commands as $command) {
            $existing = $this->findCommand($command->name);
            if ($existing !== null) {
                if ($existing instanceof self && $command instanceof self) {
                    $existing->merge($command);
                    continue;
                }

                throw new CliException("Command '{$this->name} {$command->name}' is already registered.");
            }

            $this->commands[] = $command;
            if (! $command instanceof self) {
                $this->commandOwners[$command->name] = $other->commandOwners[$command->name] ?? $other;
            }
        }

        foreach ($other->parameters as $parameter) {
            if (! array_filter($this->parameters, fn(T_Parameter $existing) => $existing->name === $parameter->name)) {
                $this->parameters[] = $parameter;
            }
        }

        if ($this->reflectionClass === null && $other->reflectionClass !== null) {
            $this->reflectionClass = $other->reflectionClass;
        }
    }

    public function getHelp(bool $detailed = true, int $nameWidth = 0, bool $includeHint = true): string
    {
        $hasChildren = $this->commands !== [];
        $commandName = $this->name . ($hasChildren ? " [COMMAND]" : "");
        $stub = "\n" . $commandName;

        if ($this->desc !== '') {
            $stub .= "\t" . $this->desc;
        }

        foreach ($this->parameters as $parameter) {
            if ($detailed || ! $parameter->isOptional) {
                $stub .= "\n\t" . $parameter->getHelp();
            }
        }

        $nameWidth = 0;
        foreach ($this->commands as $command) {
            $suffix = $command instanceof self
                ? " [COMMAND]"
                : ($command->hasArgvParameters ? " [argv]" : "");
            $nameWidth = max($nameWidth, strlen($command->name . $suffix));
        }

        foreach ($this->commands as $command) {
            $stub .= $command->getHelp($detailed, $nameWidth, false);
        }

        if ($includeHint) {
            $stub .= $this->getHelpHint();
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

        $selected = $this->findCommand($command);
        if ($selected instanceof self) {
            $selected->dispatch($argv, $arguments);
            return;
        }
        if ($selected === null) {
            throw new CliException("Command '$command' not found.");
        }

        $owner = $this->commandOwners[$command] ?? null;
        if ($owner === null || $owner->reflectionClass === null) {
            throw new CliException("Command '$command' has no action owner.");
        }

        $reflection = $owner->reflectionClass;
        $instance = $reflection->newInstance(...$owner->buildParametersFor($reflection->getConstructor(), $arguments));
        $selected->dispatch($argv, $arguments, $instance);
    }

    private function findCommand(string $name): ?T_Command
    {
        foreach ($this->commands as $command) {
            if ($command->name === $name) {
                return $command;
            }
        }

        return null;
    }

    public static function CreateFromClassName(string $className): self
    {
        $reflection = new \ReflectionClass($className);
        $attributes = $reflection->getAttributes(CliScope::class);
        $path = $attributes === []
            ? [strtolower($reflection->getShortName())]
            : $attributes[0]->newInstance()->getPath();

        $leafName = array_pop($path);
        if ($leafName === null) {
            throw new CliException("Class '$className' has an empty CLI scope.");
        }

        $group = new self($leafName, '', $reflection);

        foreach ($reflection->getConstructor()?->getParameters() ?? [] as $parameter) {
            $group->addParameter(T_Parameter::CreateFromReflection($parameter));
        }
        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->getName() !== '__construct') {
                $group->addCommand(T_Command::CreateFromReflection($method));
            }
        }

        while (($parentName = array_pop($path)) !== null) {
            $parent = new self($parentName);
            $parent->addCommand($group);
            $group = $parent;
        }

        return $group;
    }
}
