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
    
}