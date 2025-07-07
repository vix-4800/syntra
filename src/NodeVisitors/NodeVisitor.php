<?php

declare(strict_types=1);

namespace Vix\Syntra\NodeVisitors;

use PhpParser\NodeVisitorAbstract;

class NodeVisitor extends NodeVisitorAbstract
{
    protected array $results = [];

    public function getResults(): array
    {
        return $this->results;
    }
}
