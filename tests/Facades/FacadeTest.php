<?php

declare(strict_types=1);

namespace Vix\Syntra\Tests\Facades;

use PHPUnit\Framework\TestCase;
use Vix\Syntra\DI\Container;
use Vix\Syntra\Facades\Facade;
use Vix\Syntra\Tests\Fixtures\Foo;
use Vix\Syntra\Tests\Fixtures\FooInterface;

/**
 * @method static string name()
 */
class FooFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return FooInterface::class;
    }
}

class FacadeTest extends TestCase
{
    public function testFacadeResolvesService(): void
    {
        $container = new Container();
        $container->bind(FooInterface::class, Foo::class);

        FooFacade::setContainer($container);

        $this->assertSame('foo', FooFacade::name());
    }
}
