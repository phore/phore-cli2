<?php

require __DIR__ . "/../vendor/autoload.php";

use Phore\Cli\Ncurses\Nc;

/**
 * Beispiel für die Helper-Klasse Phore\Cli\Ncurses\Nc.
 *
 * Aufrufe:
 *   php examples/helper-nc.php table
 *   php examples/helper-nc.php form
 *
 * Table-Steuerung:
 *   w = hoch, s = runter, Enter = auswählen, q = abbrechen
 */
$mode = $argv[1] ?? "table";

if ($mode === "form") {
    $result = Nc::Form([
        "Name",
        "E-Mail",
        "Passwort",
    ]);

    var_dump($result);
    exit(0);
}

$result = Nc::Table([
    ["id" => 1, "name" => "alpha", "status" => "ready"],
    ["id" => 2, "name" => "beta", "status" => "pending"],
    ["id" => 3, "name" => "gamma", "status" => "failed"],
], [
    "id" => "ID",
    "name" => "Name",
    "status" => "Status",
]);

var_dump($result);
