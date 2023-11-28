<?php

namespace Phore\Cli\Output;

class CliTableOutputFormat {
    private array $config;

    public function __construct(array $config = [])
    {
        // Default configuration uses spaces as separators.
        // Add a new configuration option for row numbers.
        $this->config = array_merge(['separator' => ' ', 'rowNumbers' => true], $config);
    }

    public function print_as_table(array $data, bool $return = false): ?string
    {
        // Get the maximum width of the command line.
        $terminalWidth = exec('tput cols') ?: 80;
        // Prepare the output variable.
        $output = '';
        $separator = $this->config['separator'] === ':' ? ':' : ' ';

        // Find the longest key to set the column width.
        $columnWidths = [];
        foreach ($data as $row) {
            foreach ($row as $key => $value) {
                $value = $this->formatValue($value);
                $columnWidths[$key] = max($columnWidths[$key] ?? 0, $this->strWidth($key), $this->strWidth($value));
            }
        }

        // Adjust column widths based on the terminal width.
        $totalWidth = array_sum($columnWidths) + (count($columnWidths) - 1) * $this->strWidth($separator);
        if ($totalWidth > $terminalWidth) {
            $columnWidths = $this->adjustColumnWidths($columnWidths, $terminalWidth, $this->strWidth($separator));
        }

        // Create the header row.
        if ($this->config['rowNumbers']) {
            $output .= str_pad('#', 3, ' ', STR_PAD_LEFT) . $separator;
        }
        foreach ($columnWidths as $key => $width) {
            $output .= $this->padString($key, $width) . $separator;
        }
        $output = rtrim($output, $separator) . PHP_EOL;
        $output .= str_repeat('-', $totalWidth) . PHP_EOL;

        // Print the data rows.
        $rowNumber = 1;
        foreach ($data as $row) {
            $row = (array)$row;
            if ($this->config['rowNumbers']) {
                $output .= str_pad((string)$rowNumber, 3, ' ', STR_PAD_LEFT) . $separator;
                $rowNumber++;
            }
            foreach ($columnWidths as $key => $width) {
                $value = $this->formatValue($row[$key] ?? '');
                $output .= $this->padString($value, $width) . $separator;
            }
            $output = rtrim($output, $separator) . PHP_EOL;
        }

        // Output or return the result.
        if ($return) {
            return $output;
        } else {
            echo $output;
            return null;
        }
    }

    private function formatValue($value): string
    {
        // Convert array of objects to array of arrays.
        if (is_array($value) && count($value) > 0 && is_object($value[0])) {
            $value = array_map(function ($item) {
                return (array)$item;
            }, $value);
        }
        if (is_array($value)) {
            $value = implode(',', $value);
        } elseif (is_object($value) || is_array($value)) {
            $value = json_encode($value);
        } else {
            $value = (string) $value;
        }
        // Remove newlines, tabs, and extra spaces to prevent breaking the table layout.
        return preg_replace('/[\r\n\t]+/', ' ', $value);
    }

    private function adjustColumnWidths(array $columnWidths, int $terminalWidth, int $separatorWidth): array
    {
        // Calculate the total width taken by separators.
        $separatorTotalWidth = ($separatorWidth * (count($columnWidths) - 1));
        // Calculate available width for columns.
        $availableWidth = $terminalWidth - $separatorTotalWidth;

        // Distribute available width to columns proportionally.
        $totalColumnWidths = array_sum($columnWidths);
        foreach ($columnWidths as $key => $width) {
            $columnWidths[$key] = floor($width / $totalColumnWidths * $availableWidth);
        }

        return $columnWidths;
    }

    private function strWidth(string $str): int
    {
        return mb_strwidth($str, 'UTF-8');
    }

    private function padString(string $str, int $width): string
    {
        $padding = $width - $this->strWidth($str);
        return $str . str_repeat(' ', $padding);
    }
}

