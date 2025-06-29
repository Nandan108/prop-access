<?php

namespace Nandan108\PropAccess\Contract;

interface GetterMapResolverInterface
{
    public function supports(mixed $value): bool;

    /**
     * Get the per-property list of getters defined for the given entity.
     *
     * @param array<array-key>|string|null $propNames
     *
     * @return array<array-key, \Closure(mixed): mixed>
     */
    public function getGetterMap(
        mixed $valueSource,
        array|string|null $propNames = null,
        bool $ignoreInaccessibleProps = true,
    ): array;
}
