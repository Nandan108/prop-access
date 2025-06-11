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

        self::registerGetterResolver(new Resolver\ObjectGetterResolver());
        self::registerSetterResolver(new Resolver\ObjectSetterResolver());
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

    public static function getGetterMap(
        mixed $target,
        array|string|null $propNames = null,
        bool $ignoreInaccessibleProps = true,
    ): array {
        foreach (self::$getterResolvers as $resolver) {
            if ($resolver->supports($target)) {
                return $resolver->getGetterMap($target, $propNames, $ignoreInaccessibleProps);
            }
        }

        throw new \InvalidArgumentException('No getter resolver supports type "'.get_debug_type($target).'"');
    }

    public static function getSetterMap(
        mixed $target,
        array|string|null $propNames = null,
        bool $ignoreInaccessibleProps = true,
    ): array {
        foreach (self::$setterResolvers as $resolver) {
            if ($resolver->supports($target)) {
                return $resolver->getSetterMap($target, $propNames, $ignoreInaccessibleProps);
            }
        }

        throw new \InvalidArgumentException('No setter resolver supports type "'.get_debug_type($target).'"');
    }
}
