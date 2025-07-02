<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Identical\SimplifyBoolIdenticalTrueRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector;
use Rector\CodeQuality\Rector\Ternary\UnnecessaryTernaryExpressionRector;
use Rector\CodingStyle\Rector\ClassMethod\NewlineBeforeNewAssignSetRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector;
use Rector\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector;

return RectorConfig::configure()
    ->withRootFiles()
    ->withSkip([
        'vendor'
    ])
    ->withPhpSets(php84: true)
    ->withTypeCoverageLevel(0) // Type coverage level: 0 — no requirement for full type coverage
    ->withDeadCodeLevel(0) // Dead code detection level: 0 — do not analyze dead code
    ->withCodeQualityLevel(0) // Code quality improvement level: 0 — do not apply globally
    ->withImportNames(removeUnusedImports: true) // Import use-statements and remove unused ones
    ->withRules([
        SimplifyIfReturnBoolRector::class, // Simplifies if-statements that return true/false
        SimplifyIfElseToTernaryRector::class, // Replaces if/else with a ternary operator
        SimplifyBoolIdenticalTrueRector::class, // Replaces $a === true with just $a
        UnnecessaryTernaryExpressionRector::class, // Removes redundant ternary expressions
        RemoveUnusedPrivateMethodRector::class, // Removes unused private methods
        RemoveUnusedPrivatePropertyRector::class, // Removes unused private properties
        NewlineBeforeNewAssignSetRector::class, // Enforces newline style before `new` assignments
    ]);
