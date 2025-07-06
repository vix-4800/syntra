<?php

declare(strict_types=1);

namespace Vix\Syntra\Facades;

use Vix\Syntra\Utils\FileHelper;

/**
 * @method static array collectFiles(string $dir, array $extensions = ['php'], bool $absolute = true)
 */
class File extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return FileHelper::class;
    }
}
