<?php

declare(strict_types=1);

namespace Vix\Syntra\Facades;

use Vix\Syntra\Utils\ProjectInfo;

/**
 * @method static string detect(string $rootPath = null)
 * @method static void   setRootPath(string $path)
 * @method static string getRootPath()
 */
class Project extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ProjectInfo::class;
    }
}
