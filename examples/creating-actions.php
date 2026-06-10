<?php

require __DIR__ . "/../vendor/autoload.php";

use Phore\Cli\Annotation\CliParameter;
use Phore\Cli\CliDispatcher;
use Phore\Cli\Output\Out;

/**
 * Beispiel zum Erstellen von Actions.
 *
 * Aufrufe:
 *   php examples/creating-actions.php app create --name demo
 *   php examples/creating-actions.php app --workspace /tmp/projects create --name demo --template api
 *   php examples/creating-actions.php app greet --name Matthias
 *   php examples/creating-actions.php app inspect foo bar baz
 */
class App
{
    public function __construct(
        #[CliParameter("workspace", "Basisverzeichnis für neue Projekte")]
        private string $workspace = "./build"
    ) {
    }

    public function create(
        #[CliParameter("name", "Name des Projekts")]
        string $name,
        #[CliParameter("template", "Vorlage, z. B. basic oder api")]
        string $template = "basic"
    ): void {
        Out::TextSuccess("Projekt **{$name}** wird in _{$this->workspace}_ angelegt.");
        Out::Table([
            ["key" => "workspace", "value" => $this->workspace],
            ["key" => "name", "value" => $name],
            ["key" => "template", "value" => $template],
        ]);
    }

    public function greet(
        #[CliParameter("name", "Name der Person")]
        string $name
    ): void {
        Out::TextInfo("Hallo **{$name}** aus _{$this->workspace}_.");
    }

    public function inspect(array $argv): void
    {
        Out::TextWarning("Unverarbeitete Positionsparameter:");
        Out::Table(array_map(fn(string $value) => ["argument" => $value], $argv));
    }
}

CliDispatcher::addClass(App::class);
CliDispatcher::run($argv);
