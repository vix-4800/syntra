<?php

declare(strict_types=1);

namespace Vix\Syntra\Facades;

use Vix\Syntra\Utils\ProjectDetector;

/**
 * @method static string detect(string $projectRoot)
 */
class Project extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ProjectDetector::class;
    }
}
