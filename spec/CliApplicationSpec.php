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

        if ( ! str_contains($help, '--template-dir <value>')) {
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

        if ( ! str_contains($help, 'create') || ! str_contains($help, 'Creates a project')) {
            throw new \RuntimeException('Compact help must show CliCommand descriptions.');
        }
        if ( ! str_contains($help, '[OPTIONS]')) {
            throw new \RuntimeException('Compact help must hint at optional parameters.');
        }
    }

    public function it_shows_aligned_compact_command_descriptions(): void
    {
        $this->addClass(CreateAction::class);

        ob_start();
        $this->run(['tool', 'project']);
        $help = ob_get_clean();

        if ( ! preg_match('/^  create\\s{2,}Creates a project$/m', $help)) {
            throw new \\RuntimeException('Compact help must align command descriptions.');
        }
    }

    public function it_shows_all_parameters_with_help_flag(): void
    {
        $this->addClass(CreateAction::class);

        ob_start();
        $this->run(['tool', 'project', 'create', '--help']);
        $help = ob_get_clean();

        if ( ! str_contains($help, '--template-dir <value>')) {
            throw new \RuntimeException('Detailed help must show required parameters.');
        }
        if ( ! str_contains($help, 'Creates a new project from the selected template.')) {
            throw new \\RuntimeException('Detailed help must show the long command description.');
        }
        if ( ! str_contains($help, '[--workspace <value>]')) {
            throw new \RuntimeException('Detailed help must mark optional parameters.');
        }
    }

    public function it_rejects_duplicate_actions_in_one_scope(): void
    {
        $this->addClass(StatusAction::class);
        $this->shouldThrow(CliException::class)
            ->during('addClass', [DuplicateAction::class]);
    }
}
