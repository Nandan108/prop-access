<?php

namespace Nandan108\PropAccess\Tests\Fixtures;

final class SampleEntity
{
    public string $plain = 'plainValue';
    private int $hidden = 42;

    public function getHidden(): int
    {
        return $this->hidden;
    }

    /** @psalm-suppress PossiblyUnusedMethod */
    public function setHidden(int $value): void
    {
        $this->hidden = $value;
    }

    public string $public_snake_case = 'snake';

    /** @psalm-suppress PossiblyUnusedMethod */
    public function getPublicSnakeCase(): string
    {
        return strtoupper($this->public_snake_case);
    }

    /** @psalm-suppress PossiblyUnusedMethod */
    public function setPublicSnakeCase(string $value): void
    {
        $this->public_snake_case = strtolower($value);
    }

    private string $private_snake_case = 'snake';

    public function getPrivateSnakeCase(): string
    {
        return strtoupper($this->private_snake_case);
    }

    public function getUntransformedPrivateSnakeCase(): string
    {
        return $this->private_snake_case;
    }

    /** @psalm-suppress PossiblyUnusedMethod */
    public function setPrivateSnakeCase(string $value): void
    {
        $this->private_snake_case = strtolower($value);
    }
}
