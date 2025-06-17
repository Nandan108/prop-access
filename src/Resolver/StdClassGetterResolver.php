<?php

namespace Nandan108\PropAccess\Resolver;

use Nandan108\PropAccess\Contract\GetterMapResolverInterface;

final class StdClassGetterResolver implements GetterMapResolverInterface
{
    #[\Override]
    public function supports(mixed $value): bool
    {
        return $value instanceof \stdClass;
    }

    #[\Override]
    public function getGetterMap(
        mixed $valueSource,
        array|string|null $propNames = null,
        bool $ignoreInaccessibleProps = true,
    ): array {
        $vars = get_object_vars($valueSource);

        if (null !== $propNames) {
            $propNames = (array) $propNames;
            $vars = array_intersect_key($vars, array_flip($propNames));
            $missingProps = array_diff($propNames, array_keys($vars));
            if (count($vars) < count($propNames) && !$ignoreInaccessibleProps) {
                throw new \LogicException('One or more property not found in \StdClass object: '.implode(', ', $missingProps));
            }
        }

        $map = [];
        foreach ($vars as $name => $_) {
            $map[$name] = static fn (object $obj): mixed => $obj->$name ?? null;
        }

        return $map;
    }
}
