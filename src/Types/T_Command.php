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
        while (($cur = array_shift($argv)) !== null) {
            if (str_starts_with($cur, "--")) {
                [$name, $value] = array_pad(explode("=", $cur, 2), 2, null);
                $param = $this->findParameterByLongName($name);

                if ($param?->isBoolean()) {
                    if ($value !== null) {
                        $arguments[$name] = $this->parseBooleanValue($value, $name);
                        continue;
                    }

                    $next = $argv[0] ?? null;
                    if ($next !== null && $this->isBooleanValue($next)) {
                        $arguments[$name] = $this->parseBooleanValue(array_shift($argv), $name);
                        continue;
                    }

                    $arguments[$name] = true;
                    continue;
                }

                $arguments[$name] = $value ?? array_shift($argv);
                continue;
            }
            if (str_starts_with($cur, "-")) {
                $arguments[$cur] = true;
                continue;
            }
            return $cur;
        }
        return null;
    }

    private function findParameterByLongName(string $name) : ?T_Parameter
    {
        foreach ($this->parameters as $parameter) {
            if ($parameter->getLongName() === $name) {
                return $parameter;
            }
        }
        return null;
    }

    private function isBooleanValue(mixed $value) : bool
    {
        if (is_bool($value)) {
            return true;
        }
        if ( ! is_string($value)) {
            return false;
        }
        return in_array(strtolower($value), ["1", "0", "true", "false", "yes", "no", "on", "off"], true);
    }

    private function parseBooleanValue(mixed $value, string $parameterName) : bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if ( ! is_string($value)) {
            throw new CliException("Invalid boolean value for parameter $parameterName.");
        }

        return match (strtolower($value)) {
            "1", "true", "yes", "on" => true,
            "0", "false", "no", "off" => false,
            default => throw new CliException("Invalid boolean value for parameter $parameterName: $value")
        };
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
            $param = array_values(array_filter($this->parameters, fn(T_Parameter $p) => $p->reflectionParameter?->getName() === $parameter->name));

            if (count ($param) === 1)  {
                $param = $param[0];
                assert ($param instanceof T_Parameter);
                if (isset($arguments[$param->getLongName()])) {
                    $value = $arguments[$param->getLongName()];
                    $ret[]=  $param->isBoolean() ? $this->parseBooleanValue($value, $param->getLongName()) : $value;
                    continue;
                }

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
