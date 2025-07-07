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
 * @method static void     setCacheEnabled(bool $enabled)
 * @method static void     clearCache()
 */
class File extends Facade
{
/**
 * Get the facade accessor class name.
 */
    protected static function getFacadeAccessor(): string
    {
        return FileHelper::class;
    }
}
