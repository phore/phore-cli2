<?php

namespace spec\Phore\Cli\Input;

use Phore\Cli\Input\TerminalInputReader;
use PhpSpec\ObjectBehavior;

class InspectableTerminalInputReader extends TerminalInputReader
{
    public function submits(string $sequence): bool
    {
        return $this->isSubmitSequence($sequence);
    }
}

class TerminalInputReaderSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beAnInstanceOf(InspectableTerminalInputReader::class);
    }

    public function it_recognizes_plain_enter_sequences(): void
    {
        $this->submits("\x1b[13u")->shouldReturn(true);
        $this->submits("\x1b[13;1u")->shouldReturn(true);
        $this->submits("\x1b[13;1:1u")->shouldReturn(true);
    }

    public function it_leaves_modified_enter_as_a_line_break(): void
    {
        $this->submits("\x1b[13;2u")->shouldReturn(false);
        $this->submits("\x1b[13;5u")->shouldReturn(false);
    }
}
