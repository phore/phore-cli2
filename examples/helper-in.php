<?php

require __DIR__ . "/../vendor/autoload.php";

use Phore\Cli\Input\In;

/**
 * Beispiel für die Helper-Klasse Phore\Cli\Input\In.
 *
 * Hinweis:
 * - AskMultiLine nutzt Enter zum Absenden.
 * - Shift+Enter bzw. Ctrl+J fügt eine neue Zeile ein.
 * - Eingefügte mehrzeilige Clipboard-Blöcke werden per Bracketed Paste erkannt.
 */
$name = In::AskLine("Wie heißt du");
$deploy = In::AskBool("Deployment starten", true);
$notes = In::AskMultiLine("Notizen eingeben");

echo json_encode([
    "name" => $name,
    "deploy" => $deploy,
    "notes" => $notes,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
