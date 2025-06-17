<?php

namespace Nandan108\PropAccess\Tests;

use Nandan108\PropAccess\AccessorRegistry;
use PHPUnit\Framework\TestCase;

final class AccessorRegistryTest extends TestCase
{
    public function testNoGetterResolverAvailable(): void
    {
        AccessorRegistry::bootDefaultResolvers();

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('No getter resolver supports type "string"');

        AccessorRegistry::getGetterMap('not an object');
    }

    public function testNoSetterResolverAvailable(): void
    {
        AccessorRegistry::bootDefaultResolvers();

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('No setter resolver supports type "string"');

        AccessorRegistry::getSetterMap('not an object');
    }
}
