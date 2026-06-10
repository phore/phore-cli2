<?php

require __DIR__ . "/../vendor/autoload.php";

use Phore\Cli\CliPreset;

/**
 * Beispiel für die Nutzung von CliPreset.
 */
$tempFile = sys_get_temp_dir() . "/phore-cli2-example-presets.ini";
file_put_contents($tempFile, <<<INI
[:deploy-production]
CMD = app deploy
--env = production
--region = eu-central-1
INI);

$preset = new CliPreset($tempFile);
$arguments = [];
$command = $preset->getPreset(':deploy-production', $arguments);

echo "Command: " . implode(" ", $command) . PHP_EOL;
echo "Argumente:" . PHP_EOL;
echo json_encode($arguments, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;

@unlink($tempFile);
