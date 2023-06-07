<?php

namespace Phore\Cli\Types;

use Phore\Cli\Exception\CliException;

class T_CommandSet
{

        public array $commands = [];

        public function __construct(
            public string $name,
            public string $desc = "<no description>"
        ){}

        public function addCommand(T_Command $command) : void
        {
            $this->commands[] = $command;
        }

        public function getHelp() : string {
            $sig =  "\t" . $this->name . "\n\t\t" . $this->desc . "\n";
            foreach ($this->commands as $command) {
                $sig .= "\n\t\t" . $command->getHelp();
            }
            return $sig;
        }

        public function dispatch(array $argv, array $arguments = []) {

            if ($argv[0] === "help" || count($argv) === 0) {
                echo $this->getHelp();
                return;
            }


            while($curArg = array_shift($argv)) {
                if (startsWith($curArg, "--")) {
                    $arguments[$curArg] = array_shift($argv);
                    continue;
                }
                if (startsWith($curArg, "-")) {
                    $arguments[$curArg] = true;
                    continue;
                }
                // find command by name in $this->commands
                foreach ($this->commands as $command) {
                    if ($command->name === $curArg) {
                        return $command->dispatch($argv, $arguments);
                    }
                }
                throw new CliException("Unknown command '$curArg'");

            }


        }

}
