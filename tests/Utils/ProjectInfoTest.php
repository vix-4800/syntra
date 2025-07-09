<?php

declare(strict_types=1);

namespace Vix\Syntra\Tests\Utils;

use PHPUnit\Framework\TestCase;
use Vix\Syntra\Application;
use Vix\Syntra\Facades\Cache;
use Vix\Syntra\Facades\Project;
use Vix\Syntra\Utils\ProjectInfo;

class ProjectInfoTest extends TestCase
{
    public function testDetectsYiiProject(): void
    {
        $dir = sys_get_temp_dir() . '/syntra_test_' . uniqid();
        mkdir($dir);
        file_put_contents("$dir/composer.json", json_encode([
            'require' => [
                'yiisoft/yii2' => '*',
            ],
        ]));

        new Application();
        Cache::clearAll();

        $this->assertSame(ProjectInfo::TYPE_YII, Project::detect($dir));

        unlink("$dir/composer.json");
        rmdir($dir);
    }

    public function testDetectsLaravelProject(): void
    {
        $dir = sys_get_temp_dir() . '/syntra_test_' . uniqid();
        mkdir($dir);
        file_put_contents("$dir/composer.json", json_encode([
            'require' => [
                'laravel/framework' => '*',
            ],
        ]));

        new Application();
        Cache::clearAll();

        $this->assertSame(ProjectInfo::TYPE_LARAVEL, Project::detect($dir));

        unlink("$dir/composer.json");
        rmdir($dir);
    }

    public function testDetectsUnknownProject(): void
    {
        $dir = sys_get_temp_dir() . '/syntra_test_' . uniqid();
        mkdir($dir);
        file_put_contents("$dir/composer.json", json_encode([
            'require' => [
                'some/package' => '*',
            ],
        ]));

        new Application();
        Cache::clearAll();

        $this->assertSame(ProjectInfo::TYPE_UNKNOWN, Project::detect($dir));

        unlink("$dir/composer.json");
        rmdir($dir);
    }

    public function testGetRootPathFallsBackToCwd(): void
    {
        $dir = sys_get_temp_dir() . '/syntra_test_' . uniqid();
        mkdir($dir);
        $cwd = getcwd();
        chdir($dir);

        try {
            $info = new ProjectInfo();
            $this->assertSame($dir, $info->getRootPath());
        } finally {
            chdir($cwd);
            rmdir($dir);
        }
    }
}
