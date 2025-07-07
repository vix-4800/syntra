<?php

declare(strict_types=1);

namespace Vix\Syntra\NodeVisitors;

use PhpParser\Node;
use PhpParser\Node\Expr\Throw_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\PrettyPrinter\Standard;

class ReturnThrowVisitor extends NodeVisitor
{
    public function enterNode(Node $node)
    {
        if (
            $node instanceof Return_
            && $node->expr instanceof Throw_
        ) {
            $this->results[] = [
                'line' => $node->getLine(),
                'code' => $this->prettyPrintNode($node),
                'message' => 'Return throw',
            ];
        }

        return null;
    }

    private function prettyPrintNode($node): string
    {
        return (new Standard())->prettyPrintExpr($node);
    }
}
