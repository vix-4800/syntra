<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Rector\Laravel;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class DispatchShortcutRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Rewrites dispatch(new SomeJob($args)) to SomeJob::dispatch($args)',
            []
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->isName($node, 'dispatch')) {
            return null;
        }

        if (count($node->args) !== 1) {
            return null;
        }

        $arg = $node->args[0]->value;
        if (!$arg instanceof New_ || !$arg->class instanceof Name) {
            return null;
        }

        return new StaticCall($arg->class, 'dispatch', $arg->args);
    }
}
