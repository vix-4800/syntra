<?php

declare(strict_types=1);

namespace Vix\Syntra\DI\Providers;

use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use Vix\Syntra\DI\ContainerInterface;
use Vix\Syntra\DI\ServiceProviderInterface;
use Vix\Syntra\NodeVisitors\AssignmentInConditionVisitor;
use Vix\Syntra\NodeVisitors\NestedTernaryVisitor;
use Vix\Syntra\NodeVisitors\ReturnThrowVisitor;

/**
 * Parser Service Provider
 *
 * Registers PHP parser services and node visitors
 * for code analysis functionality.
 */
class ParserServiceProvider implements ServiceProviderInterface
{
/**
 * Register parser services with the container.
 */
    public function register(ContainerInterface $container): void
    {
        // Register PHP Parser as singleton
        $container->singleton(Parser::class, fn (): Parser => (new ParserFactory())->create(ParserFactory::PREFER_PHP7));

        // Register NodeTraverser as transient (new instance each time)
        $container->bind(NodeTraverser::class, fn (): NodeTraverser => new NodeTraverser());

        // Register Node Visitors as transient services
        $container->bind(AssignmentInConditionVisitor::class, fn (): AssignmentInConditionVisitor =>  new AssignmentInConditionVisitor());

        $container->bind(NestedTernaryVisitor::class, fn (): NestedTernaryVisitor =>  new NestedTernaryVisitor());

        $container->bind(ReturnThrowVisitor::class, fn (): ReturnThrowVisitor => new ReturnThrowVisitor());

        // Register a factory for creating configured node traversers
        $container->bind('parser.traverser_factory', fn (ContainerInterface $container): callable => function (array $visitorClasses = []) use ($container): NodeTraverser {
            $traverser = $container->make(NodeTraverser::class);

            foreach ($visitorClasses as $visitorClass) {
                $visitor = $container->make($visitorClass);
                $traverser->addVisitor($visitor);
            }

            return $traverser;
        });
    }

/**
 * Boot the parser services.
 */
    public function boot(ContainerInterface $container): void
    {
        // No additional booting needed for parser services
    }
}
