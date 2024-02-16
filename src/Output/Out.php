<?php

namespace Phore\Cli\Output;

class Out
{

    /**
     * Return a table representation of the given data
     * 
     * @param array $data
     * @param bool $return
     * @return string|null
     */
    public static function Table(array $data, bool $return = false) {
        $of = new CliTableOutputFormat();
        return $of->print_as_table($data, $return);
    }
    
    
    public static function TextDanger(string $text) {
        return "\033[31m$text\033[0m";
    }
    public static function TestWarning(string $text) {
        return "\033[33m$text\033[0m";
    }
    
}