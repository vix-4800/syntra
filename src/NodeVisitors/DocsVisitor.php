<?php

declare(strict_types=1);

namespace Vix\Syntra\NodeVisitors;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Stmt\Class_;

class DocsVisitor extends NodeVisitorAbstract
{
    public array $routes = [];

    public function enterNode(Node $node): void
    {
        if (
            $node instanceof Class_
            && $node->name !== null
            && str_ends_with($node->name->name, 'Controller')
        ) {
            $short = $node->name->name;
            $ctrl = preg_replace('/Controller$/', '', $short);
            $ctrl = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $ctrl));

            foreach ($node->getMethods() as $method) {
                if (! $method->isPublic()) {
                    continue;
                }

                $mName = $method->name->name;
                if (str_starts_with($mName, 'action')) {
                    $actionRaw = substr($mName, 6);
                    $action  = strtolower(
                        preg_replace('/([a-z])([A-Z])/', '$1-$2', $actionRaw)
                    );
                    $route = "$ctrl/$action";

                    $desc = '';
                    if ($doc = $method->getDocComment()) {
                        $lines = explode("\n", $doc->getText());
                        foreach ($lines as $line) {
                            $txt = trim($line, "/* \t");
                            if ($txt !== '' && $txt[0] !== '@') {
                                $desc = $txt;
                                break;
                            }
                        }
                    }

                    $this->routes[] = [
                        'route' => $route,
                        'desc' => $desc,
                    ];
                }
            }
        }
    }
}
