<?php

namespace Phore\Cli\Types;

use Phore\Cli\Annotation\CliParameter;
use Phore\Cli\Exception\CliException;

class T_Command
{

    /**
     * @var T_Parameter[]
     */
    public array $parameters = [];

    public bool $hasArgvParameters = false;

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
        $argv = "";
        if ($this->hasArgvParameters)
            $argv = "\t[argv] ";
        $sig =  "\n\t" . $this->name . $argv . "\t" . $this->desc . "";
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

        foreach($fn->getParameters() as $parameter) {
            if ($parameter->name === "argv") {
                $ret[] = $arguments["argv"];
                continue;
            }
            $param = array_values(array_filter($this->parameters, fn(T_Parameter $p) => $p->name === $parameter->name));

            if (count ($param) === 1)  {
                $param = $param[0];
                assert ($param instanceof T_Parameter);
                if (isset($arguments[$param->getLongName()])) {


                    $ret[]=  $arguments[$param->getLongName()];
                    continue;
                }
                // Handle boolean parameters

                if ($param->isOptional) {
                    $ret[] = $param->reflectionParameter->getDefaultValue();
                    continue;
                }
            }

            throw new CliException("Missing required parameter: " . $parameter->getName());

        }
        return $ret;
    }

    public function dispatch(array $argv, array &$arguments, $object = null) : void {
        $curCmd = $this->getNextCommand($argv, $arguments);
        if ($curCmd !== null)
            array_unshift($argv, $curCmd);

        // Make argv available
        $arguments["argv"] = $argv;

        if ($object !== null) {
            $object->{$this->reflectionFunction->getName()}(...$this->buildParametersFor($this->reflectionFunction, $arguments));
            return;
        }

        $this->reflectionFunction->invoke(...$this->buildParametersFor($this->reflectionFunction, $arguments));
    }


    public static function CreateFromReflection(\ReflectionMethod|\ReflectionFunction $method) : self {
        $cmd = new self($method->getName(), "", $method);
        foreach ($method->getParameters() as $parameter) {
            if ($parameter->name === "argv") {
                $cmd->hasArgvParameters = true;
                continue;
            }
            $cmd->addParameter(T_Parameter::CreateFromReflection($parameter));
        }
        return $cmd;
    }

}
