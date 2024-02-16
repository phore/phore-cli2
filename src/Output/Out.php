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
    
    
    public static function TextDanger(string $text, bool $return = false) :?string {
        $text =  "\033[31m$text\033[0m";
        if ($return)
            return $text;
        echo $text;
    }
    public static function TextWarning(string $text, bool $return = false) : ?string {
        $text =  "\033[33m$text\033[0m";
        if ($return)
            return $text;
        echo $text;
    }
    
}