<?php

namespace Nandan108\PropAccess\Tests;

use Nandan108\PropAccess\PropAccess;
use Nandan108\PropAccess\Tests\Fixtures\SampleEntity;
use PHPUnit\Framework\TestCase;

final class PropAccessTest extends TestCase
{
    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        PropAccess::bootDefaultResolvers();
    }

    public function testNoGetterResolverAvailable(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('No getter resolver supports type "SplObjectStorage"');

        /** @psalm-suppress InvalidArgument */
        PropAccess::getGetterMap(new \SplObjectStorage());
    }

    public function testNoSetterResolverAvailable(): void
    {
        $this->assertNull(PropAccess::getSetterMap('not an object', throwOnNotFound: false));
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('No setter resolver supports type "string"');
        PropAccess::getSetterMap('not an object');
    }

    // canGetGetterMap() for a resolable type
    public function testCanGetGetterMapForResolvableType(): void
    {
        $source = new SampleEntity();
        $canGetMap = PropAccess::canGetGetterMap($source);
        $this->assertTrue($canGetMap, 'Expected to be able to get getter map for SampleEntity');
    }

    // canGetGetterMap() for a non-resolable type (SplObjectStorage)
    public function testCanGetGetterMapForNonResolvableType(): void
    {
        $source = new \SplObjectStorage();
        $canGetMap = PropAccess::canGetGetterMap($source);
        $this->assertFalse($canGetMap, 'Expected not to be able to get getter map for SplObjectStorage');
    }

    // canGetSetterMap() for a resolable type
    public function testCanGetSetterMapForResolvableType(): void
    {
        $target = new SampleEntity();
        $canGetMap = PropAccess::canGetSetterMap($target);
        $this->assertTrue($canGetMap, 'Expected to be able to get setter map for SampleEntity');
    }

    // canGetSetterMap() for a non-resolable type (SplObjectStorage)
    public function testCanGetSetterMapForNonResolvableType(): void
    {
        $target = new \SplObjectStorage();
        $canGetMap = PropAccess::canGetSetterMap($target);
        $this->assertFalse($canGetMap, 'Expected not to be able to get setter map for SplObjectStorage');
    }

    // getGetterMap() for a non-resolable type
    public function testGetGetterMapForNonResolvableType(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('No getter resolver supports type "SplObjectStorage"');
        $this->assertNull(PropAccess::getGetterMap(new \SplObjectStorage(), throwOnNotFound: false));
        PropAccess::getGetterMap(new \SplObjectStorage());
    }

    // getValueMap() for a resolable type
    public function testGetValueMapForResolvableType(): void
    {
        $source = new SampleEntity();
        $map = PropAccess::getValueMap($source);
        $this->assertIsArray($map, 'Expected to get a value map for SampleEntity');
        $this->assertArrayHasKey('plain', $map, 'Expected "plain" property to be in the value map');
        $this->assertSame($source->plain, $map['plain'], 'Expected "plain" value to match the entity value');
    }

    // resolveValues() with a valid map
    public function testResolveValuesWithValidMap(): void
    {
        $source = new SampleEntity();
        $map = PropAccess::getGetterMap($source);
        $this->assertIsArray($map, 'Expected getter map to be an array');
        $resolved = PropAccess::resolveValues($map, $source);
        $this->assertSame($source->plain, $resolved['plain'], 'Expected resolved "plain" value to match the entity value');
    }

    // get
}
