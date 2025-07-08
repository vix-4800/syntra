<?php

declare(strict_types=1);

namespace Vix\Syntra\Facades;

use Vix\Syntra\DTO\ProcessResult;
use Vix\Syntra\Utils\RectorCommandExecutor;

/**
 * @method static ProcessResult executeRules(string $path, array $rectorClasses, array $additionalArgs = [], ?callable $outputCallback = null)
 * @method static bool          isRectorAvailable()
 */
class Rector extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return RectorCommandExecutor::class;
    }
}
