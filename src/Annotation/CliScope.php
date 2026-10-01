<?php

namespace Phore\Cli\Annotation;

/**
 * Names the CLI scope shared by one or more action classes.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class CliScope
{
    /**
     * Sets a CLI scope or nested scope path for the annotated class.
     *
     * Example: #[CliScope('ai')] creates the "ai" scope.
     * Example: #[CliScope(['ai', 'edit'])] creates the nested "ai edit" scope.
     *
     * @param string|array<int, string> $name Nonempty CLI scope or scope path.
     * @throws \InvalidArgumentException For an empty or invalid scope path.
     * @see \Phore\Cli\Types\T_CommandGroup::CreateFromClassName()
     */
    public function __construct(public string|array $name)
    {
        $path = $this->getPath();
        if ($path === []) {
            throw new \InvalidArgumentException('CLI scope path must not be empty.');
        }

        foreach ($path as $segment) {
            if ($segment === '') {
                throw new \InvalidArgumentException('CLI scope names must not be empty.');
            }
        }
    }

    /**
     * Returns the normalized scope path.
     *
     * Example: (new CliScope(['ai', 'edit']))->getPath() returns ['ai', 'edit'].
     *
     * @return array<int, string>
     * @see self::__construct()
     */
    public function getPath(): array
    {
        $path = is_array($this->name) ? array_values($this->name) : [$this->name];

        foreach ($path as $segment) {
            if (! is_string($segment)) {
                throw new \InvalidArgumentException('CLI scope names must be strings.');
            }
        }

        return $path;
    }
}
