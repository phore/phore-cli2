# Phore Cli2

Kleine CLI-Dispatcher-Library für PHP.

## Quickstart

```php
<?php

require __DIR__ . "/vendor/autoload.php";

use Phore\Cli\CliDispatcher;
use Phore\Cli\Annotation\CliParameter;

class App
{
    public function greet(
        #[CliParameter("name", "Name der Person")]
        string $name
    ): void {
        echo "Hallo {$name}!\n";
    }
}

CliDispatcher::addClass(App::class);
CliDispatcher::run($argv);
```

Aufruf:

```bash
php app.php app greet --name Matthias
```

## Examples

- `examples/creating-actions.php` – Actions mit der statischen `CliDispatcher`-API
- `examples/shared-scopes.php` – gemeinsamer Scope, CLI-Optionsname und eigene `CliApplication`
- `examples/helper-in.php` – Eingaben mit `Phore\Cli\Input\In`
- `examples/helper-out.php` – formatierte Ausgabe mit `Phore\Cli\Output\Out`
- `examples/helper-nc.php` – einfache TUI-Helfer mit `Phore\Cli\Ncurses\Nc`
- `examples/helper-clipreset.php` – Presets mit `Phore\Cli\CliPreset`

## Zentrale Bausteine

- `Phore\Cli\CliDispatcher` – registriert Command-Gruppen und startet das Dispatching
- `Phore\Cli\Annotation\CliParameter` – beschreibt CLI-Parameter
- `Phore\Cli\Input\In` – interaktive Eingaben
- `Phore\Cli\Output\Out` – Text- und Tabellen-Ausgabe
- `Phore\Cli\CliPreset` – lädt Presets aus `cli_presets.ini`
- `Phore\Cli\Ncurses\Nc` – einfache terminalbasierte Form-/Tabellen-Helfer

Weitere Übersicht: `.ai-usage-info.md`


## Eigene Instanz und gemeinsam genutzte Scopes

`CliApplication` verwaltet eine eigene Command-Registry. `run()` nimmt wie
`CliDispatcher::run()` ein `argv` einschließlich Programmname entgegen und
schreibt die Ausgabe direkt auf die Konsole. Dabei wird kein PHP-Output-Buffer
aktiviert oder verändert. Fehler werden als Exceptions an den Aufrufer
weitergegeben. Die bisherigen statischen `CliDispatcher`-Aufrufe funktionieren
weiterhin ebenfalls mit direkter Konsolenausgabe.

```php
use Phore\Cli\Annotation\CliParameter;
use Phore\Cli\Annotation\CliScope;
use Phore\Cli\CliApplication;

#[CliScope('project')]
class ProjectCreate
{
    public function create(
        #[CliParameter('template-dir')] string $templateDir
    ): void {
        echo "Template: {$templateDir}";
    }
}

#[CliScope('project')]
class ProjectStatus
{
    public function status(): void
    {
        echo 'Ready';
    }
}

$app = new CliApplication();
$app->addClass(ProjectCreate::class);
$app->addClass(ProjectStatus::class);
$app->run(['tool', 'project', 'create', '--template-dir=/tmp/basic']);
```

Ergebnis: `Template: /tmp/basic`. Ohne `CliScope` bleibt der kleingeschriebene
Klassenname der Gruppenname. Jede Action verwendet den Konstruktor ihrer
eigenen Klasse. Doppelte Action-Namen im selben Scope und widersprüchliche
Typen gleichnamiger Scope-Optionen führen bei der Registrierung zu einem
Fehler. `CliParameter` benennt nur die CLI-Option um; die Bindung an den
PHP-Parameter erfolgt weiterhin über dessen PHP-Namen.

## Tests

Nach `composer install` führt `composer test` die PHPSpec-Specs aus
(`vendor/bin/phpspec run`).
