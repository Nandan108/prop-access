<?php

namespace Nandan108\PropAccess\Resolver;

use Nandan108\PropAccess\Contract\GetterMapResolverInterface;
use Nandan108\PropAccess\Support\CaseConverter;

final class ObjectGetterResolver implements GetterMapResolverInterface
{
    #[\Override]
    public function supports(mixed $value): bool
    {
        return is_object($value)
            // SplObjectStorage and WeakMap are not supported because they use objects as keys
            // and are very rarely used in queriable contexts (DTOs, config objects, API payloads, etc.)
            && !($value instanceof \SplObjectStorage || $value instanceof \WeakMap);
    }

    /**
     * Get the per-property list of getters defined for the given entity.
     * For each property, a closure is created that calls the getter method, or sets
     * the property directly if no getter is found the prop is public.
     *
     * @param bool $ignoreInaccessibleProps If false and no getter is found, an exception is thrown
     *
     * @return array<string, \Closure(object): mixed> a map of property names to getter closures
     *
     * @throws \LogicException
     */
    #[\Override]
    public function getGetterMap(
        mixed $valueSource,
        array|string|null $propNames = null,
        bool $ignoreInaccessibleProps = true,
    ): array {
        /** @var array<array{
         *    extra: array<string, \Closure(object): mixed>,
         *    fullMap: array<string, \Closure(object): mixed>
         * }>
         * */
        static $getterCache = []; // [className => ['full' => [...], 'extra' => [...]]]

        /** @var object $valueSource */
        $entityClass = $valueSource::class;

        if (!isset($getterCache[$entityClass])) {
            $entityReflection = new \ReflectionClass($valueSource);
            /** @var array<string, \Closure(object): mixed> */
            $canonicalMap = [];
            $extra = [];

            // Getter methods first
            foreach ($entityReflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                if (!str_starts_with($method->getName(), 'get') || $method->getNumberOfRequiredParameters() > 0) {
                    continue;
                }

                $methodName = $method->getName();
                $canonical = CaseConverter::toCamel(substr($methodName, 3));

                $canonicalMap[$canonical] = self::makeMethodGetter($methodName);
            }

            // Public properties next
            foreach ($entityReflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $prop) {
                $name = $prop->getName();

                $canonical = CaseConverter::toCamel($name);

                $accessor = null;
                $canonicalMap[$canonical] ??= $accessor = self::makePropertyGetter($name);

                foreach ([$name, CaseConverter::toSnake($name)] as $alias) {
                    // If this alias is not already present in either $canonicalMap or $extra,
                    if (!isset($canonicalMap[$alias]) && !isset($extra[$alias])) {
                        // Create an accessor or if possible reuse the accessor already created for the canonical name
                        $accessor ??= self::makePropertyGetter($name);
                        $extra[$alias] = $accessor;
                    }
                }
            }

            $getterCache[$entityClass] = [
                'fullMap' => $canonicalMap,
                'extra'   => $extra,
            ];
        }

        ['fullMap' => $canonicalMap, 'extra' => $extra] = $getterCache[$entityClass];

        if (null === $propNames) {
            return $canonicalMap;
        }
        if (is_string($propNames)) {
            $propNames = [$propNames];
        }

        $map = [];
        $missingProps = [];
        /** @var string $name */
        foreach ($propNames as $name) {
            $accessor = $canonicalMap[$name] ?? $extra[$name] ?? null;
            if ($accessor) {
                $map[$name] = $accessor;
            } else {
                $missingProps[] = $name;
            }
        }

        if ($missingProps && !$ignoreInaccessibleProps) {
            throw new \LogicException('No public getter or property found for: '.implode(', ', $missingProps).' in '.$entityClass);
        }

        return $map;
    }

    /**
     * @return \Closure(object): mixed
     */
    private static function makeMethodGetter(string $methodName): \Closure
    {
        /** @psalm-suppress MixedMethodCall */
        return fn (object $entity) => $entity->$methodName();
    }

    /**
     * @return \Closure(object): mixed
     */
    private static function makePropertyGetter(string $propName): \Closure
    {
        return static function (object $entity) use ($propName): mixed {
            return $entity->$propName;
        };
    }
}
