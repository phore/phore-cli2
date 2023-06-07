<?php

namespace Phore\Cli\Types;

class T_CommandGroup
{

    public function __construct(
        public string $name,
        public string $desc = "<no description>"
    ){}


    public function addCommand(T_Command|T_CommandGroup $command) : void
    {

    }


}
