<?php

namespace Vix\Syntra\Tests\Commands;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Vix\Syntra\Application;
use Vix\Syntra\Facades\Config;
use Vix\Syntra\Facades\File;

class GenerateDocsCommandTest extends TestCase
{
    public function testParametersIncluded(): void
    {
        $dir = sys_get_temp_dir() . '/syntra_test_' . uniqid();
        mkdir("$dir/backend/controllers", 0777, true);
        copy(__DIR__ . '/../Fixtures/backend/controllers/SiteController.php', "$dir/backend/controllers/SiteController.php");
        file_put_contents("$dir/composer.json", json_encode(['require' => ['yiisoft/yii2' => '*']]));

        $app = new Application();
        File::clearCache();
        Config::setContainer($app->getContainer());
        Config::setProjectRoot($dir);

        $command = $app->find('general:generate-docs');
        $tester = new CommandTester($command);
        $tester->execute(['path' => $dir, '--no-progress' => true]);

        $content = file_get_contents("$dir/docs/routes.md");

        $this->assertMatchesRegularExpression('/\| `index`\s+\|\s+0\s+\|\s+string \$id, int \$page = 1\s+\|\s+Home page\s+\|/', $content);
        $this->assertMatchesRegularExpression('/\| `view`\s+\|\s+0\s+\|\s+\$slug\s+\|\s+\|/', $content);

        unlink("$dir/backend/controllers/SiteController.php");
        unlink("$dir/composer.json");
        unlink("$dir/docs/routes.md");
        rmdir("$dir/docs");
        rmdir("$dir/backend/controllers");
        rmdir("$dir/backend");
        rmdir($dir);
    }
}
