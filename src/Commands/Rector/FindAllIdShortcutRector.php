<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Rector;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class FindAllIdShortcutRector extends AbstractRector
{
/**
 * Get a service from the container.rule definition.
 */
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replaces Model::findAll([\'id\' => $id]) with Model::findAll($id)',
            []
        );
    }

/**
 * Get a service from the container.node types.
 */
    public function getNodeTypes(): array
    {
        return [StaticCall::class];
    }

/**
 * Refactor the provided files.
 */
    public function refactor(Node $node): ?Node
    {
        if (!$node instanceof StaticCall) {
            return null;
        }

        if (!$node->name instanceof Identifier || $node->name->toString() !== 'findAll') {
            return null;
        }

        if (count($node->args) !== 1) {
            return null;
        }

        $firstArg = $node->args[0];
        if (!$firstArg->value instanceof Array_) {
            return null;
        }

        $array = $firstArg->value;
        if (count($array->items) !== 1) {
            return null;
        }

        $firstItem = $array->items[0];
        if (!$firstItem instanceof ArrayItem) {
            return null;
        }

        if (!$firstItem->key instanceof String_ || $firstItem->key->value !== 'id') {
            return null;
        }

        $node->args = [new Arg($firstItem->value)];
        return $node;
    }
}
