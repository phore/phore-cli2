<?php

namespace Phore\Cli\Types;

use Phore\Cli\Annotation\CliParameter;
use Phore\Cli\Exception\CliException;

class T_CommandGroup extends T_Command
{

    /**
     * @var T_Command[]
     */
    public array $commands = [];

    public function __construct(
         string $name,
         string $desc = "<no description>",
         private ?\ReflectionClass $reflectionClass = null
    ){
        parent::__construct($name, $desc);
    }



    public function addCommand(T_Command $command) : void
    {
        $this->commands[] = $command;
    }

    public function getHelp(): string
    {
        $stub = "" . $this->name . "\t" . $this->desc . "\n";
        foreach ($this->parameters as $parameter) {
            $stub .= "\n\t" . $parameter->getHelp();
        }
        foreach ($this->commands as $command) {
            $stub .= "\n" . $command->getHelp();
        }
        return $stub;
    }

    public function dispatch(array $argv, array &$arguments, $object = null) : void
    {
        $command = $this->getNextCommand($argv, $arguments);
        if ($command === null) {
            echo $this->getHelp();
            return;
        }
        $object = $this->reflectionClass->newInstance(...$this->buildParametersFor($this->reflectionClass->getConstructor(), $arguments));

        foreach ($this->commands as $cmd) {
            if ($cmd->name === $command) {
                $cmd->dispatch($argv, $arguments, $object);
                return;
            }
        }
        throw new CliException("Command '$command' not found.");
    }


    public static function CreateFromClassName(string $className) : self {
        $reflection = new \ReflectionClass($className);
        $cmdGroup = new self(strtolower($reflection->getShortName()), "<no description>", $reflection);

        // Parse Constructor Parameters
        foreach ($reflection->getConstructor()?->getParameters() ?? [] as $parameter) {
            $cmdGroup->addParameter(T_Parameter::CreateFromReflection($parameter));
        }

        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->getName() === "__construct")
                continue;
            $cmdGroup->addCommand(T_Command::CreateFromReflection($method));
        }
        return $cmdGroup;
    }

}
