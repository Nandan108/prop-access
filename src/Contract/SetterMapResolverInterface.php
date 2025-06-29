<?php

namespace Nandan108\PropAccess\Contract;

interface SetterMapResolverInterface
{
    public function supports(mixed $value): bool;

    /**
     * @param array<array-key>|string|null $propNames
     *
     * @return array<array-key, \Closure(mixed, mixed): void>
     */
    public function getSetterMap(
        mixed $target,
        array|string|null $propNames = null,
        bool $ignoreInaccessibleProps = true,
    ): array;
}
