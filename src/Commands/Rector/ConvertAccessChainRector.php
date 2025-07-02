<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Rector;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use Rector\Rector\AbstractRector;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Name;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class ConvertAccessChainRector extends AbstractRector
{
    /**
     * Provides documentation and example code for the rule.
     *
     * @return RuleDefinition
     */
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replaces user->identity->hasAccessChain/hasNoAccessChain with user->canAny/cannotAny',
            []
        );
    }

    /**
     * Specifies the node types this Rector rule should process.
     *
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * Performs the transformation for matching MethodCall nodes.
     *
     * @param MethodCall $node
     *
     * @return StaticCall|null
     */
    public function refactor(Node $node): ?Node
    {
        if (!$node->name instanceof Identifier) {
            return null;
        }

        $legacyName = $node->name->toString();
        if (!in_array($legacyName, ['hasAccessChain', 'hasNoAccessChain'], true)) {
            return null;
        }

        $identityFetch = $node->var;
        if (
            !$identityFetch instanceof PropertyFetch ||
            !$this->isName($identityFetch->name, 'identity')
        ) {
            return null;
        }

        $userFetch = $identityFetch->var;
        if (
            !$userFetch instanceof PropertyFetch ||
            !$this->isName($userFetch->name, 'user')
        ) {
            return null;
        }

        $appFetch = $userFetch->var;
        if (
            !$appFetch instanceof StaticPropertyFetch ||
            !$appFetch->class instanceof Name ||
            !$this->isName($appFetch->class, 'Yii')
        ) {
            return null;
        }

        if (!$appFetch->name instanceof Identifier || !$this->isName($appFetch->name, 'app')) {
            return null;
        }

        $args = $node->getArgs();
        if (count($args) < 1) {
            return null;
        }

        return new MethodCall(
            $userFetch,
            $legacyName === 'hasAccessChain' ? 'canAny' : 'cannotAny',
            [$args[0]]
        );
    }
}
