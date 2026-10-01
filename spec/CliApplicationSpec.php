<?php

namespace spec\Phore\Cli;

use Phore\Cli\Annotation\CliCommand;
use Phore\Cli\Annotation\CliParameter;
use Phore\Cli\Annotation\CliScope;
use Phore\Cli\CliApplication;
use Phore\Cli\Exception\CliException;
use PhpSpec\ObjectBehavior;

#[CliScope('project')]
class CreateAction
{
    public static ?int $bufferLevel = null;

    public function __construct(#[CliParameter] private string $workspace = './build')
    {
    }

    #[CliCommand('create', 'Creates a project', 'Creates a new project from the selected template.')]
    public function create(#[CliParameter('template-dir')] string $templateDir): void
    {
        echo $this->workspace . ':' . $templateDir;
    }

    public function inspect(array $argv): void
    {
        echo implode(',', $argv);
    }

    public function buffer(): void
    {
        self::$bufferLevel = ob_get_level();
    }

    #[CliCommand('plain')]
    public function plain(): void
    {
        echo 'plain';
    }
}

#[CliScope('project')]
class StatusAction
{
    public function status(): void
    {
        echo 'ready';
    }
}

#[CliScope('project')]
class DuplicateAction
{
    public function status(): void
    {
    }
}

#[CliScope('ai')]
class AiAction
{
    public function __construct(
        #[CliParameter('config-file', 'Configuration file')]
        private string $configFile = './config.yml'
    ) {
    }

    public function status(): void
    {
        echo $this->configFile;
    }
}

#[CliScope(['ai', 'edit'])]
class AiEditAction
{
    public function __construct(
        #[CliParameter('model', 'Model name')]
        private string $model = 'default'
    ) {
    }

    public function run(): void
    {
        echo $this->model;
    }
}

class CliApplicationSpec extends ObjectBehavior
{
    public function it_combines_actions_from_classes_in_the_same_scope(): void
    {
        $this->addClass(CreateAction::class);
        $this->addClass(StatusAction::class);

        $bufferLevel = ob_get_level();
        CreateAction::$bufferLevel = null;

        $this->run(['tool', 'project', '--workspace', '/tmp', 'create', '--template-dir=basic']);
        $this->run(['tool', 'project', 'buffer']);
        $this->run(['tool', 'project', 'status']);

        if (CreateAction::$bufferLevel !== $bufferLevel || ob_get_level() !== $bufferLevel) {
            throw new \RuntimeException('CliApplication::run() must not change PHP output buffering.');
        }
    }

    public function it_keeps_registrations_in_separate_instances(): void
    {
        $other = new CliApplication();
        $other->addClass(StatusAction::class);
        $this->addClass(CreateAction::class);

        $this->run(['tool', 'project', 'inspect', '0']);
        $other->run(['tool', 'project', 'status']);
        $this->shouldThrow(CliException::class)
            ->during('run', [['tool', 'project', 'status']]);
    }

    public function it_shows_only_required_parameters_in_compact_help(): void
    {
        $this->addClass(CreateAction::class);

        ob_start();
        $this->run(['tool', 'project']);
        $help = ob_get_clean();

        if (! str_contains($help, '--template-dir <value>')) {
            throw new \RuntimeException('Compact help must show required parameters.');
        }
        if (str_contains($help, '--workspace')) {
            throw new \RuntimeException('Compact help must hide optional parameters.');
        }
    }

    public function it_shows_command_description_and_options_hint_in_compact_help(): void
    {
        $this->addClass(CreateAction::class);

        ob_start();
        $this->run(['tool', 'project']);
        $help = ob_get_clean();

        if (! str_contains($help, 'create') || ! str_contains($help, 'Creates a project')) {
            throw new \RuntimeException('Compact help must show CliCommand descriptions.');
        }
        if (! str_contains($help, '[OPTIONS]')) {
            throw new \RuntimeException('Compact help must hint at optional parameters.');
        }
    }

    public function it_shows_aligned_compact_command_descriptions(): void
    {
        $this->addClass(CreateAction::class);

        ob_start();
        $this->run(['tool', 'project']);
        $help = ob_get_clean();

        if (! preg_match('/^  create\\s{2,}Creates a project$/m', $help)) {
            throw new \RuntimeException('Compact help must align command descriptions.');
        }
    }

    public function it_shows_all_parameters_with_help_flag(): void
    {
        $this->addClass(CreateAction::class);

        ob_start();
        $this->run(['tool', 'project', 'create', '--help']);
        $help = ob_get_clean();

        if (! str_contains($help, '--template-dir <value>')) {
            throw new \RuntimeException('Detailed help must show required parameters.');
        }
        if (! str_contains($help, 'Creates a new project from the selected template.')) {
            throw new \RuntimeException('Detailed help must show the long command description.');
        }
        if (! str_contains($help, '[--workspace <value>]')) {
            throw new \RuntimeException('Detailed help must mark optional parameters.');
        }
    }

    public function it_supports_nested_subcommands_and_scope_options(): void
    {
        $this->addClass(AiAction::class);
        $this->addClass(AiEditAction::class);

        ob_start();
        $this->run(['tool', 'ai', '--config-file', '/tmp/ai.yml', 'edit', '--model', 'gpt', 'run']);
        $output = ob_get_clean();

        if (trim($output) !== 'gpt') {
            throw new \RuntimeException('Nested sub-command must execute with its own scope options.');
        }
    }

    public function it_shows_nested_subcommands_and_parent_options_in_help(): void
    {
        $this->addClass(AiAction::class);
        $this->addClass(AiEditAction::class);

        ob_start();
        $this->run(['tool', 'ai', '-h']);
        $help = ob_get_clean();

        if (! str_contains($help, 'edit [COMMAND]')) {
            throw new \RuntimeException('Parent help must show nested sub-commands.');
        }
        if (! str_contains($help, '[--config-file <value>]')) {
            throw new \RuntimeException('Parent help must show parent scope options.');
        }
        if (! str_ends_with(trim($help), 'Use -h to show help and additional options.')) {
            throw new \RuntimeException('Help must end with the -h hint.');
        }
    }

    public function it_keeps_command_descriptions_optional(): void
    {
        $command = new CliCommand('plain');

        if ($command->desc !== '' || $command->longDesc !== '') {
            throw new \RuntimeException('Command descriptions must be optional and empty by default.');
        }

        $this->addClass(CreateAction::class);

        ob_start();
        $this->run(['tool', 'project', 'plain', '-h']);
        $help = ob_get_clean();

        if (str_contains($help, '<no description>')) {
            throw new \RuntimeException('Help must not render a placeholder for omitted descriptions.');
        }
    }

    public function it_keeps_command_group_help_signature_compatible(): void
    {
        $parent = new \ReflectionMethod(\Phore\Cli\Types\T_Command::class, 'getHelp');
        $group = new \ReflectionMethod(\Phore\Cli\Types\T_CommandGroup::class, 'getHelp');
        $set = new \ReflectionMethod(\Phore\Cli\Types\T_CommandSet::class, 'getHelp');

        if ($group->getNumberOfParameters() < $parent->getNumberOfParameters()) {
            throw new \RuntimeException('T_CommandGroup::getHelp() must remain compatible with T_Command::getHelp().');
        }
        if ($set->getNumberOfParameters() < $group->getNumberOfParameters()) {
            throw new \RuntimeException('T_CommandSet::getHelp() must remain compatible with T_CommandGroup::getHelp().');
        }
    }

    public function it_rejects_duplicate_actions_in_one_scope(): void
    {
        $this->addClass(StatusAction::class);
        $this->shouldThrow(CliException::class)
            ->during('addClass', [DuplicateAction::class]);
    }
}
