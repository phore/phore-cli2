<?php

namespace Phore\Cli\Types;

class T_Command
{

    public array $parameters = [];

    public function __construct(
        public string $name,
        public string $desc = "<no description>"
    ){}

    public function addParameter(T_Parameter $parameter) : void
    {
        $this->parameters[] = $parameter;
    }



    public function getHelp() : string {
        $sig =  "\t" . $this->name . "\n\t\t" . $this->desc . "\n";
        foreach ($this->parameters as $parameter) {
            $sig .= "\n\t\t" . $parameter->getHelp();
        }
        return $sig;
    }


    public static function CreateFromReflection(\ReflectionMethod|\ReflectionFunction $method) : self {
        $cmd = new self($method->getName());
        foreach ($method->getParameters() as $parameter) {
            $cmd->addParameter(T_Parameter::CreateFromReflection($parameter));
        }
        return $cmd;
    }

}
