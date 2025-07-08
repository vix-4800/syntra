<?php

declare(strict_types=1);

namespace Vix\Syntra\NodeVisitors;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\IntersectionType;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\UnionType;
use PhpParser\PrettyPrinter\Standard;

class DocsVisitor extends NodeVisitor
{
    private readonly Standard $printer;

    public function __construct()
    {
        $this->printer = new Standard();
    }

    public function enterNode(Node $node)
    {
        if (
            $node instanceof Class_
            && $node->name !== null
            && str_ends_with($node->name->name, 'Controller')
        ) {
            $short = $node->name->name;
            $ctrl = preg_replace('/Controller$/', '', $short);
            $ctrl = strtolower((string) preg_replace('/([a-z])([A-Z])/', '$1-$2', (string) $ctrl));

            foreach ($node->getMethods() as $method) {
                if (! $method->isPublic()) {
                    continue;
                }

                $mName = $method->name->name;
                if (str_starts_with($mName, 'action')) {
                    $actionRaw = substr($mName, 6);
                    $action  = strtolower(
                        (string) preg_replace('/([a-z])([A-Z])/', '$1-$2', $actionRaw)
                    );
                    $route = "$ctrl/$action";

                    $desc = '';
                    $doc = $method->getDocComment();
                    if ($doc) {
                        $lines = explode("\n", $doc->getText());
                        foreach ($lines as $line) {
                            $txt = trim($line, "/* \t");
                            if ($txt !== '' && $txt[0] !== '@') {
                                $desc = $txt;
                                break;
                            }
                        }
                    }

                    $params = [];
                    foreach ($method->getParams() as $param) {
                        $params[] = $this->paramToString($param);
                    }

                    $this->results[] = [
                        'route' => $route,
                        'desc' => $desc,
                        'params' => $params,
                    ];
                }
            }
        }

        return null;
    }

    private function paramToString(Param $param): string
    {
        $type = '';
        if ($param->type !== null) {
            $type = $this->typeToString($param->type) . ' ';
        }

        $default = '';
        if ($param->default !== null) {
            $default = ' = ' . $this->printer->prettyPrintExpr($param->default);
        }

        return trim($type . '$' . $param->var->name . $default);
    }

    private function typeToString(Name|Identifier|NullableType|UnionType|IntersectionType $type): string
    {
        if ($type instanceof NullableType) {
            return '?' . $this->typeToString($type->type);
        }

        if ($type instanceof UnionType) {
            $types = array_map(fn ($t): string => $this->typeToString($t), $type->types);
            return implode('|', $types);
        }

        if ($type instanceof IntersectionType) {
            $types = array_map(fn ($t): string => $this->typeToString($t), $type->types);
            return implode('&', $types);
        }

        return $type->toString();
    }
}
