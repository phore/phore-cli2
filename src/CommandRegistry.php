<?php

namespace Phore\Cli;

class CommandRegistry
{

    private array $commands = [];


    /**
     * Add a command to be run via brace shell
     *
     * brace <commandName>
     *
     * @param string $commandName
     * @param callable $fn
     * @param string $desc,
     * @param CliBoolArgument[]|CliValueArgument[]
     * @return void
     */
    public function addCommand(string $commandName, callable|array $fn, string $desc = "<no description>", array $arguments = [])
    {
        if (isset ($this->commands[$commandName]))
            throw new \InvalidArgumentException("Command '$commandName' is already defined");
        // Allow _-. in Names
        if ( ! ctype_alnum(str_replace(["_", "-", "."], '', $commandName)))
            throw new \InvalidArgumentException("Invalid Command name '$commandName' must be alphanumeric");
        $this->commands[$commandName] = [
            "desc" => $desc,
            "arguments" => $arguments,
            "fn" => $fn
        ];
    }

    public function addClass (string $classString) {

        $reflection = new \ReflectionClass($classString);
        foreach ($reflection->getMethods() as $method) {
            $mAttr = $method->getAttributes(CliCmd::class);
            if (count($mAttr) === 0)
                continue;
            /* @var $cmd CliCmd */
            $cmd = $mAttr[0]->newInstance();

            $arguments = [];
            foreach ($method->getAttributes() as $attribute) {
                $att = $attribute->newInstance();
                if ( ! $att instanceof CliArgumentInterface)
                    continue;
                $arguments[] = $att;
            }
            $this->addCommand($cmd->name, [$classString, $method->getName()], $cmd->desc, $arguments);
        }


    }

}
