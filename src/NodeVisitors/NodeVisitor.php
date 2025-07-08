<?php

declare(strict_types=1);

namespace Vix\Syntra\NodeVisitors;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\NodeVisitorAbstract;
use PhpParser\PrettyPrinter\Standard;

class NodeVisitor extends NodeVisitorAbstract
{
    protected array $results = [];

    protected function prettyPrintNode(Node $node): string
    {
        $printer = new Standard();

        if ($node instanceof Expr) {
            return $printer->prettyPrintExpr($node);
        }

        return $printer->prettyPrint([$node]);
    }

    public function getResults(): array
    {
        return $this->results;
    }
}
