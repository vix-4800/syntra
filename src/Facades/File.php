<?php

declare(strict_types=1);

namespace Vix\Syntra\Facades;

use Vix\Syntra\Utils\FileHelper;

/**
 * @method static array    collectFiles(string $dir, array $extensions = ['php'], bool $absolute = true)
 * @method static void     writeChanges(string $filePath, string $oldContent, string $newContent)
 * @method static string   makeRelative(string $path, string $root)
 * @method static string[] getChangedFiles()
 * @method static void     clearChangedFiles()
 */
class File extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return FileHelper::class;
    }
}
