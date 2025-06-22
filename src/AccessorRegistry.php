<?php

namespace Nandan108\PropAccess;

use Nandan108\PropAccess\Contract\GetterMapResolverInterface;
use Nandan108\PropAccess\Contract\SetterMapResolverInterface;

/**
 * @api
 *
 * @psalm-suppress UnusedClass
 */
final class AccessorRegistry
{
    /** @var GetterMapResolverInterface[] */
    private static array $getterResolvers = [];

    /** @var SetterMapResolverInterface[] */
    private static array $setterResolvers = [];

    /**
     * Boot the default resolvers.
     *
     * This method is to called once at boot time to ensure that the default resolvers
     * are registered. It is typically called in a service provider's boot method.
     */
    public static function bootDefaultResolvers(): void
    {
        static $booted = false;

        if ($booted) {
            return;
        }
        $booted = true;

        // Register default getter/setter resolvers for generic objects
        self::registerGetterResolver(new Resolver\ObjectGetterResolver());
        self::registerSetterResolver(new Resolver\ObjectSetterResolver());

        // Register default getter/setter resolvers for stdClass
        self::registerGetterResolver(new Resolver\StdClassGetterResolver());
        self::registerSetterResolver(new Resolver\StdClassSetterResolver());
    }

    /**
     * Register a new getter resolver with highest priority (checked before previously registered ones).
     */
    public static function registerGetterResolver(GetterMapResolverInterface $resolver): void
    {
        array_unshift(self::$getterResolvers, $resolver);
    }

    /**
     * Register a new setter resolver with highest priority (checked before previously registered ones).
     */
    public static function registerSetterResolver(SetterMapResolverInterface $resolver): void
    {
        array_unshift(self::$setterResolvers, $resolver);
    }

    /**
     * Check if there is a registered getter resolver that supports the given target.
     *
     * @throws \RuntimeException
     */
    public static function canGetGetterMap(mixed $target): bool
    {
        self::$getterResolvers or throw new \RuntimeException('No getter resolvers registered. Please boot before use!');

        foreach (self::$getterResolvers as $resolver) {
            if ($resolver->supports($target)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if there is a registered setter resolver that supports the given target.
     *
     * @throws \RuntimeException
     */
    public static function canGetSetterMap(mixed $target): bool
    {
        self::$setterResolvers or throw new \RuntimeException('No setter resolvers registered. Please boot before use!');

        foreach (self::$setterResolvers as $resolver) {
            if ($resolver->supports($target)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get a map of getters for the given value source.
     *
     * @param mixed             $valueSource             the object or array to get the properties from
     * @param array|string|null $propNames               optional specific property names to include in the map
     * @param bool              $ignoreInaccessibleProps Whether to ignore properties that cannot be accessed.
     *                                                   If false, an exception will be thrown when a property in $propNames is not accessible.
     * @param bool              $throwOnNotFound         whether to throw an exception if no resolver supports the type
     *
     * @return ?array<string, \Closure(mixed): mixed> a map of property names to getter closures,
     *                                                or null if $throwOnNotFound is false and no resolver supports the type
     *
     * @throws \InvalidArgumentException if $throwOnNotFound is true and no resolver supports the value source type
     */
    public static function getGetterMap(
        mixed $valueSource,
        array|string|null $propNames = null,
        bool $ignoreInaccessibleProps = true,
        bool $throwOnNotFound = true,
    ): ?array {
        self::$getterResolvers or throw new \RuntimeException('No getter resolvers registered. Please boot before use!');

        foreach (self::$getterResolvers as $resolver) {
            if ($resolver->supports($valueSource)) {
                return $resolver->getGetterMap($valueSource, $propNames, $ignoreInaccessibleProps);
            }
        }
        if ($throwOnNotFound) {
            throw new \InvalidArgumentException('No getter resolver supports type "'.get_debug_type($valueSource).'"');
        } else {
            return null;
        }
    }

    /**
     * Get a map of resolved values for the given value source.
     */
    public static function getValueMap(
        mixed $valueSource,
        array|string|null $propNames = null,
        bool $ignoreInaccessibleProps = true,
        bool $throwOnNotFound = true,
    ): ?array {
        $map = self::getGetterMap($valueSource, $propNames, $ignoreInaccessibleProps, $throwOnNotFound);

        if (null !== $map) {
            foreach ($map as $name => $getter) {
                $map[$name] = $getter($valueSource);
            }
        }

        return $map;
    }

    /**
     * Resolve the values from a map of getters.
     *
     * @param array<string, \Closure(mixed): mixed> $getterMap a map of property names to getter closures
     *
     * @return array<string, mixed> an associative array of property names and their resolved values
     */
    public static function resolveValues(array $getterMap, mixed $valueSource)
    {
        $resolvedValues = [];
        foreach ($getterMap as $name => $getter) {
            $resolvedValues[$name] = $getter($valueSource);
        }

        return $resolvedValues;
    }

    /**
     *  Get a map of setters for the given target.
     *
     * @param mixed             $target                  the target object or array to get the properties from
     * @param array|string|null $propNames               optional specific property names to include in the map
     * @param bool              $ignoreInaccessibleProps Whether to ignore properties that cannot be accessed.
     *                                                   If false, an exception will be thrown when a property in $propNames is not accessible.
     * @param bool              $throwOnNotFound         whether to throw an exception if no resolver supports the type
     *
     * @return ?array<\Closure(mixed,mixed):void> a map of property names to setter closures,
     *                                            or null if $throwOnNotFound is false and no resolver supports the type
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public static function getSetterMap(
        mixed $target,
        array|string|null $propNames = null,
        bool $ignoreInaccessibleProps = true,
        bool $throwOnNotFound = true,
    ): ?array {
        self::$getterResolvers or throw new \RuntimeException('No setter resolvers registered. Please boot before use!');

        foreach (self::$setterResolvers as $resolver) {
            if ($resolver->supports($target)) {
                return $resolver->getSetterMap($target, $propNames, $ignoreInaccessibleProps);
            }
        }

        if ($throwOnNotFound) {
            throw new \InvalidArgumentException('No setter resolver supports type "'.get_debug_type($target).'"');
        } else {
            return null;
        }
    }
}
