<?php

declare(strict_types=1);

namespace Vix\Syntra\NodeVisitors;

use PhpParser\Node;
use PhpParser\Node\Expr\Ternary;

class NestedTernaryVisitor extends NodeVisitor
{
    public function enterNode(Node $node)
    {
        if ($node instanceof Ternary) {
            foreach (['if', 'else'] as $side) {
                if ($node->$side instanceof Ternary) {
                    $this->results[] = [
                        'line' => $node->getLine(),
                        'code' => $this->prettyPrintNode($node),
                        'message' => 'Nested ternary operator',
                    ];
                }
            }
        }

        return null;
    }
}
