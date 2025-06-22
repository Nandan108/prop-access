<?php

namespace Nandan108\PropAccess\Tests;

use Nandan108\PropAccess\AccessorRegistry;
use PHPUnit\Framework\TestCase;

final class StdClassResolverTest extends TestCase
{
    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        AccessorRegistry::bootDefaultResolvers();
    }

    public function testStdClassGetterResolver(): void
    {
        $stdObj = (object) [
            'plain'             => 'plainValue',
            'hidden'            => 42,
            'public_snake_case' => 'snake',
        ];

        /** @var array<string, \Closure(mixed): mixed> $map */
        $map = AccessorRegistry::getGetterMap($stdObj);

        $this->assertArrayHasKey('plain', $map);
        $this->assertArrayHasKey('hidden', $map);
        $this->assertArrayHasKey('public_snake_case', $map);
        $this->assertArrayNotHasKey('privateSnakeCase', $map);

        // however, a direct request for the public property should still work
        /** @var array<string, \Closure(mixed): mixed> $directRequestMap */
        $directRequestMap = AccessorRegistry::getGetterMap($stdObj, ['public_snake_case']);
        $this->assertArrayHasKey('public_snake_case', $directRequestMap);
        $this->assertSame('snake', $directRequestMap['public_snake_case']($stdObj));
    }

    public function testStdClassSetterResolver(): void
    {
        $stdObj = (object) [
            'plain'             => 'plainValue',
            'hidden'            => 42,
            'public_snake_case' => 'snake',
        ];

        /** @var array<string, \Closure(mixed, mixed): void> $map */
        $map = AccessorRegistry::getSetterMap($stdObj);
        foreach ($map as $key => $setter) {
            $setter($stdObj, "test-$key");
        }

        foreach ($stdObj as $key => $val) {
            $this->assertSame("test-$key", $val);
        }
        $this->assertArrayHasKey('plain', $map);
    }

    public function testStdClassGetterForNonExistingProperty(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('One or more property not found in \StdClass object: baz, qux');

        $entity = (object) ['foo' => 1, 'bar' => 42];
        AccessorRegistry::getGetterMap($entity, ['foo', 'baz', 'bar', 'qux'], false);
    }
}
