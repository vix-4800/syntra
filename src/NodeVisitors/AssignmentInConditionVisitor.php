<?php

declare(strict_types=1);

namespace Vix\Syntra\NodeVisitors;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\While_;

class AssignmentInConditionVisitor extends NodeVisitor
{
    public function enterNode(Node $node)
    {
        if ($node instanceof If_ || $node instanceof ElseIf_ || $node instanceof While_) {
            $assignments = $this->findAssignments($node->cond);
            foreach ($assignments as $assignNode) {
                $this->results[] = [
                    'line' => $node->getLine(),
                    'code' => $this->prettyPrintNode($assignNode),
                    'message' => 'Assignment in condition',
                ];
            }
        }

        return null;
    }

    private function findAssignments($cond): array
    {
        $assignments = [];

        if ($cond instanceof Assign) {
            $assignments[] = $cond;
        }

        if ($cond instanceof Expr) {
            foreach ($cond->getSubNodeNames() as $name) {
                $sub = $cond->$name;
                if ($sub instanceof Expr) {
                    $assignments = array_merge($assignments, $this->findAssignments($sub));
                } elseif (is_array($sub)) {
                    foreach ($sub as $elem) {
                        if ($elem instanceof Expr) {
                            $assignments = array_merge($assignments, $this->findAssignments($elem));
                        }
                    }
                }
            }
        }

        return $assignments;
    }
}
