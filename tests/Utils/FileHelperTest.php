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

    public function testCollectFilesHandlesSingleFile(): void
    {
        $helper = new FileHelper();

        $tempFile = tempnam(sys_get_temp_dir(), 'syntra_');
        assert($tempFile !== false);
        $phpFile = $tempFile . '.php';
        rename($tempFile, $phpFile);
        file_put_contents($phpFile, '<?php echo "test";');

        $files = $helper->collectFiles($phpFile);

        $this->assertSame([$phpFile], $files);

        unlink($phpFile);
    }

    public function testMakeRelativeWithFileRoot(): void
    {
        $helper = new FileHelper();

        $tempFile = tempnam(sys_get_temp_dir(), 'syntra_');
        assert($tempFile !== false);

        $relative = $helper->makeRelative($tempFile, $tempFile);

        $this->assertSame(basename($tempFile), $relative);

        unlink($tempFile);
    }
}
