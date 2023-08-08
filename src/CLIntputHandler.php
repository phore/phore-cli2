<?php

namespace Phore\Cli;

class CLIntputHandler
{
    const ANSI_BOLD = '\033[1m';

    const ANSI_RESET = '\033[0m';

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
        echo "$question: (end with empty CTRL-X)\n";
        $input = '';

        while (true) {
            // Read input character by character
            $char = fread(STDIN, 1);

            // Handle Ctrl+X to terminate the input process
            if ($char === "\x18") {  // ASCII code for Ctrl+X
                echo PHP_EOL . "Input process ended." . PHP_EOL;
                return $input;
            }

            // Append the character to the input
            $input .= $char;
        }

        return $input;
    }


    public function askBool(string $question, bool $default = false) : bool {
        // Print default value uppercase
        if ($default)
            $question .= " (Y/n) ";
        else
            $question .= " (y/N) ";
        $val = readline(self::ANSI_BOLD . $question .  self::ANSI_RESET);
        if ($val === "y")
            return true;
        if ($val === "n")
            return false;
        return $default;

    }

}
