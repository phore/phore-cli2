<?php

namespace Phore\Cli\Annotation;


#[\Attribute(\Attribute::TARGET_METHOD)]
class CliCommand
{
    public function __construct(
        public string|array $name,
        public string $desc = "<no description>"
    ){}
}
