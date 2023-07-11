<?php

namespace Phore\Cli;

class CLIntputHandler
{

    public function askString(string $question) : string {
        $val = readline($question);
        return $val;
    }

    public function askBool(string $question, bool $default = false) : bool {
        // Print default value uppercase
        if ($default)
            $question .= " (Y/n) ";
        else
            $question .= " (y/N) ";
        $val = readline($question);
        if ($val === "y")
            return true;
        if ($val === "n")
            return false;
        return $default;
        
    }

}
