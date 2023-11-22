<?php

namespace Phore\Cli\Input;

use Phore\Cli\CLIntputHandler;

class In
{

    public static function AskLine(string $question) : string
    {
        $ih = new CLIntputHandler();
        return $ih->askLine($question);
    }

    public static function AskBool(string $question, bool $default = false) : bool
    {
        $ih = new CLIntputHandler();
        return $ih->askBool($question, $default);
    }

    public static function AskMultiLine(string $question) : string
    {
        $ih = new CLIntputHandler();
        return $ih->askMultiLine($question);
    }

}
