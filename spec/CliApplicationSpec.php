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

        $this->run(['tool', 'project', '--workspace', '/tmp', 'create', '--template-dir=basic'])
            ->shouldReturn("/tmp:basic\n");
        $this->run(['tool', 'project', 'status'])->shouldReturn("ready\n");
    }

    public function it_keeps_registrations_in_separate_instances(): void
    {
        $other = new CliApplication();
        $other->addClass(StatusAction::class);
        $this->addClass(CreateAction::class);

        $this->run(['tool', 'project', 'inspect', '0'])->shouldReturn("0\n");
        $other->run(['tool', 'project', 'status'])->shouldReturn("ready\n");
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
