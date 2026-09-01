<?php

namespace Phore\Cli\Input;

class TerminalInputReader
{

    public function read(string $question): string
    {
        if (function_exists('stream_isatty') && ! stream_isatty(STDIN)) {
            fwrite(STDERR, $question . ': ');
            return rtrim((string) fgets(STDIN), "\r\n");
        }

        $oldStty = rtrim((string) shell_exec('stty -g 2>/dev/null'));

        $this->setStty('-icanon -echo -icrnl min 1 time 0');
        $this->enableBracketedPaste();

        $input = '';

        try {
            fwrite(STDERR, $question . ': (Enter = OK, Shift+Enter/Ctrl+J = neue Zeile, Paste erlaubt)' . PHP_EOL . '> ');

            while (true) {
                $char = $this->readByte();
                if ($char === '') {
                    continue;
                }

                if ($char === "\x03") { // Ctrl+C
                    fwrite(STDERR, PHP_EOL);
                    throw new \RuntimeException('Input aborted by Ctrl+C');
                }

                if ($char === "\x04") { // Ctrl+D
                    fwrite(STDERR, PHP_EOL);
                    return $input;
                }

                if ($char === "\x1b") { // ESC sequence
                    $sequence = $this->readEscapeSequence($char);

                    if ($sequence === "\x1b[200~") {
                        $paste = $this->readBracketedPaste();
                        $input .= $paste;
                        $this->echoInput($paste);
                        continue;
                    }

                    if ($sequence === "\x1b[13;2u" || $sequence === "\x1b[13;5u") { // Shift+Enter / Ctrl+Enter
                        $input .= "\n";
                        fwrite(STDERR, PHP_EOL . '> ');
                        continue;
                    }

                    continue;
                }

                if ($char === "\x0a") { // Ctrl+J
                    $input .= "\n";
                    fwrite(STDERR, PHP_EOL . '> ');
                    continue;
                }

                if ($char === "\x0d") { // Enter
                    fwrite(STDERR, PHP_EOL);
                    return $input;
                }

                if ($char === "\x7f" || $char === "\x08") { // Backspace
                    if ($input !== '') {
                        $input = substr($input, 0, -1);
                        fwrite(STDERR, "\x08 \x08");
                    }
                    continue;
                }

                $input .= $char;
                fwrite(STDERR, $char);
            }
        } finally {
            $this->disableBracketedPaste();
            if ($oldStty !== '') {
                $this->restoreStty($oldStty);
            }
        }
    }


    private function readEscapeSequence(string $firstChar): string
    {
        $sequence = $firstChar;

        while (strlen($sequence) < 64) {
            $char = $this->readByte(50000);
            if ($char === '') {
                break;
            }

            $sequence .= $char;

            if ($char === '~' || $char === 'u' || (strlen($sequence) > 2 && ctype_alpha($char))) {
                break;
            }
        }

        return $sequence;
    }


    private function readBracketedPaste(): string
    {
        $endSequence = "\x1b[201~";
        $buffer = '';

        while (true) {
            $char = $this->readByte();
            if ($char === '') {
                continue;
            }

            $buffer .= $char;

            if (str_ends_with($buffer, $endSequence)) {
                return substr($buffer, 0, -strlen($endSequence));
            }
        }
    }


    private function readByte(?int $timeoutMicroseconds = null): string
    {
        if ($timeoutMicroseconds === null) {
            $char = fread(STDIN, 1);
            return $char === false ? '' : $char;
        }

        $read = [STDIN];
        $write = null;
        $except = null;
        $seconds = intdiv($timeoutMicroseconds, 1000000);
        $microseconds = $timeoutMicroseconds % 1000000;

        $ready = stream_select($read, $write, $except, $seconds, $microseconds);
        if ($ready === false || $ready === 0) {
            return '';
        }

        $char = fread(STDIN, 1);
        return $char === false ? '' : $char;
    }


    private function echoInput(string $text): void
    {
        fwrite(STDERR, str_replace(["\r\n", "\r", "\n"], [PHP_EOL . '> ', PHP_EOL . '> ', PHP_EOL . '> '], $text));
    }


    private function enableBracketedPaste(): void
    {
        fwrite(STDERR, "\033[?2004h");
    }


    private function disableBracketedPaste(): void
    {
        fwrite(STDERR, "\033[?2004l");
    }


    private function setStty(string $arguments): void
    {
        system('stty ' . $arguments . ' 2>/dev/null');
    }


    private function restoreStty(string $state): void
    {
        system('stty ' . escapeshellarg($state) . ' 2>/dev/null');
    }
}
