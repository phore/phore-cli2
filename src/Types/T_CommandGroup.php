<?php

namespace Phore\Cli\Types;

use Phore\Cli\Annotation\CliParameter;

class T_CommandGroup extends T_Command
{

    public array $commands = [];

    public function __construct(
         string $name,
         string $desc = "<no description>"
    ){
        parent::__construct($name, $desc);
    }



    public function addCommand(T_Command $command) : void
    {
        $this->commands[] = $command;
    }

    public function getHelp(): string
    {
        $stub = "\t" . $this->name . "\n\t\t" . $this->desc . "\n";
        foreach ($this->parameters as $parameter) {
            $stub .= "\n\t\t" . $parameter->getHelp();
        }
        foreach ($this->commands as $command) {
            $stub .= "\n" . $command->getHelp();
        }
        return $stub;
    }


    public static function CreateFromClassName(string $className) : self {
        $reflection = new \ReflectionClass($className);
        $cmdGroup = new self($className);

        // Parse Constructor Parameters
        foreach ($reflection->getConstructor()->getParameters() as $parameter) {
            $cmdGroup->addParameter(T_Parameter::CreateFromReflection($parameter));
        }

        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $cmdAttr = $method->getAttributes(\Phore\Cli\Annotation\CliCommand::class);
            if (count($cmdAttr) === 0)
                continue;
            $cmd = new T_Command($cmdAttr[0]->newInstance()->name, $cmdAttr[0]->newInstance()->desc);
            foreach ($method->getParameters() as $parameter) {
                $cmd->addParameter(T_Parameter::CreateFromReflection($parameter));
            }
            $cmdGroup->addCommand($cmd);
        }
    }

}
