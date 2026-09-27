<?php

namespace Phore\Cli\Annotation;

/**
 * Names the CLI scope shared by one or more action classes.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class CliScope
{
    /**
     * Sets a shared CLI group name for the annotated class.
     *
     * Example: #[CliScope('project')] on two action classes.
     *
     * @param string $name Nonempty CLI group name.
     * @throws \\InvalidArgumentException For an empty name.
     * @see \\Phore\\Cli\\Types\\T_CommandGroup::CreateFromClassName()
     */
    public function __construct(public string $name)
    {
        if ($name === '') {
            throw new \InvalidArgumentException('CLI scope name must not be empty.');
        }
    }
}
