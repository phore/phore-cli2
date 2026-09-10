<?php

declare(strict_types=1);

require_once dirname(__DIR__, 4) . '/autoload.php';

use Phore\Cli\Input\TerminalInputReader;

final class TestableTerminalInputReader extends TerminalInputReader
{
    public function submits(string $sequence): bool
    {
        return $this->isSubmitSequence($sequence);
    }
}

$reader = new TestableTerminalInputReader();

$expectations = [
    "\x1b[13u" => true,
    "\x1b[13;1u" => true,
    "\x1b[13;1:1u" => true,
    "\x1b[13;2u" => false,
    "\x1b[13;5u" => false,
];

foreach ($expectations as $sequence => $expected) {
    if ($reader->submits($sequence) !== $expected) {
        fwrite(STDERR, 'Unexpected result for ' . bin2hex($sequence) . PHP_EOL);
        exit(1);
    }
}

echo "TerminalInputReader tests passed\n";
