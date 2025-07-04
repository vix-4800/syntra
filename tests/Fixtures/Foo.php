<?php

namespace Vix\Syntra\Tests\Fixtures;

class Foo implements FooInterface
{
    public function name(): string
    {
        return 'foo';
    }
}
