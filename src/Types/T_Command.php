<?php

namespace Phore\Cli\Types;

use Phore\Cli\Exception\CliException;

class T_Command
{

    /**
     * @var T_Parameter[]
     */
    public array $parameters = [];

    public function __construct(
        public string $name,
        public string $desc = "<no description>",
        public \ReflectionMethod|\ReflectionFunction|null $reflectionFunction = null
    ){}

    public function addParameter(T_Parameter $parameter) : void
    {
        $this->parameters[] = $parameter;
    }



    public function getHelp() : string {
        $sig =  "\n\t" . $this->name . "\t" . $this->desc . "";
        foreach ($this->parameters as $parameter) {
            $sig .= "\n\t\t" . $parameter->getHelp();
        }
        return $sig;
    }

    protected function getNextCommand(array &$argv, array &$arguments) : ?string {
        while ($cur = array_shift($argv)) {
            if (startsWith($cur, "--")) {
                $arguments[$cur] = array_shift($argv);
                continue;
            }
            if (startsWith($cur, "-")) {
                $arguments[$cur] = true;
                continue;
            }
            return $cur;
        }
        return null;
    }


    protected function buildParametersFor(\ReflectionFunction|\ReflectionMethod|null $fn, array $arguments) {
        if ($fn === null)
            return [];
        $ret = [];
        foreach($this->parameters as $parameter) {
            if (isset ($arguments[$parameter->getLongName()])) {
                $ret[] = $arguments[$parameter->getLongName()];
                continue;
            }
            if ($parameter->isOptional) {
                $ret[] = $parameter->reflectionParameter->getDefaultValue();
                continue;
            }
            throw new CliException("Missing required parameter: " . $parameter->getLongName());

        }
        return $ret;
    }

    public function dispatch(array $argv, array &$arguments, $object = null) : void {
        $this->getNextCommand($argv, $arguments);

        if ($object !== null) {
            $object->{$this->reflectionFunction->getName()}(...$this->buildParametersFor($this->reflectionFunction, $arguments));
            return;
        }

        $this->reflectionFunction->invoke(...$this->buildParametersFor($this->reflectionFunction, $arguments));
    }


    public static function CreateFromReflection(\ReflectionMethod|\ReflectionFunction $method) : self {
        $cmd = new self($method->getName(), "", $method);
        foreach ($method->getParameters() as $parameter) {
            $cmd->addParameter(T_Parameter::CreateFromReflection($parameter));
        }
        return $cmd;
    }

}
