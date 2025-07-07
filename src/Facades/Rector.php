<?php

declare(strict_types=1);

namespace Vix\Syntra\Facades;

use Vix\Syntra\Utils\RectorCommandExecutor;
use Vix\Syntra\DTO\ProcessResult;

/**
 * @method static ProcessResult executeRules(array $rectorClasses, array $additionalArgs = [], ?callable $outputCallback = null)
 * @method static bool isRectorAvailable()
 */
class Rector extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return RectorCommandExecutor::class;
    }
}
