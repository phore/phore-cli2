<?php

namespace Phore\Cli\Types;

use Phore\Cli\Exception\CliException;

class T_CommandSet extends T_CommandGroup
{


        public function __construct(
             string $desc = "<no description>"
        ){
            parent::__construct("", $desc);
        }

        public function addCommand(T_Command $command) : void
        {
            $this->commands[] = $command;
        }

        public function getHelp() : string {
            $sig =  "\n" . $this->name . " [group_name] [--parameters] [command]\n\n" . $this->desc . "\n";
            foreach ($this->commands as $command) {
                $sig .= "\n\n" . $command->getHelp();
            }
            return $sig;
        }

        public function setName($naem) {
            $this->name = $naem;
        }

        public function dispatch(array $argv, array &$arguments = [], $object = null) : void {


            $command = $this->getNextCommand($argv, $arguments);

            if ($command === null) {
                echo $this->getHelp();
                return;
            }
            foreach ($this->commands as $cmd) {
                if ($cmd->name === $command) {
                    $cmd->dispatch($argv, $arguments, $object);
                    return;
                }
            }
            throw new CliException("Command '$command' not found.");
        }

}
