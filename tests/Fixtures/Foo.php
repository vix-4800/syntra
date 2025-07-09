<?php

declare(strict_types=1);

namespace Vix\Syntra\Tests\Fixtures;

class Foo implements FooInterface
{
    public function name(): string
    {
        return 'foo';
    }
}
