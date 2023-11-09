<?php

namespace Phore\Cli\Output;

class CliTableOutputFormat {
    private array $config;

    public function __construct(array $config = [])
    {
        // Default configuration uses spaces as separators.
        $this->config = array_merge(['separator' => ' '], $config);
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
                $columnWidths[$key] = max($columnWidths[$key] ?? 0, strlen($key), strlen($value));
            }
        }

        // Adjust column widths based on the terminal width.
        $totalWidth = array_sum($columnWidths) + (count($columnWidths) - 1) * strlen($separator);
        if ($totalWidth > $terminalWidth) {
            $columnWidths = $this->adjustColumnWidths($columnWidths, $terminalWidth, strlen($separator));
        }

        // Create the header row.
        foreach ($columnWidths as $key => $width) {
            $output .= str_pad($key, $width) . $separator;
        }
        $output = rtrim($output, $separator) . PHP_EOL;
        $output .= str_repeat('-', $totalWidth) . PHP_EOL;

        // Print the data rows.
        foreach ($data as $row) {
            $row = (array)$row;
            foreach ($columnWidths as $key => $width) {
                $value = $this->formatValue($row[$key] ?? '');
                $output .= str_pad(substr($value, 0, $width), $width) . $separator;
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
}
