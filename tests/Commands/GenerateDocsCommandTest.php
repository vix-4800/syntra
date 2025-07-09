<?php

namespace Vix\Syntra\Tests\Commands;

use Symfony\Component\Console\Tester\CommandTester;
use Vix\Syntra\Tests\CommandTestCase;

class GenerateDocsCommandTest extends CommandTestCase
{
    public function testParametersIncluded(): void
    {
        mkdir("$this->dir/backend/controllers", 0777, true);
        copy(
            __DIR__ . '/../Fixtures/backend/controllers/SiteController.php',
            "$this->dir/backend/controllers/SiteController.php"
        );
        file_put_contents(
            "$this->dir/composer.json",
            json_encode(['require' => ['yiisoft/yii2' => '*']])
        );

        $tester = $this->runCommand('general:generate-docs', [
            'path' => $this->dir,
            '--no-progress' => true,
        ]);
        $content = file_get_contents("$this->dir/docs/routes.md");

        $this->assertMatchesRegularExpression('/\| `index`\s+\|\s+0\s+\|\s+string \$id, int \$page = 1\s+\|\s+Home page\s+\|/', $content);
        $this->assertMatchesRegularExpression('/\| `view`\s+\|\s+0\s+\|\s+\$slug\s+\|\s+\|/', $content);

    }

    public function testReferenceCountsIncluded(): void
    {
        mkdir("$this->dir/backend/controllers", 0777, true);
        copy(
            __DIR__ . '/../Fixtures/backend/controllers/SiteController.php',
            "$this->dir/backend/controllers/SiteController.php"
        );
        mkdir("$this->dir/backend/views/site", 0777, true);
        file_put_contents("$this->dir/backend/views/site/index.phtml", '/site/index /site/index');
        file_put_contents(
            "$this->dir/composer.json",
            json_encode(['require' => ['yiisoft/yii2' => '*']])
        );

        $this->runCommand('general:generate-docs', [
            'path' => $this->dir,
            '--no-progress' => true,
            '--count-refs' => true,
        ]);

        $content = file_get_contents("$this->dir/docs/routes.md");
        $this->assertMatchesRegularExpression('/\| `index`\s+\|\s+2\s+\|/', $content);
    }
}
