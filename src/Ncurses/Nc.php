<?php

namespace Phore\Cli\Ncurses;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Question\Question;
use wapmorgan\NcursesObjects\Ncurses;
use wapmorgan\NcursesObjects\Window;

class Nc
{

    public static function Table (array $data, array $columns = []) : null|array|object {
        $table = new NcursesTable($data, $columns);
        return $table->display();
    }

    public static function Form(array $fields, array $values = []) : null|array|object {
        $form = new NcursesForm($fields, $values);
        foreach ($fields as $field) {
            $form->addField($field);
        }
        return $form->display();
    }

}
