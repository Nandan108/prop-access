<?php

namespace Nandan108\PropAccess\Resolver;

use Nandan108\PropAccess\Contract\SetterMapResolverInterface;

final class StdClassSetterResolver implements SetterMapResolverInterface
{
    #[\Override]
    public function supports(mixed $value): bool
    {
        return $value instanceof \stdClass;
    }

    #[\Override]
    public function getSetterMap(mixed $target, array|string|null $propNames = null, bool $ignoreInaccessibleProps = true): array
    {
        /** @var \stdClass $target */
        $map = [];

        $props = match (true) {
            is_array($propNames)  => $propNames,
            is_string($propNames) => [$propNames],
            default               => array_keys(get_object_vars($target)),
        };

        foreach ($props as $name) {
            $map[$name] = static fn (\stdClass $obj, mixed $val): mixed => $obj->$name = $val;
        }

        return $map;
    }
}
