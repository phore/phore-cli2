<?php

require __DIR__ . "/../vendor/autoload.php";

use Phore\Cli\Output\Out;

/**
 * Beispiel für die Helper-Klasse Phore\Cli\Output\Out.
 */
Out::TextInfo("**Info:** Ausgabe mit _Markdown_-Formatierung.");
Out::TextSuccess("Der Vorgang wurde erfolgreich abgeschlossen.");
Out::TextWarning("Warnung: Bitte die Konfiguration prüfen.");
Out::TextDanger("Fehler: Die Verbindung konnte nicht hergestellt werden.");

echo PHP_EOL . "Tabelle:" . PHP_EOL;
Out::Table([
    ["id" => 1, "name" => "alpha", "status" => "ready"],
    ["id" => 2, "name" => "beta", "status" => "pending"],
    ["id" => 3, "name" => "gamma", "status" => "failed"],
], false, null, [
    // Renderer erhalten den Zellenwert und die vollständige Originalzeile.
    "status" => static fn(mixed $value, array $row): string => strtoupper((string)$value),
]);

echo PHP_EOL . "Als String zurückgeben:" . PHP_EOL;
$buffer = Out::TextSuccess("**Erfolg** als Rückgabewert", true);
echo $buffer;
