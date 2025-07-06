<?php

declare(strict_types=1);

namespace Vix\Syntra\NodeVisitors;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\NodeVisitorAbstract;

class LongMethodVisitor extends NodeVisitorAbstract
{
    public array $results;

    public function __construct(private readonly string $filePath, private readonly int $maxLength, &$results)
    {
        $this->results = &$results;
    }

    public function enterNode(Node $node): void
    {
        if (
            $node instanceof ClassMethod ||
            $node instanceof Function_
        ) {
            $startLine = $node->getStartLine();
            $endLine = $node->getEndLine();
            $length = $endLine - $startLine + 1;

            if ($length > $this->maxLength) {
                $class = $node instanceof ClassMethod && $node->getAttribute('parent') instanceof Class_
                    ? $node->getAttribute('parent')->name->toString()
                    : '';

                $this->results[] = [
                    $this->filePath,
                    $class,
                    $node->name->toString(),
                    $length,
                    $startLine,
                    $endLine,
                ];
            }
        }

        if ($node instanceof Class_) {
            foreach ($node->getMethods() as $method) {
                $method->setAttribute('parent', $node);
            }
        }
    }
}
