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

- `examples/creating-actions.php` – Actions mit `CliDispatcher` und `CliParameter`
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

