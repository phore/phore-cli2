<?php

require __DIR__ . "/../vendor/autoload.php";

use Phore\Cli\Input\In;

/**
 * Beispiel für die Helper-Klasse Phore\Cli\Input\In.
 *
 * Hinweis:
 * - AskMultiLine beendet die Eingabe mit CTRL-X.
 */
$name = In::AskLine("Wie heißt du");
$deploy = In::AskBool("Deployment starten", true);
$notes = In::AskMultiLine("Notizen eingeben");

echo json_encode([
    "name" => $name,
    "deploy" => $deploy,
    "notes" => $notes,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
