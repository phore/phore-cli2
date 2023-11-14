<?php

namespace Phore\Cli\Ncurses;

class NcursesForm
{
    private $fields = [];
    private $buttons = [];
    private $currentField = 0;
    private $nextY = 0;

    public function __construct() {
        $this->addButton(0, 0, 'OK');
        $this->addButton(1, 0, 'Cancel');
    }

    public function addField($label, $type = 'text', $y = null, $x = null) {
        if ($y === null) {
            $y = $this->nextY++;
        }
        if ($x === null) {
            $x = 0;
        }
        $this->fields[] = [
            'y' => $y, 'x' => $x, 'label' => $label,
            'value' => '', 'type' => $type
        ];
    }

    public function addButton($y, $x, $label) {
        $this->buttons[] = ['y' => $y, 'x' => $x, 'label' => $label];
    }

    private function draw() {
        foreach ($this->fields as $index => $field) {
            echo $field['label'] . ": ";
            $displayValue = $field['type'] === 'password' ? str_repeat('*', strlen($field['value'])) : $field['value'];
            echo str_pad($displayValue, 20);
            echo "\n";
        }

        foreach ($this->buttons as $index => $button) {
            echo $button['label'];
            echo "\n";
        }
    }

    private function handleInput() {
        $input = fgets(STDIN, 1);
        $currentElement = &$this->fields[$this->currentField];

        echo "R" . $input;
        switch ($input) {
            case 'up':
                $this->currentField = ($this->currentField - 1 + count($this->fields)) % count($this->fields);
                break;
            case 'down':
                $this->currentField = ($this->currentField + 1) % count($this->fields);
                break;
            case 'backspace':
                $currentElement['value'] = substr($currentElement['value'], 0, -1);
                break;
            default:
                if (ctype_print($input)) {
                    $currentElement['value'] .= $input;
                }
                break;
        }
    }

    public function display() {
        while (true) {
            $this->draw();
            $this->handleInput();
            if ($this->buttons[0]['label'] === 'OK' && $this->currentField === 0) {
                return array_map(function($field) {
                    return $field['value'];
                }, $this->fields);
            }
            if ($this->buttons[1]['label'] === 'Cancel' && $this->currentField === 1) {
                return null;
            }
        }
    }
}
