<?php

namespace Nandan108\PropAccess;

/**
 * @template-implements \ArrayAccess<array-key, mixed>
 * @template-implements \IteratorAggregate<array-key, mixed>
 */
final class AccessProxy implements \ArrayAccess, \IteratorAggregate, \Countable
{
    /**
     * A map of property names to getter closures.
     *
     * @var array<array-key, \Closure(mixed): mixed>
     */
    private array $getterMap;

    /**
     * A map of property names to setter closures.
     *
     * @var ?array<array-key, \Closure(mixed, mixed): void>
     */
    private ?array $setterMap = null;

    /**
     * @param ?array<array-key, \Closure(mixed): mixed>       $getterMap
     * @param ?array<array-key, \Closure(mixed, mixed): void> $setterMap
     * @param ?list<array-key>                                $propNames
     */
    public function __construct(
        private object $target,
        ?array $propNames = null,
        public readonly bool $readOnly = true,
        ?array $getterMap = null,
        ?array $setterMap = null,
    ) {
        PropAccess::bootDefaultResolvers();

        $this->getterMap = $getterMap ?? PropAccess::getGetterMapOrThrow($target, $propNames);

        if (!$this->readOnly) {
            $this->setterMap = $setterMap ?? PropAccess::getSetterMapOrThrow($target, $propNames);
        }
    }

    /**
     * Creates an AccessorProxy for the given target object.
     *
     * @param ?list<array-key> $propNames
     */
    public static function getFor(
        object $target,
        ?array $propNames = null,
        bool $readOnly = true,
        bool $throwOnFailure = false,
    ): ?AccessProxy {
        PropAccess::bootDefaultResolvers();

        if ($throwOnFailure) {
            return new self($target, $propNames, $readOnly);
        }

        $getterMap = PropAccess::getGetterMap($target, $propNames, throwOnNotFound: false);
        $valid = (bool) $getterMap;

        $setterMap = null;
        if ($valid && !$readOnly) {
            $setterMap = PropAccess::getSetterMap($target, $propNames, throwOnNotFound: false);
            $valid = null !== $setterMap;
        }

        return $valid
            ? new self($target, $propNames, $readOnly, $getterMap, $setterMap)
            : null;
    }

    /**
     * Returns the original target object of this proxy.
     */
    public function getTarget(): object
    {
        return $this->target;
    }

    /**
     * Returns the getter map of this proxy.
     *
     * @return array<array-key, \Closure(mixed): mixed> a map of property names to getter closures
     */
    public function getGetters(): array
    {
        return $this->getterMap;
    }

    /**
     * Returns the setter map of this proxy.
     *
     * @return array<array-key, \Closure(mixed, mixed): void> a map of property names to setter closures
     *
     * @throws \LogicException if this proxy was created in read-only mode
     */
    public function getSetters(): array
    {
        if ($this->readOnly || null === $this->setterMap) {
            throw new \LogicException('This proxy was created in read-only mode.');
        }

        return $this->setterMap;
    }

    #[\Override]
    public function offsetGet(mixed $offset): mixed
    {
        $getter = $this->getterMap[(string) $offset] ?? null;
        if (null === $getter) {
            throw new \LogicException(sprintf('No getter found for "%s" in %s', $offset, get_debug_type($this->target)));
        }

        return $getter($this->target);
    }

    #[\Override]
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($this->readOnly) {
            throw new \LogicException('Cannot write via a read-only proxy.');
        }
        $offset = (string) $offset;
        $setter = $this->setterMap[$offset] ?? null;
        if (null === $setter) {
            throw new \LogicException(sprintf('No setter found for "%s" in %s', $offset, get_debug_type($this->target)));
        }
        $setter($this->target, $value);
    }

    #[\Override]
    public function offsetExists(mixed $offset): bool
    {
        $getter = $this->getGetters()[(string) $offset] ?? null;

        return $getter && null !== $getter($this->target);
    }

    #[\Override]
    public function offsetUnset(mixed $offset): void
    {
        throw new \LogicException('Cannot unset values on AccessorProxy; use object methods directly.');
    }

    /**
     * Removes accessors for the given keys from the proxy's internal maps.
     *
     * @param array<string|int> $keys the keys to remove from the getter and setter maps
     */
    public function removeAccessors(array $keys): void
    {
        foreach ($keys as $key) {
            unset($this->getterMap[$key]);
            if (null !== $this->setterMap) {
                unset($this->setterMap[$key]);
            }
        }
    }

    /**
     * Returns an iterator over the readable properties in this proxy.
     *
     * @return \Traversable<array-key, mixed> an iterator yielding property names and their values
     */
    #[\Override]
    public function getIterator(): \Traversable
    {
        foreach ($this->getterMap as $key => $getter) {
            yield $key => $getter($this->target);
        }
    }

    #[\Override]
    public function count(): int
    {
        return count($this->getterMap);
    }

    /**
     * Returns the keys of the readable properties in this proxy.
     *
     * @return array<array-key> the keys of the readable properties
     */
    public function readableKeys(): array
    {
        return array_keys($this->getterMap);
    }

    /**
     * Returns the keys of the writable properties in this proxy.
     *
     * If the proxy is read-only, this will return an empty array.
     *
     * @return array<array-key> the keys of the writable properties
     */
    public function writableKeys(): array
    {
        return array_keys($this->setterMap ?? []);
    }

    /**
     * Returns the values of the readable properties in this proxy.
     * Fails silently and returns an empty array if no getters are found or the target is not accessible.
     */
    public function toArray(): array
    {
        return PropAccess::resolveValues($this->getterMap, $this->target);
    }
}
