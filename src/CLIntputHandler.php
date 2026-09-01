<?php

namespace Phore\Cli;

use Phore\Cli\Input\TerminalInputReader;

class CLIntputHandler
{
    const ANSI_BOLD = "\033[1m";

    const ANSI_RESET = "\033[0m";

    public function askLine(string $question) : string {
        $val = readline($question . ": ");
        return $val;
    }

    public function out(string $msg) {
        $blueBackground = "\033[44m";  // Blue background
        $blackText = "\033[30m";      // Black text
        $reset = "\033[0m";           // Reset to terminal's default

        echo $blueBackground . $blackText . $msg . $reset . PHP_EOL;
    }

    public function askMultiLine(string $question) : string {
        return (new TerminalInputReader())->read($question);
    }


    public function askBool(string $question, bool $default = false) : bool {
        // Print default value uppercase
        if ($default)
            $question .= " (Y/n) ";
        else
            $question .= " (y/N) ";
        file_put_contents("php://stderr", PHP_EOL . self::ANSI_BOLD . $question .  self::ANSI_RESET);
        $val = readline();
        if ($val === "y")
            return true;
        if ($val === "n")
            return false;
        return $default;

    }

}
