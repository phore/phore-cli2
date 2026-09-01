<?php

namespace Phore\Cli\Output;

class Out
{

    /**
     * Return a table representation of the given data.
     *
     * A column renderer receives the cell value and the complete original row.
     * Its return value is rendered in the table cell; returning a preformatted string is recommended.
     *
     * @param array $data
     * @param bool $return
     * @param array|null $columns
     * @param array<string|int, callable(mixed, array): mixed> $columnRenderers Example:
     *        ["quantity" => static fn(mixed $value, array $row): string => (string) round((float)$value)]
     * @return string|null
     */
    public static function Table(array $data, bool $return = false, array $columns = null, array $columnRenderers = []) : ?string {
        $of = new CliTableOutputFormat(["columnRenderers" => $columnRenderers]);
        return $of->print_as_table($data, $return, $columns);
    }

    private static $markdown = true;
    
    public static function ParseMarkdown($value = true) {
        self::$markdown = $value;
    }
    

    /**
     * Trys to find Markdown *italiq* **bold**  text and translate it to terminal bold text
     *
     * @param string $text
     * @param bool $return
     * @return string|null
     */
    private static function translateMarkdown(string $text) : ?string {
        if ( ! self::$markdown)
            return $text;
        $text =  preg_replace("/(^|\s)\*\*(.*?)\*\*(\s|$)/", "\033[1m$2\033[0m", $text); // Bold
        // Italiq
        $text = preg_replace("/(^|\s)\*(.*?)\*(\s|$)/", "\033[3m$2\033[0m", $text);
        // Both
        $text = preg_replace("/(^|\s)\*\*\*(.*?)\*\*\*(\s|$)/", "\033[1m\033[3m$2\033[0m", $text);
        
        // Underline biy _text_
        $text = preg_replace("/(^|\s)\_(.*?)\_(\s|$)/i", "\033[4m$2\033[0m", $text);
        return $text;
    }

    public static function TextDanger(string $text, bool $return = false) :?string {
        $text = self::translateMarkdown($text);
        $text =  "\033[31m$text\033[0m\n";
        if ($return)
            return $text;
        echo $text;
        return null;
    }
    public static function TextWarning(string $text, bool $return = false) : ?string {
        $text = self::translateMarkdown($text);

        $text =  "\033[33m$text\033[0m\n";
        if ($return)
            return $text;
        echo $text;
        return null;
    }

    public static function TextSuccess(string $text, bool $return = false) : ?string {
        $text = self::translateMarkdown($text);
        $text =  "\033[32m$text\033[0m\n";
        if ($return)
            return $text;
        echo $text;
        return null;
    }

    /**
     * Dark gray
     *
     * Support Markdown Bild
     *
     * @param string $text
     * @param bool $return
     * @return string|null
     */
    public static function TextInfo (string $text, bool $return = false) : ?string {
        $text = self::translateMarkdown($text);

        $text =  "\033[90m$text\033[0m\n";
        if ($return)
            return $text;
        echo $text;
        return null;
    }

}
