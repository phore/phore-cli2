<?php

namespace Phore\Cli\Exception;

class CliException extends \Exception
{

    public function visualize() : void {
        echo "\n Error: " . $this->getMessage() . "\n";
    }
}
