<?php

namespace Nandan108\PropAccess\Tests;

use Nandan108\PropAccess\Exception\AccessorException;
use Nandan108\PropAccess\PropAccess;
use Nandan108\PropAccess\Tests\Fixtures\SampleEntity;
use PHPUnit\Framework\TestCase;

final class ObjectResolverTest extends TestCase
{
    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        PropAccess::bootDefaultResolvers();
        // A second time for coverage of the already-booted case
        PropAccess::bootDefaultResolvers();
    }

    public function testObjectGetterResolver(): void
    {
        $entity = new SampleEntity();

        /** @var array<string, \Closure(mixed): mixed> $map */
        $map = PropAccess::getGetterMap($entity);

        $this->assertArrayHasKey('plain', $map);
        $this->assertArrayHasKey('hidden', $map);

        // Check that the general map has an accessor for the public getter
        $this->assertArrayHasKey('publicSnakeCase', $map);
        $this->assertSame('SNAKE', $map['publicSnakeCase']($entity));

        // property is public, but the getter's already there, so the key should not be duplicated
        $this->assertArrayNotHasKey('public_snake_case', $map);

        // however, a direct request for the public property should still work
        /** @var array<string, \Closure(mixed): mixed> $directRequestMap */
        $directRequestMap = PropAccess::getGetterMap($entity, ['public_snake_case']);
        $this->assertArrayHasKey('public_snake_case', $directRequestMap);
        $this->assertSame('snake', $directRequestMap['public_snake_case']($entity));

        $directRequestMapForSingleProp = PropAccess::getGetterMap($entity, 'public_snake_case');
        $this->assertSame($directRequestMap, $directRequestMapForSingleProp);

        // should have key because getter is public
        $this->assertArrayHasKey('privateSnakeCase', $map);
        $this->assertArrayNotHasKey('private_snake_case', $map);

        $this->assertSame('plainValue', $map['plain']($entity));
        $this->assertSame(42, $map['hidden']($entity));
        $this->assertSame('SNAKE', $map['privateSnakeCase']($entity));
    }

    // test failure on getting getters of non-existing properties
    public function testGetterForNonExistingProperty(): void
    {
        $this->expectException(AccessorException::class);
        $this->expectExceptionMessage('No public getter or property found for: nonExisting, missingProperty in Nandan108\PropAccess\Tests\Fixtures\SampleEntity');

        $entity = new SampleEntity();
        PropAccess::getGetterMap($entity, ['plain', 'nonExisting', 'missingProperty'], false);
    }

    public function testObjectSetterResolver(): void
    {
        $entity = new SampleEntity();

        /** @var array<string, \Closure(mixed, mixed): void> $setterMap */
        $setterMap = PropAccess::getSetterMap($entity);

        $this->assertArrayHasKey('plain', $setterMap);
        $this->assertArrayHasKey('hidden', $setterMap);

        // Check that the general map has an accessor for the public setter
        $this->assertArrayHasKey('publicSnakeCase', $setterMap);
        $setterMap['publicSnakeCase']($entity, 'SNAKE');
        $this->assertSame('snake', $entity->public_snake_case);

        // property is public, but the setter's already there, so the key should not be duplicated
        $this->assertArrayNotHasKey('public_snake_case', $setterMap);

        // however, a direct request for the public property should still work
        /** @var array<string, \Closure(mixed, mixed): void> $directRequestMap */
        $directRequestMap = PropAccess::getSetterMap($entity, ['public_snake_case']);
        $this->assertArrayHasKey('public_snake_case', $directRequestMap);
        $directRequestMap['public_snake_case']($entity, 'SNAKE');
        /** @psalm-suppress DocblockTypeContradiction */
        $this->assertSame('SNAKE', $entity->public_snake_case);

        // should have key because setter is public
        $this->assertArrayHasKey('privateSnakeCase', $setterMap);
        $this->assertArrayNotHasKey('private_snake_case', $setterMap);
        $setterMap['privateSnakeCase']($entity, 'SNAKE');
        $this->assertSame('snake', $entity->getUntransformedPrivateSnakeCase());
        $this->assertSame('SNAKE', $entity->getPrivateSnakeCase());

        $setterMap['plain']($entity, 'plainValue');
        $this->assertSame('plainValue', $entity->plain);
        $setterMap['hidden']($entity, 42);
        $this->assertSame(42, $entity->getHidden());
    }

    // test failure on getting setters of non-existing properties
    public function testSetterForNonExistingProperty(): void
    {
        $this->expectException(AccessorException::class);
        $this->expectExceptionMessage('No public setter or property found for: nonExisting, missingProperty in Nandan108\PropAccess\Tests\Fixtures\SampleEntity');

        $entity = new SampleEntity();
        PropAccess::getSetterMap($entity, ['plain', 'nonExisting', 'missingProperty'], false);
    }
}
