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
    public static function Table(array $data, bool $return = false, array $columns = null) : ?string {
        $of = new CliTableOutputFormat();
        return $of->print_as_table($data, $return, $columns);
    }


    public static function TextDanger(string $text, bool $return = false) :?string {
        $text =  "\033[31m$text\033[0m\n";
        if ($return)
            return $text;
        echo $text;
        return null;
    }
    public static function TextWarning(string $text, bool $return = false) : ?string {
        $text =  "\033[33m$text\033[0m\n";
        if ($return)
            return $text;
        echo $text;
        return null;
    }

    public static function TextSuccess(string $text, bool $return = false) : ?string {
        $text =  "\033[32m$text\033[0m\n";
        if ($return)
            return $text;
        echo $text;
        return null;
    }

}
