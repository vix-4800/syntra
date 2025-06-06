<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Rector;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Name;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Identifier;
use PhpParser\Node\Expr\Array_;
use Rector\Rector\AbstractRector;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\StaticPropertyFetch;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Rector rule that replaces chains of can()/!can() combined via && or ||
 * with shortcut methods like canAny(), canAll(), cannotAny(), cannotAll().
 */
class CanHelpersRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replaces can()/!can() chains with canAny(), canAll(), cannotAny(), or cannotAll()',
            []
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [BooleanOr::class, BooleanAnd::class];
    }

    /**
     * @param BooleanOr|BooleanAnd $node
     */
    public function refactor(Node $node): ?Node
    {
        $operatorClass = $node::class;
        $conditions = $this->flattenNode($node, $operatorClass);

        $permissions = [];
        $isNegated = null;
        $firstCall = null;

        foreach ($conditions as $condition) {
            $currentNegated = false;
            $permissionNode = $condition;

            // Handle negated conditions (!Yii::$app->user->can(...))
            if ($condition instanceof BooleanNot) {
                $currentNegated = true;
                $permissionNode = $condition->expr;
            }

            // Check if it's a ->can(...) method call
            if (!$this->isUserCanCall($permissionNode)) {
                return null;
            }

            $args = $permissionNode->getArgs();
            if (count($args) !== 1) {
                return null;
            }

            $argValue = $args[0]->value;
            if (!$argValue instanceof String_) {
                return null;
            }

            // Ensure all calls are made on the same object (e.g. Yii::$app->user)
            if ($firstCall === null) {
                $firstCall = $permissionNode;
            } elseif (!$this->nodeComparator->areNodesEqual($permissionNode->var, $firstCall->var)) {
                return null;
            }

            // Ensure all conditions are either negated or not (no mixing)
            if ($isNegated === null) {
                $isNegated = $currentNegated;
            } elseif ($isNegated !== $currentNegated) {
                return null;
            }

            $permissions[] = $argValue->value;
        }

        if (count($permissions) < 2) {
            return null;
        }

        $methodName = $this->resolveMethodName($node, $isNegated);
        if ($methodName === null) {
            return null;
        }

        $arrayItems = array_map(
            fn($permission): ArrayItem => new ArrayItem(new String_($permission)),
            $permissions
        );

        return new MethodCall(
            $firstCall->var,
            $methodName,
            [new Arg(new Array_($arrayItems))]
        );
    }

    private function resolveMethodName(Node $node, bool $isNegated): ?string
    {
        if ($node instanceof BooleanOr) {
            return $isNegated ? 'cannotAll' : 'canAny';
        }

        if ($node instanceof BooleanAnd) {
            return $isNegated ? 'cannotAny' : 'canAll';
        }

        return null;
    }

    /**
     * Recursively flattens nested BooleanOr or BooleanAnd expressions.
     *
     * @param  Node               $node
     * @param  class-string<Node> $operatorClass
     * @return Node[]
     */
    private function flattenNode(Node $node, string $operatorClass): array
    {
        if (!($node instanceof $operatorClass)) {
            return [$node];
        }

        return array_merge(
            $this->flattenNode($node->left, $operatorClass),
            $this->flattenNode($node->right, $operatorClass)
        );
    }

    /**
     * Checks if the node is a Yii::$app->user->can(...) call.
     */
    private function isUserCanCall(Node $node): bool
    {
        if (!$node instanceof MethodCall) {
            return false;
        }

        if (!$this->isName($node->name, 'can')) {
            return false;
        }

        $var = $node->var;
        if (!$var instanceof PropertyFetch || !$this->isName($var->name, 'user')) {
            return false;
        }

        $appFetch = $var->var;
        if (
            !$appFetch instanceof StaticPropertyFetch ||
            !$appFetch->class instanceof Name ||
            !$this->isName($appFetch->class, 'Yii')
        ) {
            return false;
        }

        return $appFetch->name instanceof Identifier && $this->isName($appFetch->name, 'app');
    }
}
