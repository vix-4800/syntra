<?php

namespace Vix\Syntra\Tests\Fixtures;

class Bar
{
    public function __construct(public FooInterface $foo) {}
}
