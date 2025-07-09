<?php

declare(strict_types=1);

namespace Vix\Syntra\Tests\Utils;

use PHPUnit\Framework\TestCase;
use Vix\Syntra\Exceptions\DirectoryNotFoundException;
use Vix\Syntra\Utils\FileHelper;

class FileHelperTest extends TestCase
{
    public function testCollectFilesThrowsForMissingDirectory(): void
    {
        $helper = new FileHelper();

        $this->expectException(DirectoryNotFoundException::class);
        $helper->collectFiles(sys_get_temp_dir() . '/nonexistent_' . uniqid());
    }
}
