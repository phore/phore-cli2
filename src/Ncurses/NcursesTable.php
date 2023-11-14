<?php

namespace Phore\Cli\Ncurses;

class NcursesTable
{
    private $data;
    private $columns;
    private $selectedRow = 0;

    public function __construct(array $data, array $columns = []) {
        $this->data = $data;
        $this->columns = $columns;
    }

    private function draw() {
        $y = 1;
        $x = 2;

        // Draw headers
        if (empty($this->columns)) {
            if (!empty($this->data)) {
                $this->columns = array_keys((array)$this->data[0]);
            }
        }
        foreach ($this->columns as $key => $title) {
            echo str_pad(is_int($key) ? $title : $key, 20);
            $x += 22; // Adjust column width here
        }
        echo "\n";

        // Draw rows
        foreach ($this->data as $index => $row) {
            $y++;
            $x = 2;
            foreach ($this->columns as $key => $title) {
                $cellValue = is_int($key) ? $row[$title] : $row[$key];
                if ($index == $this->selectedRow) {
                    echo "\033[7m"; // ANSI escape code for reverse video
                }
                echo str_pad($cellValue, 18);
                if ($index == $this->selectedRow) {
                    echo "\033[0m"; // ANSI escape code to reset formatting
                }
                $x += 22; // Adjust column width here
            }
            echo "\n";
        }
    }

    private function handleInput() {
        $input = fgets(STDIN);

        switch ($input) {
            case 'w': // 'w' for up
                $this->selectedRow = max(0, $this->selectedRow - 1);
                break;
            case 's': // 's' for down
                $this->selectedRow = min(count($this->data) - 1, $this->selectedRow + 1);
                break;
            case "\n":
                return $this->data[$this->selectedRow];
            case 'q': // 'q' for quit
                return null;
        }

        return false;
    }

    public function display() {
        while (true) {
            $this->draw();
            $result = $this->handleInput();
            if ($result !== false) {
                return $result;
            }
        }
    }
}
