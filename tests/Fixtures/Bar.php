<?php

declare(strict_types=1);

namespace Vix\Syntra\Tests\Fixtures;

class Bar
{
    public function __construct(public FooInterface $foo)
    {
        //
    }
}
