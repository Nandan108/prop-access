<?php

namespace Nandan108\PropAccess\Contract;

interface GetterMapResolverInterface
{
    public function supports(mixed $value): bool;

    /**
     * @return array<string, \Closure(mixed): mixed>
     */
    public function getGetterMap(
        mixed $valueSource,
        array|string|null $propNames = null,
        bool $ignoreInaccessibleProps = true,
    ): array;
}
