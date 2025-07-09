<?php

namespace Vix\Syntra\Tests\Utils;

use PHPUnit\Framework\TestCase;
use Vix\Syntra\Exceptions\ConfigException;
use Vix\Syntra\Utils\ConfigLoader;

class ConfigLoaderTest extends TestCase
{
    public function testThrowsForInvalidConfig(): void
    {
        $dir = sys_get_temp_dir() . '/syntra_test_' . uniqid();
        mkdir($dir);
        file_put_contents("$dir/composer.json", '{}');
        file_put_contents("$dir/syntra.php", "<?php return 'invalid';");
        $cwd = getcwd();
        chdir($dir);

        try {
            $this->expectException(ConfigException::class);
            new ConfigLoader();
        } finally {
            chdir($cwd);
            unlink("$dir/syntra.php");
            unlink("$dir/composer.json");
            rmdir($dir);
        }
    }
}
