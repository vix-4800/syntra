<?php

declare(strict_types=1);

namespace Vix\Syntra\Traits;

use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use Throwable;

/**
 * Helper trait to parse a PHP file and traverse its AST.
 */
trait ParsesPhpFilesTrait
{
    use ContainerAwareTrait;

    /**
     * Parse the given PHP file and traverse it with custom visitors.
     *
     * @param callable(NodeTraverser):void $traverserSetup Callback receiving the traverser to add visitors
     */
    protected function parseFile(string $path, callable $traverserSetup): void
    {
        $parser = $this->getService(
            Parser::class,
            fn (): Parser => (new ParserFactory())->create(ParserFactory::PREFER_PHP7),
        );

        $code = file_get_contents($path);
        if ($code === false) {
            return;
        }

        try {
            $ast = $parser->parse($code);
        } catch (Throwable) {
            return;
        }

        if ($ast === null) {
            return;
        }

        $traverser = new NodeTraverser();
        $traverserSetup($traverser);
        $traverser->traverse($ast);
    }
}
