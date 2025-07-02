<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Rector;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Identifier;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use Rector\Rector\AbstractRector;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Scalar\String_;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class UserFindOneToIdentityRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replaces redundant User::findOne(...) lookups for current user with Yii::$app->user->identity',
            []
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [StaticCall::class, MethodCall::class];
    }

    /**
     * @param StaticCall|MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        // User::findOne(...)
        if (
            $node instanceof StaticCall
            && $node->class instanceof Name
            && $this->isName($node->class, 'User')
            && $this->isName($node->name, 'findOne')
            && count($node->args) === 1
        ) {
            $arg = $node->args[0]->value;

            // User::findOne(Yii::$app->user->id) и вариации
            if ($this->isCurrentUserIdExpr($arg)) {
                return $this->userIdentityExpr();
            }

            // User::findOne(['id' => ...])
            if ($arg instanceof Array_) {
                foreach ($arg->items as $item) {
                    if (
                        $item instanceof ArrayItem
                        && $item->key instanceof String_
                        && $item->key->value === 'id'
                        && $this->isCurrentUserIdExpr($item->value)
                    ) {
                        return $this->userIdentityExpr();
                    }
                }
            }
        }

        if (
            $node instanceof MethodCall
            && $this->isName($node->name, 'one')
            && $node->var instanceof MethodCall
            && $this->isName($node->var->name, 'where')
            && $node->var->var instanceof StaticCall
            && $node->var->var->class instanceof Name
            && $this->isName($node->var->var->class, 'User')
            && $this->isName($node->var->var->name, 'find')
            && isset($node->var->args[0])
        ) {
            $whereArg = $node->var->args[0]->value;

            if ($whereArg instanceof Array_) {
                foreach ($whereArg->items as $item) {
                    if (
                        $item instanceof ArrayItem
                        && $item->key instanceof String_
                        && $item->key->value === 'id'
                        && $this->isCurrentUserIdExpr($item->value)
                    ) {
                        return $this->userIdentityExpr();
                    }
                }
            }
        }

        return null;
    }

    /**
     * Check if expression is any variant of current user id
     */
    private function isCurrentUserIdExpr(Node $expr): bool
    {
        // Yii::$app->user->id
        if (
            $expr instanceof PropertyFetch
            && $expr->name instanceof Identifier && $expr->name->toString() === 'id'
            && $expr->var instanceof PropertyFetch
            && $expr->var->name instanceof Identifier && $expr->var->name->toString() === 'user'
            && $expr->var->var instanceof StaticPropertyFetch
            && $expr->var->var->class instanceof Name && $this->isName($expr->var->var->class, 'Yii')
            && $expr->var->var->name instanceof Identifier && $expr->var->var->name->toString() === 'app'
        ) {
            return true;
        }

        // Yii::$app->user->getId()
        if (
            $expr instanceof MethodCall
            && $expr->name instanceof Identifier && $expr->name->toString() === 'getId'
            && $expr->var instanceof PropertyFetch
            && $expr->var->name instanceof Identifier && $expr->var->name->toString() === 'user'
            && $expr->var->var instanceof StaticPropertyFetch
            && $expr->var->var->class instanceof Name && $this->isName($expr->var->var->class, 'Yii')
            && $expr->var->var->name instanceof Identifier && $expr->var->var->name->toString() === 'app'
        ) {
            return true;
        }

        // Yii::$app->user->identity->getId()
        if (
            $expr instanceof MethodCall
            && $expr->name instanceof Identifier && $expr->name->toString() === 'getId'
            && $expr->var instanceof PropertyFetch
            && $expr->var->name instanceof Identifier && $expr->var->name->toString() === 'identity'
            && $expr->var->var instanceof PropertyFetch
            && $expr->var->var->name instanceof Identifier && $expr->var->var->name->toString() === 'user'
            && $expr->var->var->var instanceof StaticPropertyFetch
            && $expr->var->var->var->class instanceof Name && $this->isName($expr->var->var->var->class, 'Yii')
            && $expr->var->var->var->name instanceof Identifier && $expr->var->var->var->name->toString() === 'app'
        ) {
            return true;
        }

        // Yii::$app->user->getIdentity()->getId()
        if (
            $expr instanceof MethodCall
            && $expr->name instanceof Identifier && $expr->name->toString() === 'getId'
            && $expr->var instanceof MethodCall
            && $expr->var->name instanceof Identifier && $expr->var->name->toString() === 'getIdentity'
            && $expr->var->var instanceof PropertyFetch
            && $expr->var->var->name instanceof Identifier && $expr->var->var->name->toString() === 'user'
            && $expr->var->var->var instanceof StaticPropertyFetch
            && $expr->var->var->var->class instanceof Name && $this->isName($expr->var->var->var->class, 'Yii')
            && $expr->var->var->var->name instanceof Identifier && $expr->var->var->var->name->toString() === 'app'
        ) {
            return true;
        }

        return false;
    }

    /**
     * Returns node Yii::$app->user->identity
     */
    private function userIdentityExpr(): Node
    {
        return new PropertyFetch(
            new PropertyFetch(
                new StaticPropertyFetch(new Name('Yii'), new Identifier('app')),
                new Identifier('user')
            ),
            new Identifier('identity')
        );
    }
}
