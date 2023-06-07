<?php

namespace Phore\Cli\Annotation;


#[\Attribute(\Attribute::TARGET_PARAMETER)]
class CliParameter
{
    public function __construct(
        /**
         * If null: Use parameter name as long name
         */
        public ?string $name = null,
        public string $desc = "<no description>",
    ){}
}
