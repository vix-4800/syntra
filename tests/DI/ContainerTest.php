<?php

namespace Vix\Syntra\Tests\DI;

use PHPUnit\Framework\TestCase;
use Vix\Syntra\DI\Container;
use Vix\Syntra\Tests\Fixtures\Bar;
use Vix\Syntra\Tests\Fixtures\Foo;
use Vix\Syntra\Tests\Fixtures\FooInterface;

class ContainerTest extends TestCase
{
    public function testResolvesDependencies(): void
    {
        $container = new Container();
        $container->bind(FooInterface::class, Foo::class);

        $bar = $container->make(Bar::class);

        $this->assertInstanceOf(Bar::class, $bar);
        $this->assertSame('foo', $bar->foo->name());
    }
}
