<?php

namespace Phore\Cli\Format;

use Phore\Cli\Exception\CliException;

class ExceptionVisualizer
{

    private function visualizeException(\Exception|\Error $exception) {
        // Set some ANSI color codes
        $red = "\033[31m";
        $yellow = "\033[33m";
        $cyan = "\033[36m";
        $reset = "\033[0m";
        $bold = "\033[1m";
        $gray = "\033[0;37m";

        // Get exception details
        $class = get_class($exception);
        $message = $exception->getMessage();
        $file = $exception->getFile();
        $line = $exception->getLine();
        $trace = explode("\n", $exception->getTraceAsString());

        // Output the exception details
        echo $red . $bold . "Exception: " . $reset . $cyan . $class . $reset . "\n";
        echo $yellow  . "Message: " . $reset . $bold . $message . $reset . "\n";
        echo $yellow . "File: " . $reset . $file . "\n";
        echo $yellow . "Line: " . $reset . $line . "\n";

        echo $red . "Trace: " . $reset . "\n";
        echo $bold . array_shift($trace) . $reset . "\n"; // Print the first line of the trace in bold
        if (count ($trace) > 0)
             echo array_shift($trace) . $reset . "\n"; // Print the first line of the trace in bold

        foreach($trace as $line) { // Print the remaining lines of the trace
            if (strpos($line, ": Phore\\Cli\\") === false && strpos($line, " {main}") === false) {
                 echo $line . $reset . "\n";
            } else {
                echo $gray . $line . $reset . "\n";
            }
        }
    }

    private function visualizeInputException(CliException $cliException) {
         $red = "\033[31m";
        $reset = "\033[0m";
        $bold = "\033[1m";
        // Get exception message
        $message = $cliException->getMessage();

        // Output the exception message
        echo $red . $bold . "Input error: " . $reset . $bold . $message . "\n";
    }


    public function visualize(\Exception|\Error $exception) {
        if ($exception instanceof CliException)
            return $this->visualizeInputException($exception);
        return $this->visualizeException($exception);
    }

}
