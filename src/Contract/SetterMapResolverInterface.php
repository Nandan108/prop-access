<?php

namespace Nandan108\PropAccess\Contract;

interface SetterMapResolverInterface
{
    public function supports(mixed $value): bool;

    /**
     * @return array<string, \Closure(mixed, mixed): void>
     */
    public function getSetterMap(
        mixed $target,
        array|string|null $propNames = null,
        bool $ignoreInaccessibleProps = true,
    ): array;
}
