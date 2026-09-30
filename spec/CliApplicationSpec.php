<?php

namespace spec\Phore\Cli;

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

    public function it_rejects_duplicate_actions_in_one_scope(): void
    {
        $this->addClass(StatusAction::class);
        $this->shouldThrow(CliException::class)
            ->during('addClass', [DuplicateAction::class]);
    }
}
