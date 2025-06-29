<?php

namespace Nandan108\PropAccess\Resolver;

use Nandan108\PropAccess\Contract\SetterMapResolverInterface;
use Nandan108\PropAccess\Support\CaseConverter;

final class ObjectSetterResolver implements SetterMapResolverInterface
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
     * Get a map of closure setters for the given properties.
     *
     * @return array<array-key, \Closure(mixed, mixed): void>
     *
     * @throws \LogicException
     */
    #[\Override]
    public function getSetterMap(
        mixed $target,
        array|string|null $propNames = null,
        bool $ignoreInaccessibleProps = true,
    ): array {
        /** @var array<array{
         *    extra: array<array-key, \Closure(object, mixed): void>,
         *    fullMap: array<array-key, \Closure(object, mixed): void>
         * }>
         */
        static $setterCache = []; // [className => ['full' => [...], 'extra' => [...]]]

        /** @var object $target */
        $entityClass = $target::class;

        if (!isset($setterCache[$entityClass])) {
            $entityReflection = new \ReflectionClass($target);
            $canonicalMap = [];
            $extra = [];

            // Setter methods first
            foreach ($entityReflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                if (!str_starts_with($method->getName(), 'set') || 1 !== $method->getNumberOfRequiredParameters()) {
                    continue;
                }

                $methodName = $method->getName();
                $canonical = CaseConverter::toCamel(substr($methodName, 3));

                $canonicalMap[$canonical] = self::makeMethodSetter($methodName);
            }

            // Public properties next
            foreach ($entityReflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $prop) {
                $name = $prop->getName();
                $canonical = CaseConverter::toCamel($name);

                $accessor = null;
                $canonicalMap[$canonical] ??= $accessor = self::makePropertySetter($name);

                foreach ([$name, CaseConverter::toSnake($name)] as $alias) {
                    if (!isset($canonicalMap[$alias]) && !isset($extra[$alias])) {
                        $accessor ??= self::makePropertySetter($name);
                        $extra[$alias] = $accessor;
                    }
                }
            }

            $setterCache[$entityClass] = [
                'fullMap' => $canonicalMap,
                'extra'   => $extra,
            ];
        }

        ['fullMap' => $canonicalMap, 'extra' => $extra] = $setterCache[$entityClass];

        if (null === $propNames) {
            return $canonicalMap;
        }

        $map = [];
        $missingProps = [];
        /** @var string $name */
        foreach ((array) $propNames as $name) {
            $accessor = $canonicalMap[$name] ?? $extra[$name] ?? null;
            if ($accessor) {
                $map[$name] = $accessor;
            } else {
                $missingProps[] = $name;
            }
        }

        if ($missingProps && !$ignoreInaccessibleProps) {
            throw new \LogicException('No public setter or property found for: '.implode(', ', $missingProps).' in '.$entityClass);
        }

        return $map;
    }

    /**
     * @param array-key $methodName
     *
     * @return \Closure(object, mixed): void
     */
    private static function makeMethodSetter(string $methodName): \Closure
    {
        $methodName = (string) $methodName;

        return static function (object $entity, mixed $value) use ($methodName): void {
            /** @psalm-suppress MixedMethodCall */
            $entity->$methodName($value);
        };
    }

    /**
     * @return \Closure(object, mixed): void
     */
    private static function makePropertySetter(string $propName): \Closure
    {
        return static function (object $entity, mixed $value) use ($propName): void {
            $entity->$propName = $value;
        };
    }
}
