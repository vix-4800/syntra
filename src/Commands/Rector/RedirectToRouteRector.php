<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Rector rule that replaces redirect()->route() with to_route().
 */
class RedirectToRouteRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replaces redirect()->route() with to_route()', []);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    public function refactor(Node $node): ?Node
    {
        if (!$node instanceof MethodCall || !$this->isName($node->name, 'route')) {
            return null;
        }

        $var = $node->var;
        if (!$var instanceof FuncCall) {
            return null;
        }
        if (!$var->name instanceof Name || !$this->isName($var->name, 'redirect')) {
            return null;
        }

        return new FuncCall(new Name('to_route'), $node->args);
    }
}
