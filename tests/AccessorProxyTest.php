<?php

namespace Nandan108\PropAccess\Tests;

use Nandan108\PropAccess\AccessProxy;
use Nandan108\PropAccess\PropAccess;
use PHPUnit\Framework\TestCase;

final class AccessorProxyTest extends TestCase
{
    public function testArrayAccessGetAndSet(): void
    {
        $obj = new class {
            public string $name = 'John';
            public int $age = 30;
        };

        PropAccess::bootDefaultResolvers();

        $proxy = new AccessProxy($obj, readOnly: false);

        $this->assertSame('John', $proxy['name']);
        $this->assertSame(30, $proxy['age']);
        $this->assertSame(2, count($proxy));
        $this->assertSame(2, $proxy->count());

        $proxy['name'] = 'Alice';
        $proxy['age'] = 25;

        $this->assertSame('Alice', $obj->name);
        $this->assertSame(25, $obj->age);
    }

    public function testOffsetExists(): void
    {
        $obj = new class {
            public string $foo = 'bar';
        };

        $proxy = new AccessProxy($obj);

        $this->assertTrue(isset($proxy['foo']));
        $this->assertFalse(isset($proxy['baz']));
    }

    public function testOffsetUnset(): void
    {
        PropAccess::bootDefaultResolvers();
        $obj = new class {
            public string $foo = 'bar';
            public string $baz = 'qux';
        };

        $proxy = new AccessProxy($obj, readOnly: false);
        $proxy->removeAccessors(['baz']);

        $this->assertTrue(isset($proxy['foo']));
        $this->assertFalse(isset($proxy['baz']));

        // directly unsetting a property should throw a LogicException('Cannot unset values on AccessorProxy; use object methods directly.')
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Cannot unset values on AccessorProxy; use object methods directly.');
        unset($proxy['baz']);
    }

    public function testCount(): void
    {
        $obj = new class {
            public string $foo = 'bar';
            public string $baz = 'qux';
        };

        $proxy = new AccessProxy($obj);
        $this->assertCount(2, $proxy);
    }

    public function testIteration(): void
    {
        $obj = new class {
            public string $a = 'x';
            public string $b = 'y';
        };

        $proxy = new AccessProxy($obj);

        $result = [];

        /** @psalm-var mixed $value */
        foreach ($proxy as $key => $value) {
            /** @psalm-suppress MixedAssignment */
            $result[$key] = $value;
        }

        $this->assertSame(['a' => 'x', 'b' => 'y'], $result);
    }

    public function testKeysAndToArray(): void
    {
        $obj = new class {
            public string $a = 'x';
            public string $b = 'y';
        };

        $proxy = AccessProxy::getFor($obj, readOnly: false);
        $this->assertInstanceOf(AccessProxy::class, $proxy);
        $this->assertSame(['a', 'b'], $proxy->readableKeys());
        $this->assertSame(['a', 'b'], $proxy->writableKeys());
        $this->assertSame(['a' => 'x', 'b' => 'y'], $proxy->toArray());

        $proxy->removeAccessors(['b']);
        $this->assertSame(['a'], $proxy->readableKeys());
        $this->assertSame(['a'], $proxy->writableKeys());

        $this->assertSame($obj, $proxy->getTarget());
    }

    public function testGetForSuccess(): void
    {
        $obj = new class {
            public string $prop = 'val';
        };

        $proxy = AccessProxy::getFor($obj);
        $this->assertInstanceOf(AccessProxy::class, $proxy);
        $this->assertSame('val', $proxy['prop']);
    }

    public function testGetForFailure(): void
    {
        $proxy = AccessProxy::getFor(new \SplObjectStorage());
        $this->assertNull($proxy);

        // test getFor() trying to access a non-existing property
        $obj = new class {
            public string $foo = 'val';
        };

        $proxy = AccessProxy::getFor($obj, ['bar'], throwOnFailure: false);
        $this->assertNull($proxy);
    }

    // test getFor() with $throwOnFailure = true
    public function testGetForWithThrowOnFailure(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('No getter resolver supports type "SplObjectStorage"');

        AccessProxy::getFor(new \SplObjectStorage(), ['nonexistent'], throwOnFailure: true);
    }

    // test getFor() with $readonly = false
    public function testGetForWritableProxy(): void
    {
        $obj = new class {
            public string $prop = 'value';
        };

        $proxy = AccessProxy::getFor($obj, readOnly: false);
        $this->assertInstanceOf(AccessProxy::class, $proxy);
        $this->assertSame('value', $proxy['prop']);

        // Test setting a value
        $proxy['prop'] = 'new value';
        $this->assertSame('new value', $obj->prop);
    }

    // test getSetters() on read-only proxy (throws \LogicException('This proxy was created in read-only mode.'))
    public function testGetSettersOnReadOnlyProxy(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('This proxy was created in read-only mode.');

        $obj = new class {
            public string $prop = 'value';
        };

        $proxy = AccessProxy::getFor($obj);
        $this->assertInstanceOf(AccessProxy::class, $proxy);
        $proxy->getSetters();
    }

    // test getSetters() on writable proxy
    // 237486
    public function testGetSettersOnWritableProxy(): void
    {
        $obj = new class {
            public string $prop = 'value';
        };

        $proxy = AccessProxy::getFor($obj, readOnly: false);
        $this->assertInstanceOf(AccessProxy::class, $proxy);

        $setters = $proxy->getSetters();
        $this->assertArrayHasKey('prop', $setters);

        // Test setting a value via the setter
        $setters['prop']($obj, 'new value');
        $this->assertSame('new value', $obj->prop);
    }

    // test offsetGet() for invalid property (throws \LogicException("No getter found for \"$offset\" in ".get_debug_type($target)))
    public function testOffsetGetInvalidProperty(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('No getter found for "nonexistent" in class@anonymous');

        $obj = new class {
            public string $foo = 'bar';
        };
        $proxy = AccessProxy::getFor($obj);
        $this->assertInstanceOf(AccessProxy::class, $proxy);
        $proxy['nonexistent'];
    }

    // test offsetSet() on read-only proxy (throws \LogicException('Cannot write via a read-only proxy.'))
    public function testOffsetSetOnReadOnlyProxy(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Cannot write via a read-only proxy.');

        $obj = new class {
            public string $prop = 'value';
        };
        $proxy = AccessProxy::getFor($obj);
        $this->assertInstanceOf(AccessProxy::class, $proxy);
        $proxy['prop'] = 'value';
    }

    // test offsetSet() on writable proxy but invalid property (throws \LogicException("No setter found for \"$offset\" in ".get_debug_type($target)))
    public function testOffsetSetInvalidProperty(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('No setter found for "nonexistent" in class@anonymous');

        $obj = new class {
            public string $foo = 'bar';
        };
        $proxy = AccessProxy::getFor($obj, readOnly: false);
        $this->assertInstanceOf(AccessProxy::class, $proxy);
        $proxy['nonexistent'] = 'value';
    }
}
