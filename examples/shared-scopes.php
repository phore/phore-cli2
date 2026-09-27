<?php

require __DIR__ . '/../vendor/autoload.php';

use Phore\Cli\Annotation\CliParameter;
use Phore\Cli\Annotation\CliScope;
use Phore\Cli\CliApplication;

/**
 * Aufrufe:
 *   php examples/shared-scopes.php project create --template-dir=/tmp/basic
 *   php examples/shared-scopes.php project status
 */
#[CliScope('project')]
class ProjectCreateActions
{
    public function create(
        #[CliParameter('template-dir', 'Pfad zur Projektvorlage')]
        string $templateDir
    ): void {
        echo "Template: {$templateDir}";
    }
}

#[CliScope('project')]
class ProjectStatusActions
{
    public function status(): void
    {
        echo 'Ready';
    }
}

$app = new CliApplication();
$app->addClass(ProjectCreateActions::class);
$app->addClass(ProjectStatusActions::class);

// run() liefert die Ausgabe zurück; Exceptions gehen an den Aufrufer.
echo $app->run($argv);
