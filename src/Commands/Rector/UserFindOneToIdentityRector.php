<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use Rector\Rector\AbstractRector;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class UserFindOneToIdentityRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replaces User::findOne(Yii::$app->user->id) with Yii::$app->user->identity',
            []
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [StaticCall::class];
    }

    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->isUserFindOneCall($node)) {
            return null;
        }

        $args = $node->getArgs();
        if (count($args) !== 1) {
            return null;
        }

        $argValue = $args[0]->value;

        if ($this->isYiiUserIdPropertyFetch($args[0]->value)) {
            return $this->createYiiUserIdentityPropertyFetch();
        }

        return null;
    }

    private function isUserFindOneCall(StaticCall $node): bool
    {
        // Проверяем что метод называется findOne
        if (!$this->isName($node->name, 'findOne')) {
            return false;
        }

        // Проверяем что класс - User (базовое имя без неймспейса)
        if ($node->class instanceof Name) {
            $className = $this->getName($node->class);
            $baseName = $className ? basename(str_replace('\\', '/', $className)) : null;
            return $baseName === 'User';
        }

        return false;
    }

    private function isYiiUserIdPropertyFetch(Expr $expr): bool
    {
        return $expr instanceof PropertyFetch
            && $this->getName($expr->name) === 'id'
            && $expr->var instanceof PropertyFetch
            && $this->getName($expr->var->name) === 'user'
            && $expr->var->var instanceof StaticPropertyFetch
            && $this->isName($expr->var->var->class, 'Yii')
            && $this->isName($expr->var->var->name, 'app');
    }

    private function createYiiUserIdentityPropertyFetch(): PropertyFetch
    {
        return new PropertyFetch(
            new PropertyFetch(
                new StaticPropertyFetch(new Name('Yii'), 'app'),
                'user'
            ),
            'identity'
        );
    }
}
