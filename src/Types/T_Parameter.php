<?php

namespace Phore\Cli\Types;

use Phore\Cli\Annotation\CliParameter;

class T_Parameter
{
    public function __construct(
        public string $name,
        public string $description,
        public bool $isOptional = true,
        public ?\ReflectionParameter $reflectionParameter = null
    ) {

    }

    public function getLongName() : string {
        return "--" . $this->name;
    }

    public function getHelp() : string {
        return $this->getLongName() . " [value] " . $this->description;
    }

    public static function CreateFromReflection(\ReflectionParameter $parameter) : self {
        $pAttr = $parameter->getAttributes(CliParameter::class);
        if (count($pAttr) === 0){
            return new self($parameter->getName(), "<no description>", $parameter->isOptional(), $parameter);
        }

        /* @var $param CliParameter */
        $param = $pAttr[0]->newInstance();
        return new self($param->name, $param->desc, $parameter->isOptional(), $parameter);
    }

}
